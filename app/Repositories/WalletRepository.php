<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\PaymentSystem;
use App\Models\Wallet;
use App\Transformers\WalletTransformer;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class WalletRepository
 * @package App\Repositories
 */
class WalletRepository
{
	/**
	 * @param array $filters
	 * @param array $relations
	 * @param array $fields
	 * @param null $search_string
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function getWallets(array $filters = [], array $relations = [], array $fields = ['*'], $search_string = null)
	{
		$query = Wallet::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);

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
	 * @param array $filters
	 * @return array
	 */
	public static function getAvailableWallets(array $filters = [])
	{
		$result = [];

		$query = Wallet::query();
		self::applyFiltersToQuery($query, $filters);

		$wallets = $query->get(['account', 'id']);
		foreach ($wallets as $wallet) {
			$result[$wallet['id']] = $wallet;
		}

		return $result;
	}

	/**
	 * @param array $filters
	 * @return array
	 */
	public static function getAvailableWalletsForCommission(array $filters = [])
	{
		$result = [];
		$query = Wallet::query();
		self::applyFiltersToQuery($query, $filters);

		$wallets = $query
			->with('paymentSystem')
			->get(['account', 'id', 'currency', 'payment_system_id']);

		foreach ($wallets as $wallet) {
			$result[$wallet['id']] = [
				'account' => $wallet->paymentSystem['name'] . ', ' . CurrencyRepository::getAvailableCurrencies()[$wallet['currency']]['prefix'] . ' (' . StringHelper::truncate($wallet['account'], 25) . ')',
				'id' => $wallet['id']
			];
		}

		return $result;
	}

	/**
	 * @param PaymentSystem $payment_system
	 * @return Builder
	 */
	public static function getWalletQuery(PaymentSystem $payment_system)
	{
		$query = Wallet::query();
		self::applyFiltersToQuery($query, ['payment_system_id' => $payment_system->id]);

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
				$query->where(DB::raw('LOWER(account)'), 'LIKE', '%' . mb_strtolower($search_string) . '%');
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