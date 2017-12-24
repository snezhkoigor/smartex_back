<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Delatbabel\Elocrypt\Elocrypt;

/**
 * @property integer $id
 * @property string $name
 * @property integer $payment_system_id
 * @property string $ps_type
 * @property string $currency
 * @property string $account
 * @property string $user
 * @property string $password
 * @property string $secret
 * @property string $adv_sci
 * @property string $id_payee
 * @property double $balance
 * @property boolean $active
 * @property boolean $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PaymentSystem $paymentSystem
 *
 * Class PaymentAccount
 * @package App\Models
 */
class Wallet extends Model
{
	use Elocrypt;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'currency',
		'payment_system_id',
		'adv_sci',
		'id_payee',
		'account',
		'balance',
		'user',
		'secret',
		'password',
	];

	protected $table = 'payment_account';

	protected $guarded = [
		'active',
		'ps_type',
		'is_deleted',
	];

	/**
	 * The attributes that should be encrypted on save.
	 *
	 * @var array
	 */
	protected $encrypts = [
		'user',
		'secret',
		'password',
	];

	protected $hidden = [
		'user',
		'secret',
		'password',
		'adv_sci',
		'id_payee',
	];

	protected $dates = [
		'created_at',
		'updated_at'
	];

	public function commissions()
	{
		return $this->hasMany(Commission::class);
	}

	public function paymentSystem()
	{
		return $this->belongsTo(PaymentSystem::class);
	}
}
