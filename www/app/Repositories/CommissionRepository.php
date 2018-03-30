<?php

namespace App\Repositories;

use App\Models\Commission;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
	 * @param array $filters
	 * @return array
	 */
	public static function getPaymentSystemsFrom(array $filters = []): array
	{
		$result = [];

		$query = Commission::query();
		$query->select(['payment_systems.name', 'payment_systems.id as payment_system_id', 'payment_systems.logo', 'payment_account.currency', 'ps_commission.wallet_id', 'ps_commission.id'])
			->join('payment_account', 'payment_account.id', '=', 'ps_commission.wallet_id')
			->join('payment_systems', 'payment_systems.id', '=', 'payment_account.payment_system_id');

		if ($filters)
		{
			foreach ($filters as $name => $value)
			{
				switch ($name)
				{
					case 'payment_system':
						$query->where('ps_commission.payment_system_id', $value);
						break;
					case 'currency':
						$query->where('ps_commission.currency', $value);
						break;
				}
			}
		}

		$data = $query
			->get()
			->toArray();

		if ($data)
		{
			$items = [];
			foreach ($data as $item)
			{
				$items[$item['name']]['id'] = $item['payment_system_id'];
				$items[$item['name']]['name'] = $item['name'];
				$items[$item['name']]['logo'] = $item['logo'] ? Storage::disk('logo')->url($item['logo']) : '';
				$items[$item['name']]['currencies'][$item['currency']] = [
					'name' => $item['currency'],
					'id' => $item['id'],
					'wallet_id' => $item['wallet_id']
				];
			}

			foreach ($items as $item)
			{
				foreach ($item['currencies'] as $currency)
				{
					$result[] = [
						'id' => $item['id'],
						'wallet_id' => $currency['wallet_id'],
						'name' => $item['name'],
						'currency' => $currency['name'],
						'logo' => $item['logo'],
						'commission_id' => $currency['id'],
					];
				}
			}
		}

		return $result;
	}


	/**
	 * @param array $filters
	 * @return array
	 */
	public static function getPaymentSystemsTo(array $filters = []): array
	{
		$result = [];

		$query = Commission::query();
		$query->select(['payment_systems.name', 'payment_systems.id as payment_system_id', 'payment_systems.logo', 'payment_account.currency', 'ps_commission.commission', 'ps_commission.wallet_id', 'ps_commission.id' ])
			->join('payment_account', 'payment_account.id', '=', 'ps_commission.wallet_id')
			->join('payment_systems', 'payment_systems.id', '=', 'ps_commission.payment_system_id');

		if ($filters)
		{
			foreach ($filters as $name => $value)
			{
				switch ($name)
				{
					case 'payment_system':
						$query->where('payment_account.payment_system_id', $value);
						break;
					case 'currency':
						$query->where('payment_account.currency', $value);
						break;
				}
			}
		}

		$data = $query
			->get()
			->toArray();

		if ($data)
		{
			$items = [];
			foreach ($data as $item)
			{
				$items[$item['name']]['id'] = $item['payment_system_id'];
				$items[$item['name']]['name'] = $item['name'];
				$items[$item['name']]['logo'] = $item['logo'] ? Storage::disk('logo')->url($item['logo']) : '';
				$items[$item['name']]['currencies'][$item['currency']] = [
					'name' => $item['currency'],
					'id' => $item['id'],
					'commission' => $item['commission'],
					'wallet_id' => $item['wallet_id']
				];
			}

			foreach ($items as $item)
			{
				foreach ($item['currencies'] as $currency)
				{
					$result[] = [
						'id' => $item['id'],
						'wallet_id' => $currency['wallet_id'],
						'name' => $item['name'],
						'currency' => $currency['name'],
						'logo' => $item['logo'],
						'commission' => $currency['commission'],
						'commission_id' => $currency['id'],
					];
				}
			}
		}

		return $result;
	}


	/**
	 * @return array
	 */
	public static function getCommissionView(): array
	{
		$columns = [];
		$result = [];

		$payment_systems_available = Commission::query()
			->select(['payment_systems.name', 'ps_commission.currency', 'payment_systems.logo'])
			->join('payment_systems', 'payment_systems.id', '=', 'ps_commission.payment_system_id')
			->where('ps_commission.active', 1)
			->get()
			->toArray();

		if ($payment_systems_available)
		{
			$data = Commission::query()
				->select(['ps_commission.currency', 'from.logo as from_ps_logo', 'to.logo', 'ps_commission.commission', 'payment_account.currency as wallet_currency', 'from.name as payment_system_from', 'to.name as payment_system_to'])
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
					$from = $item['payment_system_from'] . ' ' . mb_strtoupper($item['wallet_currency']);
					$to = $item['payment_system_to'] . ' ' . mb_strtoupper($item['currency']);
					$result[$from]['name'] = $from;
					$result[$from]['logo'] = $item['from_ps_logo'] ? Storage::disk('logo')->url($item['from_ps_logo']) : '';

					foreach ($payment_systems_available as $payment_system_to)
					{
						if (($payment_system_to['name'] . ' ' . mb_strtoupper($payment_system_to['currency'])) === $to)
						{
							$result[$from]['items'][$payment_system_to['name'] . ' ' . mb_strtoupper($payment_system_to['currency'])] = [
								'name'     => $payment_system_to['name'] . ' ' . mb_strtoupper($payment_system_to['currency']),
								'logo'     => $payment_system_to['logo'] ? Storage::disk('logo')->url($payment_system_to['logo']) : '',
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
						if (!array_key_exists($ps['name'] . ' ' . mb_strtoupper($ps['currency']), $row['items']))
						{
							$result[$id]['items'][$ps['name'] . ' ' . mb_strtoupper($ps['currency'])] = [
								'name'     => $ps['name'] . ' ' . mb_strtoupper($ps['currency']),
								'logo'     => $ps['logo'] ? Storage::disk('logo')->url($ps['logo']) : '',
								'value'    => null,
								'currency' => null
							];
						}
						$columns[$ps['name'] . ' ' . mb_strtoupper($ps['currency'])] = [
							'name' => $ps['name'] . ' ' . mb_strtoupper($ps['currency']),
							'logo' => $ps['logo'] ? Storage::disk('logo')->url($ps['logo']) : ''
						];
					}

					ksort($result[$id]['items']);
					$result[$id]['items'] = array_values($result[$id]['items']);
					
				}
				ksort($columns);
				$columns = array_values($columns);
			}
		}

		ksort($result);
		
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