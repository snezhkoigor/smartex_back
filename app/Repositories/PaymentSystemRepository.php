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
	 * @return \Illuminate\Database\Eloquent\Model|null|static
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
	public static function getAvailablePaymentSystems(array $filters = []): array
	{
		$result = [];

		$query = PaymentSystem::query();
		self::applyFiltersToQuery($query, $filters);

		$payment_systems = $query
			->where('active', 1)
			->get(['id', 'name', 'code', 'is_account_multi_line', 'fields']);
		foreach ($payment_systems as $payment_system) {
			$result[(int)$payment_system['id']] = $payment_system;

			$result[(int)$payment_system['id']]['label'] = $payment_system['name'];
			$result[(int)$payment_system['id']]['value'] = $payment_system['id'];

			$result[(int)$payment_system['id']]['fields'] = explode(',', $payment_system['fields']);
		}

		return $result;
	}


	/**
	 * @param array $filters
	 * @return array
	 */
	public static function getRequireFields(array $filters = []): array
	{
		$result = [];
		$query = PaymentSystem::query();
		self::applyFiltersToQuery($query, $filters);

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
	public static function getPaymentSystemsCount(array $filters = [], $search_string = null): int
	{
		return self::getPaymentSystemsQuery($filters, $search_string)->count();
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getPaymentSystemsQuery(array $filters = [], $search_string = null): Builder
	{
		$query = PaymentSystem::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);

		return $query;
	}


	/**
	 * @param Builder $query
	 * @param array $filter_parameters
	 * @return Builder
	 */
	private static function applyFiltersToQuery(Builder $query, array $filter_parameters = []): Builder
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
	private static function applySearch(Builder $query, $search_string): Builder
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
	private static function applySortingToQuery(Builder $query, array $sorts = []): Builder
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
}