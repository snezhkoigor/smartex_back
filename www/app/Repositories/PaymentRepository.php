<?php

namespace App\Repositories;

use App\Exceptions\SystemErrorException;
use App\Models\Exchange;
use App\Models\Payment;
use App\Services\Advcash\AdvcashService;
use App\Services\BtcService;
use App\Services\EtcService;
use App\Services\PayeerService;
use App\Services\PerfectMoneyService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentRepository
 * @package App\Repositories
 */
class PaymentRepository
{
	/**
	 * @param array $filters
	 * @param array $sorts
	 * @param array $relations
	 * @param array $fields
	 * @param null $search_string
	 * @param null $limit
	 * @param null $offset
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function getPayments( array $filters = [], array $sorts = [], array $relations = [], array $fields = ['*'], $search_string = null, $limit = null, $offset = null)
	{
		$query = Payment::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);
		self::applySortingToQuery($query, $sorts);

		if (!empty($offset)) {
			$query->skip($offset);
		}

		if (!empty($limit)) {
			$query->take($limit);
		}

		$query->with($relations);

		return $query->get($fields);
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return int
	 */
	public static function getPaymentsCount(array $filters = [], $search_string = null): int
	{
		return self::getPaymentsQuery($filters, $search_string)->count();
	}
	
	
	/**
	 * @param $ps_code
	 * @param $wallet_id
	 * @param $amount
	 * @param $currency
	 * @param $exchange_id
	 * @return array
	 * @throws \Exception
	 */
	public static function getFormRedirect($ps_code, $wallet_id, $amount, $currency, $exchange_id): array
	{
		switch ($ps_code)
		{
			case 'pm':
				return PerfectMoneyService::getForm($wallet_id, $amount, $currency, $exchange_id);
			case 'adv':
				return AdvcashService::getForm($wallet_id, $amount, $currency, $exchange_id);
			case 'payeer':
				return PayeerService::getForm($wallet_id, $amount, $currency, $exchange_id);
			case 'btc':
				return BtcService::getForm($wallet_id, $amount, $currency, $exchange_id);
			case 'etc':
				return EtcService::getForm($wallet_id, $amount, $currency, $exchange_id);
		}

		return [];
	}
	
	
	/**
	 * @param Exchange $exchange
	 * @param $type
	 * @param $confirmed
	 * @return Payment
	 * @throws \Exception
	 */
	public static function createPayment(Exchange $exchange, $type = 1, $confirmed = false): Payment
	{
		try
	    {
	        $payment = new Payment();
	        $payment->id_user = $exchange->id_user;
	        $payment->id_account = 0;
	        $payment->date = Carbon::now()
		        ->format('Y-m-d H:i:s');
	        $payment->type = $type;
	        $payment->payment_system = $type === 2 ? $exchange->out_payment : $exchange->in_payment;
	        $payment->payer = $type === 2 ? $exchange->out_payer : null;
	        $payment->payee = $type === 2 ? $exchange->out_payee : $exchange->in_payee;
	        $payment->amount = $type === 2 ? $exchange->out_amount : $exchange->in_amount;
	        $payment->currency = $type === 2 ? $exchange->out_currency : $exchange->in_currency;
	        $payment->fee = $type === 2 ? $exchange->out_fee : $exchange->in_fee;
	        $payment->batch = $type === 2 ? $exchange->out_batch : null;
	        $payment->confirm = $confirmed;
	        $payment->date_confirm = $confirmed ? Carbon::now()->format('Y-m-d H:i:s') : null;
			$payment->comment = null;
			$payment->btc_check = false;

	        $payment->save();
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Creating out payment by confirm in payment failed', $e);
	    }

	    return $payment;
	}

	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getPaymentsQuery(array $filters = [], $search_string = null): Builder
	{
		$query = Payment::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);

