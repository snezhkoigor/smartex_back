<?php

namespace App\Repositories;

use App\Models\Exchange;
use App\Models\PaymentSystem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
							$query->where('in_id_pay', '=', 0)
								->where('out_id_pay', '=', 0);
						});
					}
					elseif ($value === 'start')
					{
						$query->where(function(Builder $query) {
							$query->where('in_id_pay', '<>', 0)
								->where('out_id_pay', '=', 0);
						});
					}
					else
					{
						$query->where(function(Builder $query) {
							$query->where('in_id_pay', '<>', 0)
								->where('out_id_pay', '<>', 0);
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