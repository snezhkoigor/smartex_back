<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 *
 * @property integer $id
 * @property string $title
 * @property string $text
 * @property string $date
 * @property string $meta_key
 * @property string $meta_description
 * @property string $password
 * @property string $lang
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 *
 * @method static News|QueryBuilder|EloquentBuilder query()
 *
 * Class News
 * @package App\Models
 */
class News extends Model
{
	use LogsActivity;

	public static $languages = [
		'ru' => 'Русский',
		'en' => 'English'
	];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'meta_key',
        'meta_description',
        'date',
        'lang'
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
		'text',
		'lang'
	];

	protected static $logOnlyDirty = true;

	public function getDescriptionForEvent($eventName): string
	{
		return 'This news "' . $this->title . '" has been ' . $eventName;
	}

	public function getLogNameToUse($eventName = ''): string
	{
		return $eventName;
	}
}
