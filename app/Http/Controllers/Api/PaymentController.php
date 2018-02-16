<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
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
	 * @param $user_id
	 * @return BinaryFileResponse
	 * @throws \Exception
	 */
    public function pdfTransactionsByUser($user_id)
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

        view()->share('data', [
        	'payments' => $payments,
	        'user_id' => $user_id,
	        'user' => $user
        ]);

        $pdf = PDF::loadView('pdf.transactions');
        $file_name = $user_id . '_' . $user->name . '_' . $user->family . '_transactions_' . date('Y-m-d H:i:s') . '_' . md5(date('Y-m-d H:i:s')) . '.pdf';
        Storage::disk('pdf')->put($file_name, $pdf->stream());

        return response()->download(storage_path('pdf') . '/' . $file_name);
    }
}