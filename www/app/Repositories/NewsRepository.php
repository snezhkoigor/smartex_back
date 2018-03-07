<?php

namespace App\Repositories;

use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class NewsRepository
 * @package App\Repositories
 */
class NewsRepository
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
	public static function getNews(array $filters = [], array $sorts = [], array $relations = [], array $fields = ['*'], $search_string = null, $limit = null, $offset = null)
	{
		$query = News::query();

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
	public static function getNewsCount(array $filters = [], $search_string = null): int
	{
		return self::getNewsQuery($filters, $search_string)->count();
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getNewsQuery(array $filters = [], $search_string = null): Builder
	{
		$query = News::query();

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
				$query->where(DB::raw('LOWER(title)'), 'LIKE', '%' . mb_strtolower($search_string) . '%');
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
				case 'title':
				case 'text':
				case 'date':
					$query->orderBy($name, $value);
					break;

				default:
					$query->orderBy('date', 'desc');
					break;
			}

		}
	}
}