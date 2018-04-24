<?php

namespace App\Repositories;

use App\Exceptions\SystemErrorException;
use App\Helpers\CurrencyHelper;
use App\Models\Commission;
use App\Models\Exchange;
use App\Models\Payment;
use App\Models\PaymentSystem;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ExchangeRepository
 * @package App\Repositories
 */
class ExchangeRepository
{
	/**
	 * @param array $filters
	 * @param array $sorts
	 * @param array $relations
	 * @param array $fields
	 * @param null $search_string
	 * @param null $limit
	 * @param null $offset
	 * @return \Illuminate\Database\Eloquent\Collection|static[]|Exchange[]
	 */
	public static function getExchanges(array $filters = [], array $sorts = [], array $relations = [], array $fields = ['*'], $search_string = null, $limit = null, $offset = null)
	{
		$query = Exchange::query();

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
	public static function getExchangesCount(array $filters = [], $search_string = null): int
	{
		return self::getExchangesQuery($filters, $search_string)->count();
	}


	/**
	 * @return array
	 */
	public static function getFinishedExchages(): array
	{
		$allCount = Exchange::query()
			->select(DB::raw('COUNT(id) as count'))
			->first();

		$finishedCount = Exchange::query()
			->select(DB::raw('COUNT(id) as count'))
			->where('in_id_pay', '<>', 0)
			->where('out_id_pay', '<>', 0)
			->first();

		return [
			'all' => $allCount ? $allCount['count'] : 0,
			'finished' => $finishedCount ? $finishedCount['count'] : 0,
			'conversion' => $allCount['count'] ? round($finishedCount['count'] * 100 / $allCount['count'], 0) : 0
		];
	}


	/**
	 * 0 - нет приходящего платежа
	 * 1 - есть приходящий, но он еще не подтвержден
	 * 2 - есть приходящий, он подвержден, но нет исходящего
	 * 3 - есть исходящий, но он еще не подтвержден
	 * 4 - все готово
	 *
	 * @param Exchange $exchange
	 * @return int
	 */
	public static function getStatus(Exchange $exchange): int
	{
		$in_payment = Payment::query()->where('id', $exchange->in_id_pay)->first();
		$out_payment = Payment::query()->where('id', $exchange->out_id_pay)->first();

		if ($in_payment === null || ($in_payment && $in_payment->confirm === 0))
		{
			return 1;
		}
		if ($out_payment && $out_payment->confirm === 0)
		{
			return 2;
		}

		return 3;
	}


	/**
	 * @param $user
	 * @param $commission_id
	 * @param $in_amount
	 * @param $out_payee
	 * @return Exchange|null
	 * @throws \Exception
	 */
	public static function createExchange($user, $commission_id, $in_amount, $out_payee): ?Exchange
	{
		$exchange = null;

		try
		{
			$ps_commission = Commission::query()->where('id', $commission_id)->first();
			$wallet = Wallet::query()->where('id', $ps_commission->wallet_id)->first();
			$payment_system = PaymentSystem::query()->where('id', $ps_commission->payment_system_id)->first();

			$discount = round($ps_commission->commission * (int)$user->discount/100, 4);
			$fee = round($in_amount * ($ps_commission->commission/100 - $discount), 4);
			$amount = (float)$in_amount - $fee;

			$exchange = new Exchange();
			$exchange->date = Carbon::today()->format('Y-m-d H:i:s');
			$exchange->id_user = $user->id;
			$exchange->in_payment = $wallet->ps_type;
			$exchange->in_id_pay = 0;
			$exchange->in_currency = $wallet->currency;
			$exchange->in_amount = $amount;
			$exchange->in_fee = $fee;
			$exchange->out_payment = $payment_system->code;
			$exchange->out_id_pay = 0;
			$exchange->out_currency = $ps_commission->currency;
			$exchange->out_amount = CurrencyHelper::convert($wallet->currency, $ps_commission->currency, $amount);
			$exchange->out_payee = $out_payee;
			$exchange->out_fee = 0;
			$exchange->in_discount = (int)$user->discount;

			$in_payment = PaymentRepository::createPayment($exchange, 1, false);
			$out_payment = PaymentRepository::createPayment($exchange, 2, false);

			$exchange->in_id_pay = $in_payment->id;
			$exchange->out_id_pay = $out_payment->id;
			
			$exchange->save();
		}
		catch (\Exception $e)
		{var_dump($e);die;
			throw new SystemErrorException('Adding exchange by user failed', $e);
		}

		return $exchange;
	}


	/**
	 * @return array
	 */
	public static function getStartedExchages(): array
	{
		$allCount = Exchange::query()
			->select(DB::raw('COUNT(id) as count'))
			->first();

		$finishedCount = Exchange::query()
			->select(DB::raw('COUNT(id) as count'))
			->where('in_id_pay', '<>', 0)
			->where('out_id_pay', 0)
			->first();

		return [
			'all' => $allCount ? $allCount['count'] : 0,
			'finished' => $finishedCount ? $finishedCount['count'] : 0,
			'conversion' => $allCount['count'] ? round($finishedCount['count'] * 100 / $allCount['count'], 0) : 0
		];
	}


	/**
	 * @return array
	 */
	public static function getNewExchages(): array
	{
		$allCount = Exchange::query()
			->select(DB::raw('COUNT(id) as count'))
			->first();

		$finishedCount = Exchange::query()
			->select(DB::raw('COUNT(id) as count'))
			->where('in_id_pay', 0)
			->where('out_id_pay', 0)
			->first();

		return [
			'all' => $allCount ? $allCount['count'] : 0,
			'finished' => $finishedCount ? $finishedCount['count'] : 0,
			'conversion' => $allCount['count'] ? round($finishedCount['count'] * 100 / $allCount['count'], 0) : 0
		];
	}


	public static function getAvailableUsers(): array
	{
		$result = [];
		$users = User::query()
			->select(['name', 'family', 'id'])
			->groupBy(['id', 'name', 'family'])
			->get();

		if ($users)
		{
			foreach ($users as $user)
			{
				if (!empty($user['name']) || !empty($user['family']))
				{
					$result[] = [
						'label' => $user['name'] . ' ' . $user['family'],
						'value' => $user['id']
					];
				}
			}
		}
		
		return $result;
	}


	/**
	 * @return array
	 */
	public static function getExchagesDynamic($date_from = null, $date_to = null): array
	{
		$result = [
			'categories' => [],
			'finished' => [
				'name' => 'Finished',
				'data' => []
			],
			'not' => [
				'name' => 'Others',
				'data' => []
			]
		];
		$date_from = $date_from ?: Carbon::now()->subMonth()->format('Y-m-d 00:00:00');
		$date_to = $date_to ?: Carbon::now()->format('Y-m-d 23:59:59');

		$finished = Exchange::query()
			->select(DB::raw('DATE_FORMAT(date, \'%d.%m.%Y\') as date_, COUNT(id) as count'))
			->where('in_id_pay', '<>', 0)
			->where('out_id_pay', '<>', 0)
			->whereBetween('date', [$date_from, $date_to])
			->groupBy(DB::raw('DATE_FORMAT(date, \'%d.%m.%Y\')'))
			->orderBy('date_')
			->get()
			->toArray();

		$started = Exchange::query()
			->select(DB::raw('DATE_FORMAT(date, \'%d.%m.%Y\') as date_, COUNT(id) as count'))
			->where(function ($query) {
				$query->where('in_id_pay', 0)
					->orWhere('out_id_pay', 0);
			})
			->whereBetween('date', [$date_from, $date_to])
			->groupBy(DB::raw('DATE_FORMAT(date, \'%d.%m.%Y\')'))
			->orderBy('date_')
			->get()
			->toArray();

		foreach ($finished as $item)
		{
			$result['categories'][$item['date_']] = $item['date_'];
			$result['finished']['data'][$item['date_']] = $item['count'];
			$result['not']['data'][$item['date_']] = 0;
		}
		foreach ($started as $item)
		{
			if (!array_key_exists($item['date_'], array_keys($result['categories'])))
			{
				$result['categories'][$item['date_']] = $item['date_'];
			}
			if (!isset($result['finished']['data'][$item['date_']]))
			{
				$result['finished']['data'][$item['date_']] = 0;
			}
			$result['not']['data'][$item['date_']] = $item['count'];
		}

		$result['finished']['data'] = array_values($result['finished']['data']);
		$result['not']['data'] = array_values($result['not']['data']);

		return $result;
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getExchangesQuery(array $filters = [], $search_string = null): Builder
	{
		$query = Exchange::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);

		return $query;
	}


	/**
	 * @param Builder $query
	 * @param array $filter_parameters
	 * @return Builder
	 */
	private static function applyFiltersToQuery(Builder $query, array $filter_parameters = []): Builder
	{
		foreach ($filter_parameters as $name => $value)
		{
			switch ($name)
			{
				case 'id':
					$query->where('id', (int)$value);
					break;
				case 'exchange_status':
					if ($value === 'create')
					{
						$query->where(function(Builder $query) {
							$query->where('in_id_pay', '<>', 0)
								->whereHas('inPayment', function(Builder $query)
								{
								    $query->where('type', 1)
									    ->whereNull('date_confirm');
								});
						});
					}
					elseif ($value === 'start')
					{
						$query->where(function(Builder $query) {
							$query->where('in_id_pay', '<>', 0)
								->whereHas('inPayment', function(Builder $query)
								{
								    $query->where('type', 1)
									    ->whereNotNull('date_confirm');
								})
								->whereHas('outPayment', function(Builder $query)
								{
								    $query->where('type', 2)
									    ->whereNull('date_confirm');
								});
						});
					}
					else
					{
						$query->where(function(Builder $query) {
							$query->where('out_id_pay', '<>', 0)
								->whereHas('inPayment', function(Builder $query)
								{
								    $query->where('type', 1)
									    ->whereNotNull('date_confirm');
								})
								->whereHas('outPayment', function(Builder $query)
								{
								    $query->where('type', 2)
									    ->whereNotNull('date_confirm');
								});
						});
					}
					break;

				case 'out_currency':
					$query->where('out_currency', $value);
					break;

				case 'in_currency':
					$query->where('in_currency', $value);
					break;

				case 'id_user':
					$query->where('id_user', (int)$value);
					break;

				case 'in_payment':
					$query->where('in_payment', $value);
					break;
					
				case 'out_payment':
					$query->where('out_payment', $value);
					break;
			}
		}

		return $query;
	}


	/**
	 * @param Builder $query
	 * @param $search_string
	 * @return Builder
	 */
	private static function applySearch(Builder $query, $search_string): Builder
	{
		if (!empty($search_string)) {
			$query->where(function(Builder $query) use ($search_string) {
				$query->where('id', '=', $search_string);
			});
		}

		return $query;
	}


	/**
	 * @param Builder $query
	 * @param array $sorts
	 * @return Builder
	 */
	private static function applySortingToQuery(Builder $query, array $sorts = []): Builder
	{
		foreach ($sorts as $name => $value)
		{
			switch ($name)
			{
				case 'id':
					$query->orderBy('id', $value);
					break;
				case 'in_id_pay':
					$query->orderBy('in_id_pay', $value);
					break;
				case 'out_id_pay':
					$query->orderBy('in_id_pay', $value);
					break;
				case 'out_date':
					$query->orderBy('out_date', $value);
					break;
				default:
					$query->orderBy('date', 'desc');
					break;
			}

		}

		return $query;
	}
}