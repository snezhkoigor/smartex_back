<?php

namespace App\Repositories;

use App\Models\PaymentSystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentSystemRepository
 * @package App\Repositories
 */
class PaymentSystemRepository
{
	/**
	 * @param array $filters
	 * @param array $sorts
	 * @param array $relations
	 * @param array $fields
	 * @param null $search_string
	 * @param null $limit
	 * @param null $offset
	 * @return \Illuminate\Database\Eloquent\Collection|static[]|PaymentSystem[]
	 */
	public static function getPaymentSystems(array $filters = [], array $sorts = [], array $relations = [], array $fields = ['*'], $search_string = null, $limit = null, $offset = null)
	{
		$query = PaymentSystem::query();

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


	/**
	 * @param $id
	 * @return PaymentSystem
	 */
	public static function getById($id)
	{
		return PaymentSystem::query()
			->where('id', $id)
			->first();
	}


	/**
	 * @param array $filters
	 * @return array
	 */
	public static function getAvailablePaymentSystems(array $filters = [])
	{
		$result = [];

		$query = PaymentSystem::query();
		self::applyFiltersToQuery($query, $filters);
		self::applyIsDelete($query);

		$payment_systems = $query->get(['name', 'id', 'code']);
		foreach ($payment_systems as $payment_system) {
			$result[$payment_system['id']] = $payment_system;
		}

		return $result;
	}


	/**
	 * @param array $filters
	 * @return array
	 */
	public static function getRequireFields(array $filters = [])
	{
		$result = [];
		$query = PaymentSystem::query();
		self::applyFiltersToQuery($query, $filters);
		self::applyIsDelete($query);

		$fields = $query->get(['fields', 'id']);
		foreach ($fields as $field) {
			$result[$field['id']] = explode(',', $field['fields']);
		}

		return $result;
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return int
	 */
	public static function getPaymentSystemsCount(array $filters = [], $search_string = null)
	{
		return self::getPaymentSystemsQuery($filters, $search_string)->count();
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getPaymentSystemsQuery(array $filters = [], $search_string = null)
	{
		$query = PaymentSystem::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);
		self::applyIsDelete($query);

		return $query;
	}


	/**
	 * @param Builder $query
	 * @param array $filter_parameters
	 * @return Builder
	 */
	private static function applyFiltersToQuery(Builder $query, array $filter_parameters = [])
	{
		foreach ($filter_parameters as $name => $value)
		{
			$query->where($name, $value);
		}

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
				$query->where(DB::raw('LOWER(name)'), 'LIKE', '%' . mb_strtolower($search_string) . '%');
			});
		}

		return $query;
	}


	/**
	 * @param Builder $query
	 * @param array $sorts
	 * @return Builder
	 */
	private static function applySortingToQuery(Builder $query, array $sorts = [])
	{
		foreach ($sorts as $name => $value)
		{
			switch ($name)
			{
				case 'name':
					$query->orderBy($name, $value);
					break;

				default:
					$query->orderBy('created_at', 'desc');
					break;
			}

		}

		return $query;
	}


	/**
	 * @param Builder $query
	 */
	private static function applyIsDelete(Builder $query)
	{
		$query->where('is_deleted', '=',false);
	}
}