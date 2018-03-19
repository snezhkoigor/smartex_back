<?php

namespace App\Repositories;

use App\Models\Payment;
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