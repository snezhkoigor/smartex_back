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
		$columns = [];
		$result = [];

		$payment_systems_available = Commission::query()
			->select(['payment_systems.name', 'ps_commission.currency'])
			->join('payment_systems', 'payment_systems.id', '=', 'ps_commission.payment_system_id')
			->where('ps_commission.active', 1)
			->get()
			->toArray();

		if ($payment_systems_available)
		{
			$data = Commission::query()
				->select(['ps_commission.currency', 'ps_commission.commission', 'payment_account.currency as wallet_currency', 'from.name as payment_system_from', 'to.name as payment_system_to'])
				->join('payment_account', 'payment_account.id', '=', 'ps_commission.wallet_id')
				->join('payment_systems as from', 'from.id', '=', 'payment_account.payment_system_id')
				->join('payment_systems as to', 'to.id', '=', 'ps_commission.payment_system_id')
				->where('ps_commission.active', 1)
				->get()
				->toArray();

			if ($data)
			{
				foreach ($data as $item)
				{
					$from = $item['payment_system_from'] . ' (' . mb_strtolower($item['wallet_currency']) . ')';
					$to = $item['payment_system_to'] . ' (' . mb_strtolower($item['currency']) . ')';
					$result[$from]['name'] = $from;

					foreach ($payment_systems_available as $payment_system_to)
					{
						if (($payment_system_to['name'] . ' (' . mb_strtolower($payment_system_to['currency'] . ')')) === $to)
						{
							$result[$from]['items'][$payment_system_to['name']] = [
								'name'     => $payment_system_to['name'],
								'value'    => $item['commission'],
								'currency' => $item['currency']
							];
						}
					}
				}
				
				foreach ($result as $id => $row)
				{
					foreach ($payment_systems_available as $ps)
					{
						if (!array_key_exists($ps['name'], $row['items']))
						{
							$result[$id]['items'][$ps['name']] = [
								'name'     => $ps['name'],
								'value'    => null,
								'currency' => null
							];
						}
					}
					
					ksort($result[$id]['items']);
					$columns = array_keys($result[$id]['items']);
					$result[$id]['items'] = array_values($result[$id]['items']);
					
				}
			}
		}

		return [
			'columns' => $columns,
			'data' => array_values($result)
		];
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