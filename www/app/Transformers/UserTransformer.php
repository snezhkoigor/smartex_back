<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Resource\Collection;

class UserTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'roles',
		'activities'
	];


	/**
	 * @param User $user
	 * @return array
	 */
	public function transform(User $user): array
	{
		$data = [
			'id' => (int)$user->id,
			'name' => $user->name,
			'family' => $user->family,
			'email' => $user->email,
			'avatar' => $user->avatar,
			'avatar_link' => $user->avatar ? Storage::disk('avatars')->url($user->avatar) : '',
			'activation' => (bool)$user->activation,
			'date' => $user->date,
			'refer' => $user->refer,
			'lang' => $user->lang,
			'country' => $user->country,
			'auth_err' => $user->auth_err,
			'auth_err_date' => $user->auth_err_date,
			'auth_err_ip' => $user->auth_err_ip,
			'ip' => $user->ip,
			'online' => $user->online,
			'discount' => $user->discount,
			'total_exchange' => $user->total_exchange,
			'document_number' => $user->document_number,
			'comment' => $user->comment,
			'address' => $user->address,
			'verification_image' => $user->verification_image ? Storage::disk('verifications')->url($user->verification_image) : '',
			'verification_ok' => (bool)$user->verification_ok,
			'verification_kyc' => $user->verification_kyc ? Storage::disk('verifications')->url($user->verification_kyc) : '',
			'verification_kyc_ok' => (bool)$user->verification_kyc_ok,
		];

		return $data;
	}


	/**
	 * @param User $user
	 * @return \League\Fractal\Resource\Collection
	 */
	public function includeRoles(User $user): Collection
	{
		return $this->collection($user->roles, new RoleTransformer(), 'roles');
	}


	/**
	 * @param User $user
	 * @return \League\Fractal\Resource\Collection
	 */
	public function includeActivities(User $user): Collection
	{
		return $this->collection($user->activities, new LogActivityTransformer(), 'activities');
	}
}