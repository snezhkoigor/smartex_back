<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property integer $id
 * @property string $date
 * @property string $in_currency
 * @property string $out_currency
 * @property double $course
 * @property string $created_at
 * @property string $updated_at
 *
 * Class Course
 * @package App\Models
 */
class Course extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'date',
		'in_currency',
		'out_currency',
		'course'
	];

	protected $guarded = [];

	protected $dates = [

		'created_at',
		'updated_at'
	];
}
