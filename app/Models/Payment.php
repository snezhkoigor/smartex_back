<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
 * Class Payment
 * @package App\Models
 */
class Payment extends Model
{
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

	protected $guarded = [];

	protected $dates = [
		'date',
		'date_confirm'
	];
}