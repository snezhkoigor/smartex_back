<?php

namespace App\Repositories;

use App\Models\Commission;
use Illuminate\Database\Eloquent\Builder;

class CommissionRepository
{
	public static function getCommissions(array $relations = [], array $fields = ['*'])
	{
		$query = Commission::query();

		self::applyIsDelete($query);

		$query->with($relations);

		return $query->get($fields);
	}

	private static function applyIsDelete(Builder $query)
	{
		return $query->where('is_deleted', '=',false);
	}
}