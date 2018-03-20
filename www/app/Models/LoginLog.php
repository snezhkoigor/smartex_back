<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $browser
 * @property string $tech_browser_info
 * @property string $ip
 * @property string $geo
 * @property string $created_at
 * @property string $updated_at
 *
 * @method static LogActivity|QueryBuilder|EloquentBuilder query()
 *
 * @property User $user
 *
 * Class LogActivity
 * @package App
 */
class LoginLog extends Model
{
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function user(): HasOne
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
