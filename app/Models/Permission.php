<?php

namespace App\Models;

use Zizaco\Entrust\EntrustPermission;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 *
 * @property integer $id
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 *
 * @method static Permission|QueryBuilder|EloquentBuilder query()
 *
 * Class Permission
 * @package App\Models
 */
class Permission extends EntrustPermission
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'display_name',
		'description'
	];

	protected $dates = ['created_at', 'updated_at'];
}