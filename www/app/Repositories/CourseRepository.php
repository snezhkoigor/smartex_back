<?php

namespace App\Repositories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class CourseRepository
 * @package App\Repositories
 */
class CourseRepository
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


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return int
	 */
	public static function getCoursesCount(array $filters = [], $search_string = null): int
	{
		return self::getCoursesQuery($filters, $search_string)->count();
	}


	/**
	 * @return \Illuminate\Database\Eloquent\Model|null|static
	 */
	public static function getLastDateFromCourses()
	{
		return Course::query()
			->select('date')
			->orderBy('date', 'desc')
			->first();
	}


	public static function getCourse($from, $to)
	{
		$last_date = self::getLastDateFromCourses();

		if ($last_date)
		{
			return Course::query()
				->select('course')
				->where([
					[ 'date', $last_date->date ],
					[ 'in_currency', mb_strtoupper($from) ],
					[ 'out_currency', mb_strtoupper($to) ]
				])
				->pluck('course')
				->first();
		}

		return 0;
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getCoursesQuery(array $filters = [], $search_string = null): Builder
	{
		$query = Course::query();

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


	/**
	 * @param Builder $query
	 * @param $search_string
	 */
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