<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;


/**
 *
 * @property integer $id
 * @property string $date
 * @property integer $id_user
 * @property string $in_payment
 * @property integer $in_id_pay
 * @property string $in_currency
 * @property double $in_amount
 * @property double $in_fee
 * @property string $in_payee
 * @property string $comment
 * @property string $out_payment
 * @property integer $out_id_pay
 * @property string $out_currency
 * @property double $out_amount
 * @property string $out_payer
 * @property string $out_payee
 * @property double $out_fee
 * @property string $out_batch
 * @property string $out_date
 *
 * @method static Exchange|QueryBuilder|EloquentBuilder query()
 *
 * Class Exchange
 * @package App\Models
 */
class Exchange extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'date',
		'id_user',
		'in_payment',
		'in_id_pay',
		'in_currency',
		'in_amount',
		'in_fee',
		'in_payee',
		'comment',
		'out_payment',
		'out_id_pay',
		'out_currency',
		'out_amount',
		'out_payer',
		'out_payee',
		'out_fee',
		'out_batch',
		'out_date',
	];

	protected $guarded = [];

	protected $dates = [
		'date',
		'out_date'
	];
}