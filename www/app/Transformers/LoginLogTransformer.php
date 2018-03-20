<?php

namespace App\Transformers;

use App\Models\LoginLog;
use League\Fractal\TransformerAbstract;

/**
 * Class LoginLogTransformer
 * @package App\Transformers
 */
class LoginLogTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'user'
	];


	/**
	 * @param LoginLog $login_log
	 * @return array
	 */
	public function transform(LoginLog $login_log): array
	{
		$data = [
			'id' => (int)$login_log->id,
			'user_id' => $login_log->user_id,
			'browser' => $login_log->browser,
			'ip' => $login_log->ip,
			'geo' => $login_log->geo,
			'created_at' => $login_log->created_at->toDateTimeString(),
			'updated_at' => $login_log->updated_at->toDateTimeString()
		];

		return $data;
	}


	/**
	 * @param LoginLog $login_log
	 * @return \League\Fractal\Resource\Item|null
	 */
	public function includeUser(LoginLog $login_log)
	{
		return $this->item($login_log->user, new UserTransformer(), 'user');
	}
}