<?php

namespace App\Repositories;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class ClientRepository
 * @package App\Repositories
 */
class ClientRepository
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
	public static function getClients( array $filters = [], array $sorts = [], array $relations = [], array $fields = ['*'], $search_string = null, $limit = null, $offset = null)
	{
		$query = Client::query();

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
	public static function getClientsCount(array $filters = [], $search_string = null)
	{
		return self::getClientsQuery($filters, $search_string)->count();
	}


	/**
	 * @return mixed
	 */
	public static function widgetsTotalRegistrations()
	{
		$result = [];

		$data = Client::query()
			->select(DB::raw('COUNT(id) as count, country'))
			->groupBy('country')
			->orderBy('count')
			->get();

		if ($data) {
			$result['name'] = 'Clients';
			foreach ($data as $item) {
				$result['data'][] = [ $item['country'] ?: 'unknown', $item['count'] ];
			}
		}

		return $result;
	}


	/**
	 * @param null $period_type
	 * @return array
	 */
	public static function widgetsRegistrationsAndActivations($period_type = null)
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

		$query = Client::query();
		switch ($period_type) {
			case 'year':
				$query->select(DB::raw('COUNT(id) as count, SUM(activation) as activations, DATE_FORMAT(date, \'%Y\') as date'));
				$query->groupBy(DB::raw('DATE_FORMAT(date, \'%Y\')'));
				break;
			case 'month':
				$query->select(DB::raw('COUNT(id) as count, SUM(activation) as activations, DATE_FORMAT(date, \'%m.%Y\') as date'));
				$query->groupBy(DB::raw('DATE_FORMAT(date, \'%m.%Y\')'));
				$query->whereBetween('date', [ Carbon::today()->subMonths(12), Carbon::today() ]);
				break;
			default:
				$query->select(DB::raw('COUNT(id) as count, SUM(activation) as activations, DATE_FORMAT(date, \'%d.%m.%Y\') as date'));
				$query->groupBy(DB::raw('DATE_FORMAT(date, \'%d.%m.%Y\')'));
				$query->whereBetween('date', [ Carbon::today()->subDays(7), Carbon::today() ]);
				break;
		}

		$data = $query
			->orderBy('date')
			->get();

		if ($data) {
			foreach ($data as $item) {
				$result['categories'][] = $item['date'];
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
	private static function getClientsQuery(array $filters = [], $search_string = null)
	{
		$query = Client::query();

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
				default:
//					$query->where('model_type', $value);
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
//				$query->where('name', 'regexp', '/.*' . $search_string . '.*/i')
//					->orWhereIn('company_id', $companies_ids);
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
				case 'name':
				case 'family':
				case 'email':
				case 'date':
				case 'discount':
				case 'total_exchange':
				case 'verification_ok':
				case 'activation':
					$query->orderBy($name, $value);
					break;
			}

		}
	}
}