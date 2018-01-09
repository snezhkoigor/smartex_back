<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 *
 * @property integer $id
 * @property string $title
 * @property string $text
 * @property string $date
 * @property string $meta_key
 * @property string $meta_description
 * @property string $password
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 *
 * Class News
 * @package App\Models
 */
class News extends Model
{
	use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'meta_key',
        'meta_description',
        'date'
    ];

	protected $table = 'news';

	protected $guarded = [
        'active',
        'text'
    ];

    protected $dates = [
    	'created_at',
	    'updated_at'
    ];

	protected static $ignoreChangedAttributes = [
		'updated_at'
	];

	protected static $logAttributes = [
		'title',
		'meta_key',
		'meta_description',
		'date',
		'active',
		'text'
	];

	protected static $logOnlyDirty = true;

	public function getDescriptionForEvent($eventName)
	{
		return 'This news "' . $this->title . '" has been ' . $eventName;
	}

	public function getLogNameToUse($eventName = '')
	{
		return $eventName;
	}
}
