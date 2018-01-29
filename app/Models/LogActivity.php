<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 *
 * @property integer $id
 * @property string $log_name
 * @property string $description
 * @property integer $subject_id
 * @property string $subject_type
 * @property integer $causer_id
 * @property string $causer_type
 * @property string $properties
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
class LogActivity extends Activity
{
	public function user()
	{
		return $this->hasOne(User::class, 'id', 'causer_id');
	}
}
