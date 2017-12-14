<?php

namespace App\Transformers;

use App\Models\Role;
use League\Fractal\TransformerAbstract;

class RoleTransformer extends TransformerAbstract
{
	protected $availableIncludes = [];

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