<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Connection;

class ResetPasswordRepository
{
	protected $db;

	protected $table = 'user_reset_password';

	public function __construct(Connection $db)
	{
		$this->db = $db;
	}

	public function create($user)
	{
		$activation = $this->get($user);

		if (!$activation) {
			return $this->createToken($user);
		}

		return $this->regenerateToken($user);
	}

	public function get($user)
	{
		return $this->db->table($this->table)
			->where([
				['active', 1],
				['user_id', $user->id]
			])
			->first();
	}

	public function getByToken($token)
	{
		return $this->db->table($this->table)
			->where([
				['active', 1],
				['token', $token]
			])
			->first();
	}

	public function delete($token)
	{
		return $this->db->table($this->table)
			->where('token', $token)
			->update([
				'active' => 0,
				'updated_at' => new Carbon()
			]);
	}

	protected function getToken()
	{
		return hash_hmac('sha256', str_random(40), config('app.key'));
	}

	private function regenerateToken($user)
	{
		$token = $this->getToken();

		$this->db->table($this->table)
			->where('user_id', $user->id)
			->update([
				'token' => $token,
				'updated_at' => new Carbon()
			]);

		return $token;
	}

	private function createToken($user)
	{
		$token = $this->getToken();

		$this->db->table($this->table)
			->insert([
				'user_id' => $user->id,
				'token' => $token,
				'created_at' => new Carbon()
			]);

		return $token;
	}
}