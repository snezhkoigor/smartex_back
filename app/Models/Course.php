<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
 * @method static Course|QueryBuilder|EloquentBuilder query()
 *
 * Class Course
 * @package App\Models
 */
class Course extends Model
{
	use LogsActivity;

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

	protected static $ignoreChangedAttributes = [
		'updated_at'
	];

	protected static $logAttributes = [
		'date',
		'in_currency',
		'out_currency',
		'course'
	];

	protected static $logOnlyDirty = true;

	public function getDescriptionForEvent($eventName)
	{
		return 'This course "' . $this->date . ', ' . $this->in_currency . '/' . $this->out_currency . ',' . $this->course . '" has been ' . $eventName;
	}

	public function getLogNameToUse($eventName = '')
	{
		return $eventName;
	}
}
