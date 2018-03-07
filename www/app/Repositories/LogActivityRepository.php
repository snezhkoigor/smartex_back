<?php

namespace App\Repositories;

use App\Models\LogActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LogActivityRepository
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
	public static function getLogActivities(array $filters = [], array $sorts = [], array $relations = [], array $fields = ['*'], $search_string, $limit = null, $offset = null)
	{
		$query = LogActivity::query();

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
	public static function getLogActivitiesCount(array $filters = [], $search_string = null): int
	{
		return self::getLogActivitiesQuery($filters, $search_string)->count();
	}


	/**
	 * @return array
	 */
	public static function getAvailableSubjectTypesForMeta(): array
	{
		$result = [];
		$types = LogActivity::query()
			->groupBy('subject_type')
			->get(['subject_type']);

		if ($types) {
			foreach ($types as $type) {
				$subject_type_array = explode('\\', $type['subject_type']);
				$result[$type['subject_type']] = [
					'name' => $subject_type_array[\count($subject_type_array) - 1],
					'id' => $type['subject_type'],
					'label' => $subject_type_array[\count($subject_type_array) - 1],
					'value' => $type['subject_type']
				];
			}
		}

		return $result;
	}


	/**
	 * @return array
	 */
	public static function getAvailableLogNamesForMeta(): array
	{
		$result = [];
		$types = LogActivity::query()
			->groupBy('log_name')
			->get(['log_name']);

		if ($types) {
			foreach ($types as $type) {
				$result[$type['log_name']] = [
					'name' => $type['log_name'],
					'id' => $type['log_name'],
					'label' => $type['log_name'],
					'value' => $type['log_name']
				];
			}
		}

		return $result;
	}

	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getLogActivitiesQuery(array $filters = [], $search_string = null): Builder
	{
		$query = LogActivity::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);

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
				$query->where(DB::raw('LOWER(description)'), 'LIKE', '%' . mb_strtolower($search_string) . '%');
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
				case 'log_name':
					$query->where('log_name', $value);
					break;

				case 'subject_type':
					$query->where('subject_type', $value);
					break;

				case 'causer_id':
					$query->where('causer_id', $value);
					break;
			}
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
				case 'created_at':
					$query->orderBy($name, $value);
					break;
					
				default:
					$query->orderBy('id');
					break;
			}

		}
	}
}