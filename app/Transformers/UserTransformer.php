<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'roles'
	];

	public function transform(User $user)
	{
		$data = [
			'id' => $user->id,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'email' => $user->email,
			'active' => $user->active,
			'created_at' => $user->created_at,
			'updated_at' => $user->updated_at
		];

		return $data;
	}

	public function includeRoles(User $user)
	{
		return $this->collection($user->roles, new RoleTransformer(), 'roles');
	}
}