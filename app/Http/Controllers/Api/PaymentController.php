<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use PDF;

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
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
	 */
    public function pdfTransactionsByUser($user_id, $pdf = null)
    {
    	$user = User::query()
		    ->select(['id', 'name', 'family'])
		    ->where('id', $user_id)
		    ->first();

        $payments = Payment::query()
	        ->select(['payments.id', 'date', 'type', 'payment_systems.name', 'amount', 'currency', 'fee', 'comment', 'date_confirm', 'confirm', 'payer', 'payee'])
	        ->join('payment_systems', 'payments.payment_system', '=', 'payment_systems.code')
	        ->where('id_user', $user_id)
	        ->orderBy('date')
	        ->get();

        view()->share('data', [
        	'payments' => $payments,
	        'user_id' => $user_id,
	        'pdf' => $pdf,
	        'user' => $user
        ]);

        if ($pdf)
        {
            $pdf = PDF::loadView('pdf.transactions');
            return $pdf->download($user_id . '_' . $user->name . '_' . $user->family . '_transactions_' . date('Y-m-d H:i:s') . '.pdf');
        }

        return view('pdf.transactions');
    }
}