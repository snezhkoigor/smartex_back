<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

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


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return int
	 */
	public static function getUsersCount(array $filters = [], $search_string = null)
	{
		return self::getUsersQuery($filters, $search_string)->count();
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getUsersQuery(array $filters = [], $search_string = null)
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
				case 'first_name':
				case 'last_name':
				case 'email':
				case 'created_at':
				case 'updated_at':
				case 'active':
					$query->orderBy($name, $value);
					break;
			}

		}
	}
}