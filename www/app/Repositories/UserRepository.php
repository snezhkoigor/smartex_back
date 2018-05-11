<?php

namespace App\Repositories;

use App\Helpers\CurrencyHelper;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\LogActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class UserRepository
 * @package App\Repositories
 */
class UserRepository
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
	public static function getUsers( array $filters = [], array $sorts = [], array $relations = [], array $fields = ['*'], $search_string = null, $limit = null, $offset = null)
	{
		$query = User::query();

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


	public static function updateDiscount(User $user): void
	{
		$query = Exchange::query()
			->where('id_user', $user->id);
		$query->whereHas('inPayment', function(Builder $query) use ($user)
        {
            $query->where('id_user', (int)$user->id)
                ->where('confirm', 1);
        });
		$query->whereHas('outPayment', function(Builder $query) use ($user)
        {
            $query->where('id_user', (int)$user->id)
                ->where('confirm', 1);
        });

		$exchanges = $query->get();
		
		if ($exchanges)
		{
			$sum = 0;
			foreach ($exchanges as $exchange)
			{
				$sum += CurrencyHelper::convert($exchange->in_currency, Currency::CURRENCY_USD, $exchange->in_amount);
			}

			if ($sum > 0)
			{
				if ($sum >= 10 && $sum < 1000)
				{
					$user->discount = 3;
				}
				elseif ($sum >= 1000 && $sum < 10000)
				{
					$user->discount = 5;
				}
				elseif ($sum >= 10000 && $sum < 100000)
				{
					$user->discount = 7;
				}
				else
				{
					$user->discount = 15;
				}

				$user->save();
			}
		}
	}
	
	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return int
	 */
	public static function getUsersCount(array $filters = [], $search_string = null): int
	{
		return self::getUsersQuery($filters, $search_string)->count();
	}


	public static function getErrorCodeByCreatingExchange($amount, $in): int
	{
		$amount_in_eur = CurrencyHelper::convert($in, Currency::CURRENCY_EUR, $amount);
		$user = \Auth::user();

		if ($user !== null && $amount_in_eur >= 1000 && (!$user->verification_ok || !$user->verification_kyc_ok || !$user->verification_phone_ok))
		{
			return 1;
		}
		if ($user === null && $amount_in_eur >= 1000)
		{
			return 2;
		}

		return 0;
	}


	/**
	 * @param $amount
	 * @param $in
	 * @return bool
	 */
	public static function canDoExchange($amount, $in): bool
	{
		$amount_in_eur = CurrencyHelper::convert($in, Currency::CURRENCY_EUR, $amount);
		$user = \Auth::user();

		if ($user !== null)
		{
//			if ($amount_in_eur < 1000)
//			{
//				return true;
//			}
			if ($amount_in_eur >= 1000 && $user->verification_ok && $user->verification_kyc_ok && $user->verification_phone_ok)
			{
				return true;
			}
		}

		return $amount_in_eur < 1000/* && $user === null*/;
	}


	/**
	 * @return array
	 */
	public static function getAvailableUsersInLogActivitiesForMeta(): array
	{
		$result = [];

		$users = User::query()
			->whereIn('id', LogActivity::all()->pluck('causer_id')->toArray())
			->get(['id', 'name', 'family']);

		if ($users) {
			foreach ($users as $user) {
				$result[$user['id']] = [
					'id' => $user['id'],
					'name' => $user['name'] . ' ' . $user['family'],
					'value' => $user['id'],
					'label' => $user['name'] . ' ' . $user['family']
				];
			}
		}

		return $result;
	}


	/**
	 * @return mixed
	 */
	public static function widgetsTotalRegistrations()
	{
		$result = [];

		$data = User::query()
			->select(DB::raw('COUNT(id) as count, country'))
			->groupBy('country')
			->orderBy('count')
			->get();

		if ($data) {
			$result['name'] = 'Clients';
			foreach ($data as $item) {
				$result['data'][] = [ $item['country'] ?: 'unknown', (int)$item['count'] ];
			}
		}

		return $result;
	}


	/**
	 * @param null $period_type
	 * @return array
	 */
	public static function widgetsRegistrationsAndActivations($period_type = null): array
	{
		$result = [
			'categories' => [],
			'registrations' => [
				'name' => 'Registrations',
				'data' => []
			],
			'activations' => [
				'name' => 'Activations',
				'data' => []
			]
		];

		$query = User::query();
		switch ($period_type) {
			case 'year':
				$query->select(DB::raw('COUNT(id) as count, SUM(activation) as activations, DATE_FORMAT(date, \'%Y\') as date_'));
				$query->groupBy(DB::raw('DATE_FORMAT(date, \'%Y\')'));
				break;
			case 'month':
				$query->select(DB::raw('COUNT(id) as count, SUM(activation) as activations, DATE_FORMAT(date, \'%m.%Y\') as date_'));
				$query->groupBy(DB::raw('DATE_FORMAT(date, \'%m.%Y\')'));
				$query->whereBetween('date', [ Carbon::today()->subMonths(12), Carbon::today() ]);
				break;
			default:
				$query->select(DB::raw('COUNT(id) as count, SUM(activation) as activations, DATE_FORMAT(date, \'%d.%m.%Y\') as date_'));
				$query->groupBy(DB::raw('DATE_FORMAT(date, \'%d.%m.%Y\')'));
				$query->whereBetween('date', [ Carbon::today()->subDays(7), Carbon::today() ]);
				break;
		}

		$data = $query
			->orderBy('date_')
			->get();

		if ($data) {
			foreach ($data as $item) {
				$result['categories'][] = (string)$item['date_'];
				$result['registrations']['data'][] = (int)$item['count'];
				$result['activations']['data'][] = (int)$item['activations'];
			}
		}

		return $result;
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getUsersQuery(array $filters = [], $search_string = null): Builder
	{
		$query = User::query();

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
				case 'verification_ok':
					$query->where('verification_ok', (int)$value);
					break;
				case 'refer':
					$query->where('refer', (int)$value);
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
		if (!empty($search_string)) {
			$query->where(function(Builder $query) use ($search_string) {
				$query->where(DB::raw('LOWER(name)'), 'LIKE', '%' . mb_strtolower($search_string) . '%')
					->orWhere(DB::raw('LOWER(family)'), 'LIKE', '%' . mb_strtolower($search_string) . '%')
					->orWhere(DB::raw('LOWER(email)'), 'LIKE', '%' . mb_strtolower($search_string) . '%')
					->orWhere('id', (int)$search_string);
			});
		}
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
				case 'first_name':
				case 'last_name':
				case 'email':
				case 'created_at':
				case 'updated_at':
				case 'active':
				case 'date':
				case 'comment':
					$query->orderBy($name, $value);
					break;
			}

		}
	}
}