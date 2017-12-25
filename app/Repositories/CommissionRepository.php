<?php

namespace App\Repositories;

use App\Models\Commission;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CommissionRepository
{
	/**
	 * @param array $filters
	 * @param array $relations
	 * @param array $fields
	 * @param null $search_string
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function getCommissions(array $filters = [], array $relations = [], array $fields = ['*'], $search_string = null)
	{
		$query = Commission::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);
		self::applyIsDelete($query);

		$query->with($relations);

		return $query->get($fields);
	}


	/**
	 * @param Builder $query
	 */
	private static function applyIsDelete(Builder $query)
	{
		$query->where('is_deleted', '=',false);
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return int
	 */
	public static function getCommissionsCount(array $filters = [], $search_string = null)
	{
		return self::getCommissionsQuery($filters, $search_string)->count();
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getCommissionsQuery(array $filters = [], $search_string = null)
	{
		$query = Commission::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);

		return $query;
	}


	/**
	 * @param Builder $query
	 * @param $search_string
	 * @return Builder
	 */
	private static function applySearch(Builder $query, $search_string)
	{
		if (!empty($search_string)) {
			$query->where(function(Builder $query) use ($search_string) {
				$query->whereIn('wallet_id', Wallet::where(DB::raw('LOWER(account)'), 'LIKE', '%' . mb_strtolower($search_string) . '%')->pluck('id')->toArray());
			});
		}

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
				case 'wallet_id':
					$query->where('wallet_id', $value);
					break;

				case 'currency':
					$query->where('currency', $value);
					break;

				case 'payment_system_id':
					$query->where('payment_system_id', $value);
					break;
			}
		}
	}
}