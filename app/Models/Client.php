<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property integer $id
 * @property integer $refer
 * @property string $email
 * @property string $password
 * @property string $name
 * @property string $family
 * @property string $lang
 * @property string $country
 * @property string $date
 * @property boolean $activation
 * @property integer $auth_err
 * @property string $auth_err_date
 * @property string $auth_err_ip
 * @property string $ip
 * @property string $online
 * @property string $role
 * @property integer $discount
 * @property double $total_exchange
 * @property string $document_number
 * @property string $verification_image
 * @property boolean $verification_ok
 *
 * Class Client
 * @package App\Models
 */
class Client extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'refer',
		'email',
		'name',
		'family',
		'date',
		'auth_err',
		'auth_err_date',
		'auth_err_ip',
		'ip',
		'online',
		'role',
		'discount',
		'total_exchange',
	];

	protected $guarded = [
		'lang',
		'password',
		'country',
		'activation',
		'document_number',
		'verification_image',
		'verification_ok',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
	];
}