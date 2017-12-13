<?php

namespace App\Models;

use Zizaco\Entrust\EntrustRole;

/**
 *
 * @property integer $id
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 *
 * Class Role
 * @package App\Models
 */
class Role extends EntrustRole
{
	const ROLE_ADMIN = 'admin';
	const ROLE_OPERATOR = 'operator';
	const ROLE_USER = 'user';

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

	protected $dates = [
		'created_at',
		'updated_at'
	];
}