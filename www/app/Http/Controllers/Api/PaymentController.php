<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Http\Controllers\Controller;
use App\Mail\ExchangeCompletedMail;
use App\Mail\IncomePaymentManualConfirmMail;
use App\Models\Exchange;
use App\Models\Payment;
use App\Models\PaymentSystem;
use App\Models\User;
use App\Repositories\PaymentRepository;
use App\Services\Advcash\AdvcashService;
use App\Services\PayeerService;
use App\Services\PaymentService;
use App\Services\PerfectMoneyService;
use App\Transformers\PaymentTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
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
	 * @param $exchange_id
	 * @return JsonResponse
	 *
	 * @throws \Exception
	 */
	public function manualConfirmIncomeByUser($exchange_id): JsonResponse
	{
		$exchange = Exchange::query()->where('id', '=', $exchange_id)->first();
		if ($exchange === null) {
			throw new NotFoundHttpException('Exchange not found');
		}
		$payment = Payment::query()->where('id', '=', $exchange->in_id_pay)->first();
		if ($payment === null) {
			throw new NotFoundHttpException('Income payment not found');
		}

		try
		{
			PaymentService::confirm($payment);
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Income payment manual confirm failed', $e);
		}

		return response()->json(null, Response::HTTP_OK);
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

	    try
	    {
	    	PaymentService::confirm($payment);
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Payment confirm process failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }


	/**
	 * @param Request $request
	 * @param $ps_code
	 * @return JsonResponse
	 * @throws \Exception
	 */
    public function sci(Request $request, $ps_code): JsonResponse
    {
    	$paymentSystem = PaymentSystem::query()->where('code', $ps_code)->first();
    	if ($paymentSystem === null) {
			throw new NotFoundHttpException('No payment system found');
		}

		try
		{
			\DB::table('payment_answers_queue')
				->insert([
					'active' => true,
					'post' => json_encode($request->all()),
					'ps_code' => $ps_code,
					'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
					'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
				]);
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Payment adding process failed', $e);
		}

		return response()->json('OK', Response::HTTP_OK);
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
