<?php

namespace App\Repositories;

use App\Helpers\StringHelper;
use App\Models\PaymentSystem;
use App\Models\Role;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class RoleRepository
 * @package App\Repositories
 */
class RoleRepository
{
	/**
	 * @param array $filters
	 * @return array
	 */
	public static function getAvailableRoles(array $filters = []): array
	{
		$result = [];

		$query = Role::query();
		self::applyFiltersToQuery($query, $filters);

		$roles = $query->get(['display_name', 'id']);
		foreach ($roles as $role) {
			$result[$role['id']] = [
				'label' => $role['display_name'],
				'value' => (int)$role['id']
			];
		}

		return $result;
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