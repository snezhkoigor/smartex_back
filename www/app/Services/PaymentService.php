<?php

namespace App\Services;

use App\Mail\ExchangeCompletedMail;
use App\Mail\IncomePaymentSucceedMail;
use App\Models\Exchange;
use App\Models\Payment;
use App\Models\User;
use App\Services\Advcash\AdvcashService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PaymentService
 * @package App\Services
 */
class PaymentService
{
	/**
	 * @param Payment $payment
	 * @return Payment
	 * @throws \Exception
	 */
	public static function confirm(Payment $payment): Payment
	{
		$exchange = Exchange::query()->where($payment->type === 1 ? 'in_id_pay' : 'out_id_pay', $payment->id)->first();
		if ($exchange === null)
		{
			throw new NotFoundHttpException('Exchange transaction not found');
		}
		$user = User::query()->where('id', $exchange->id_user)->first();
		if ($user === null)
		{
			throw new NotFoundHttpException('Exchange user transaction not found');
		}

		try
		{
			$payment->confirm = true;
		    $payment->date_confirm = Carbon::now()
			    ->format('Y-m-d H:i:s');
		    $payment->save();

			if ($payment->type === 1)
			{
			    Mail::to($user->email)->send(new IncomePaymentSucceedMail($user, $payment, $exchange));
			}
			else
			{
				if ($exchange->out_payment === 'pm')
				{
					PerfectMoneyService::processOutTransaction($exchange);
				}
				if ($exchange->out_payment === 'adv')
				{
					AdvcashService::processOutTransaction($exchange);
				}
				if ($exchange->out_payment === 'payeer')
				{
					PayeerService::processOutTransaction($exchange);
				}

				Mail::to($user->email)->send(new ExchangeCompletedMail($exchange, $user));
			}
		}
		catch (\Exception $e)
		{
			throw new NotFoundHttpException('Payment confirm error.');
		}

		return $payment;
	}
}