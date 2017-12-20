<?php

namespace App\Repositories;

use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NewsRepository
{
	public static function getNews(array $filters = [], array $sorts = [], array $relations = [], array $fields = ['*'], $search_string = null, $limit = null, $offset = null)
	{
		$query = News::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);
		self::applySortingToQuery($query, $sorts);
		self::applyIsDelete($query);

		if (!empty($offset)) {
			$query->skip($offset);
		}

		if (!empty($limit)) {
			$query->take($limit);
		}

		$query->with($relations);

		return $query->get($fields);
	}

	public static function getNewsCount(array $filters = [], $search_string = null)
	{
		return self::getNewsQuery($filters, $search_string)->count();
	}

	private static function getNewsQuery(array $filters = [], $search_string = null)
	{
		$query = News::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);
		self::applyIsDelete($query);

		return $query;
	}

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

	private static function applySearch(Builder $query, $search_string)
	{
		if (!empty($search_string)) {
			$query->where(function(Builder $query) use ($search_string) {
				$query->where(DB::raw('LOWER(title)'), 'LIKE', '%' . mb_strtolower($search_string) . '%');
			});
		}
	}

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

	private static function applyIsDelete(Builder $query)
	{
		return $query->where('is_deleted', '=',false);
	}
}