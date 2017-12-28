<?php

namespace App\Transformers;

use App\Models\Role;
use League\Fractal\TransformerAbstract;

/**
 * Class RoleTransformer
 * @package App\Transformers
 */
class RoleTransformer extends TransformerAbstract
{
	protected $availableIncludes = [];


	/**
	 * @param Role $role
	 * @return array
	 */
	public function transform(Role $role)
	{
		$data = [
			'id' => (int)$role->id,
			'name' => $role->name,
			'display_name' => $role->display_name,
			'description' => $role->description,
			'created_at' => $role->created_at,
			'updated_at' => $role->updated_at
		];

		return $data;
	}
}