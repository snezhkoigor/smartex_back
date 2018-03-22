<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Http\Controllers\Controller;
use App\Models\Exchange;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\PaymentRepository;
use App\Transformers\PaymentTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PDF;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\JsonResponse;

/**
 * Class PaymentController
 * @package App\Http\Controllers\Api
 *
 *
 */
class PaymentController extends Controller
{
	/**
	 * @param Request $request
	 * @return mixed
	 * @throws \Exception
	 */
	public function userPayments(Request $request)
	{
		$user = \Auth::user();
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		$filters = $this->getFilters($request);
	    $sorts = $this->getSortParameters($request);
	    $search_string = $this->getSearchString($request);
	    $fieldsets = $this->getFieldsets($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $filters['id_user'] = $user->id;
	    $users = PaymentRepository::getPayments($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

	    $meta = [
		    'count' => PaymentRepository::getPaymentsCount($filters, $search_string),
	    ];

	    return fractal($users, new PaymentTransformer())
		    ->parseIncludes(['paymentSystem'])
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
	}


	/**
	 * @param $payment_id
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function getPaymentById($payment_id): JsonResponse
    {
	    $payment = Payment::query()->find($payment_id);
	    if ($payment === null) {
		    throw new NotFoundHttpException('Payment not found');
	    }

	    return fractal($payment, new PaymentTransformer())
		    ->respond();
    }


	/**
	 * @param $payment_id
	 * @return JsonResponse
	 * @throws \Exception
	 */
    public function confirm($payment_id): JsonResponse
    {
    	$payment = Payment::query()->find($payment_id);
	    if ($payment === null) {
		    throw new NotFoundHttpException('Payment not found');
	    }

	    $exchange = Exchange::query()
		    ->where('in_id_pay', $payment_id)
		    ->orWhere('out_id_pay', $payment_id)
	        ->first();
	    if ($exchange === null) {
		    throw new NotFoundHttpException('Exchange not found');
	    }

	    if ($payment->type === 1 && $exchange->out_id_pay === 0)
	    {
	    	try
		    {
		    	$out_payment = new Payment();
		        $out_payment->id_user = $exchange->id_user;
		        $out_payment->id_account = 0;
		        $out_payment->date = Carbon::now()
			        ->format('Y-m-d H:i:s');
		        $out_payment->type = 2;
		        $out_payment->payment_system = $exchange->out_payment;
		        $out_payment->payer = $exchange->out_payer;
		        $out_payment->payee = $exchange->out_payee;
		        $out_payment->amount = $exchange->out_amount;
		        $out_payment->currency = $exchange->out_currency;
		        $out_payment->fee = $exchange->out_fee;
		        $out_payment->batch = $exchange->out_batch;
		        $out_payment->confirm = false;
		        $out_payment->save();

		        $exchange->out_id_pay = $out_payment->id;
		        $exchange->save();
		    }
		    catch (\Exception $e)
		    {
			    throw new SystemErrorException('Creating out payment by confirm in payment failed', $e);
		    }
	    }

	    try
	    {
		    $payment->confirm = true;
		    $payment->date_confirm = Carbon::now()
			    ->format('Y-m-d H:i:s');
		    $payment->save();
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Payment confirm process failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }


	/**
	 * wkhtmltopdf need
	 * @param $user_id
	 * @return BinaryFileResponse
	 * @throws \Exception
	 */
    public function pdfTransactionsByUser($user_id): BinaryFileResponse
    {
    	$jwt = \Auth::user();
		if ($jwt === null) {
			throw new NotFoundHttpException('Not has token');
		}

		$user = User::query()
		    ->select(['id', 'name', 'family'])
		    ->where('id', $user_id)
		    ->first();

		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

        $payments = Payment::query()
	        ->select(['payments.id', 'date', 'type', 'payment_systems.name', 'amount', 'currency', 'fee', 'comment', 'date_confirm', 'confirm', 'payer', 'payee'])
	        ->join('payment_systems', 'payments.payment_system', '=', 'payment_systems.code')
	        ->where('id_user', $user_id)
	        ->orderBy('date')
	        ->get();

//         view()->share('data', [
//         	'payments' => $payments,
// 	        'user_id' => $user_id,
// 	        'user' => $user
//         ]);

        $pdf = PDF::loadView('pdf.transactions', [ 'data' => [
        	'payments' => $payments,
	        'user_id' => $user_id,
	        'user' => $user
        ]]);
	    
    	return $pdf->stream();
        
//         $file_name = $user_id . '_' . $user->name . '_' . $user->family . '_transactions_' . date('Y-m-d H:i:s') . '_' . md5(date('Y-m-d H:i:s')) . '.pdf';
//         Storage::disk('pdf')->put($file_name, $pdf->stream());

//         return response()->download(storage_path('pdf') . '/' . $file_name);
    }
}
