<?php

namespace App\Repositories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CourseRepository
{
	public static function getCourses(array $filters = [], array $sorts = [], array $relations = [], array $fields = ['*'], $search_string = null, $limit = null, $offset = null)
	{
		$query = Course::query();

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

	public static function getNewsCount(array $filters = [], $search_string = null)
	{
		return self::getNewsQuery($filters, $search_string)->count();
	}

	public static function getLastDateFromCourses()
	{
		return Course::query()
			->select('date')
			->orderBy('date', 'desc')
			->first();
	}

	private static function getNewsQuery(array $filters = [], $search_string = null)
	{
		$query = Course::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);

		return $query;
	}

	private static function applyFiltersToQuery(Builder $query, array $filter_parameters = [])
	{
		foreach ($filter_parameters as $name => $value) {
			if (!empty($value)) {
				switch ($name) {
					case 'date':
						$query->where('date', '=', $value);
						break;
				}
			}
		}
	}

	private static function applySearch(Builder $query, $search_string)
	{
		if (!empty($search_string)) {
			$query->where(function(Builder $query) use ($search_string) {
				if (strpos($search_string, '/') !== false) {
					$query->where(DB::raw('CONCAT(in_currency, \'/\', out_currency)'), '=', mb_strtolower($search_string));
				} else {
					$query->where(DB::raw('LOWER(in_currency)'), 'LIKE', '%' . mb_strtolower($search_string) . '%')
						->orWhere(DB::raw('LOWER(out_currency)'), 'LIKE', '%' . mb_strtolower($search_string) . '%');
				}
			});
		}
	}

	private static function applySortingToQuery(Builder $query, array $sorts = [])
	{
		foreach ($sorts as $name => $value)
		{
			switch ($name)
			{
				case 'date':
				case 'in_currency':
				case 'out_currency':
				case 'course':
					$query->orderBy($name, $value);
					break;

				default:
					$query->orderBy('date', 'desc');
					break;
			}

		}
	}
}