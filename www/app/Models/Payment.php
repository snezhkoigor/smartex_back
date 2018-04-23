<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;


/**
 * @property integer $id
 * @property integer $id_user
 * @property integer $id_account
 * @property string $date
 * @property integer $type
 * @property string $payment_system
 * @property string $payer
 * @property string $payee
 * @property integer $id_user_details
 * @property double $amount
 * @property string $currency
 * @property double $fee
 * @property string $batch
 * @property string $date_confirm
 * @property string $comment
 * @property integer $confirm
 * @property integer $btc_check
 *
 * @property User $user
 * @property PaymentSystem $paymentSystem
 *
 * @method static Payment|QueryBuilder|EloquentBuilder query()
 *
 * Class Payment
 * @package App\Models
 */
class Payment extends Model
{
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id_user',
		'id_account',
		'date',
		'type',
		'payment_system',
		'payer',
		'payee',
		'id_user_details',
		'amount',
		'currency',
		'fee',
		'batch',
		'date_confirm',
		'comment',
		'confirm',
		'btc_check'
	];


	protected $dates = [
		'date',
		'date_confirm'
	];


	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function user(): HasOne
	{
		return $this->hasOne(User::class, 'id', 'id_user');
	}


	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function paymentSystem(): HasOne
	{
		return $this->hasOne(PaymentSystem::class, 'code', 'payment_system');
	}
}