		return $query;
	}


	/**
	 * @param Builder $query
	 * @param array $filter_parameters
	 */
	private static function applyFiltersToQuery(Builder $query, array $filter_parameters = [])
	{
		foreach ($filter_parameters as $name => $value)
		{
			switch ($name)
			{
				case 'type':
					$query->where('type', (int)$value);
					break;
				case 'id_user':
					$query->where('id_user', (int)$value);
					break;
			}
		}
	}


	/**
	 * @param Builder $query
	 * @param $search_string
	 */
	private static function applySearch(Builder $query, $search_string)
	{
//		if (!empty($search_string)) {
//			$query->where(function(Builder $query) use ($search_string) {
//				$query->where(DB::raw('LOWER(name)'), 'LIKE', '%' . mb_strtolower($search_string) . '%')
//					->orWhere(DB::raw('LOWER(family)'), 'LIKE', '%' . mb_strtolower($search_string) . '%')
//					->orWhere(DB::raw('LOWER(email)'), 'LIKE', '%' . mb_strtolower($search_string) . '%')
//					->orWhere('id', (int)$search_string);
//			});
//		}
	}


	/**
	 * @param Builder $query
	 * @param array $sorts
	 */
	private static function applySortingToQuery(Builder $query, array $sorts = [])
	{
		foreach ($sorts as $name => $value)
		{
			switch ($name)
			{
				case 'id':
				case 'date':
					$query->orderBy($name, $value);
					break;
			}

		}
	}


	/**
	 * @return array
	 */
	public static function getFinishedInPayments(): array
	{
		$allCount = Payment::query()
			->select(DB::raw('COUNT(id) as count'))
			->where('type', 1)
			->first();

		$finishedCount = Payment::query()
			->select(DB::raw('COUNT(id) as count'))
			->where('type', 1)
			->where('confirm', 1)
			->first();

		return [
			'all' => $allCount ? $allCount['count'] : 0,
			'finished' => $finishedCount ? $finishedCount['count'] : 0,
			'conversion' => $allCount ? round($finishedCount['count'] * 100 / $allCount['count'], 2) : 0
		];
	}
	
	
	/**
	 * @param $date_from
	 * @param $date_to
	 * @return array
	 */
	public static function getCurrenciesInPayments($date_from = null, $date_to = null): array
	{
		$result = [];
		$date_from = $date_from ?: Carbon::now()->subMonth()->format('Y-m-d 00:00:00');
		$date_to = $date_to ?: Carbon::now()->format('Y-m-d 23:59:59');

		$data = Payment::query()
			->select(DB::raw('currency, COUNT(id) as count'))
			->where('type', 1)
			->where('currency', '<>', '')
			->whereBetween('date', [$date_from, $date_to])
			->groupBy('currency')
			->get()
			->toArray();
		
		if ($data)
		{
			foreach ($data as $item)
			{
				$result['categories'][] = $item['currency'];
				$result['data']['data'][] = [
					'name' => $item['currency'],
		            'y' => $item['count']
				];
			}
			$result['data']['name'] = 'Currencies';
		}

		return $result;
	}


	/**
	 * @param $date_from
	 * @param $date_to
	 * @return array
	 */
	public static function getCurrenciesOutPayments($date_from = null, $date_to = null): array
	{
		$result = [];
		$date_from = $date_from ?: Carbon::now()->subMonth()->format('Y-m-d 00:00:00');
		$date_to = $date_to ?: Carbon::now()->format('Y-m-d 23:59:59');

		$data = Payment::query()
			->select(DB::raw('currency, COUNT(id) as count'))
			->where('type', 2)
			->where('currency', '<>', '')
			->whereBetween('date', [$date_from, $date_to])
			->groupBy('currency')
			->get()
			->toArray();
		
		if ($data)
		{
			foreach ($data as $item)
			{
				$result['categories'][] = $item['currency'];
				$result['data']['data'][] = [
					'name' => $item['currency'],
		            'y' => $item['count']
				];
			}
			$result['data']['name'] = 'Currencies';
		}

		return $result;
	}
}