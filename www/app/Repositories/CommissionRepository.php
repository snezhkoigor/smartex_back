<?php

namespace App\Repositories;

use App\Models\Commission;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CommissionRepository
{
	/**
	 * @param array $filters
	 * @param array $relations
	 * @param array $fields
	 * @param null $search_string
	 * @param null $limit
	 * @param null $offset
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function getCommissions(array $filters = [], array $relations = [], array $fields = ['*'], $search_string = null, $limit = null, $offset = null)
	{
		$query = Commission::query();

		self::applyFiltersToQuery($query, $filters);
		self::applySearch($query, $search_string);

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
	 * @return array
	 */
	public static function getCommissionView(): array
	{
		$result = [];

		$payment_systems_available = Commission::query()
			->join('payment_systems', 'payment_systems.id', '=', 'ps_commission.payment_system_id')
			->get(['payment_systems.name'])
			->where('ps_commission.active', 1)
			->pluck('name');

		if ($payment_systems_available)
		{
			$data = Commission::query()
				->select(['ps_commission.currency', 'ps_commission.commission', 'from.name as payment_system_from', 'to.name as payment_system_to'])
				->join('payment_account', 'payment_account.id', '=', 'ps_commission.wallet_id')
				->join('payment_systems as from', 'from.id', '=', 'payment_account.payment_system_id')
				->join('payment_systems as to', 'to.id', '=', 'ps_commission.payment_system_id')
				->where('ps_commission.active', 1)
				->get()
				->toArray();

			if ($data)
			{
				foreach ($payment_systems_available as $payment_system_to)
				{
					foreach ($data as $payment_system_from)
					{
						$result[$payment_system_from['payment_system_from']]['name'] = $payment_system_from['payment_system_from'];
						
						if (\in_array($payment_system_to, $payment_system_from))
						{
							$result[$payment_system_from['payment_system_from']]['items'][] = [
								'name' => $payment_system_to,
								'value' => $payment_system_from['commission'],
								'currency' => $payment_system_from['currency']
							];
						}
						else
						{
							$result[$payment_system_from['payment_system_from']]['items'][] = [
								'name' => $payment_system_to,
								'value' => null,
								'currency' => null
							];
						}
					}
				}
			}
		}

		return array_values($result);
	}
	
	
	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return int
	 */
	public static function getCommissionsCount(array $filters = [], $search_string = null): int
	{
		return self::getCommissionsQuery($filters, $search_string)->count();
	}


	/**
	 * @param array $filters
	 * @param null $search_string
	 * @return Builder
	 */
	private static function getCommissionsQuery(array $filters = [], $search_string = null): Builder
	{
		$query = Commission::query();

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
				$query->whereIn('wallet_id', Wallet::where(DB::raw('LOWER(account)'), 'LIKE', '%' . mb_strtolower($search_string) . '%')->pluck('id')->toArray());
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
				case 'wallet_id':
					$query->where('wallet_id', $value);
					break;

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