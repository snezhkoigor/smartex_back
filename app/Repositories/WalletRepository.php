<?php

namespace App\Repositories;

use App\Models\PaymentSystem;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;

class WalletRepository
{
	/**
	 * @param array $filters
	 * @param array $relations
	 * @param array $fields
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function getWallets(array $filters = [], array $relations = [], array $fields = ['*'])
	{
		$query = Wallet::query();

		self::applyFiltersToQuery($query, $filters);
		self::applyIsDelete($query);

		$query->with($relations);

		return $query->get($fields);
	}


	/**
	 * @param PaymentSystem $payment_system
	 * @return int
	 */
	public static function getWalletCount(PaymentSystem $payment_system)
	{
		return self::getWalletQuery($payment_system)->count();
	}


	/**
	 * @param PaymentSystem $payment_system
	 * @return Builder
	 */
	public static function getWalletQuery(PaymentSystem $payment_system)
	{
		$query = Wallet::query();
		self::applyFiltersToQuery($query, ['payment_system_id' => $payment_system->id]);
		self::applyIsDelete($query);

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
				case 'payment_system_id':
					$query->where('payment_system_id', $value);
					break;
			}
		}
	}


	/**
	 * @param Builder $query
	 */
	private static function applyIsDelete(Builder $query)
	{
		$query->where('is_deleted', '=',false);
	}
}