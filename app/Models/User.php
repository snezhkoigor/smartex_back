<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Spatie\Activitylog\Models\Activity;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 *
 * @property integer $id
 * @property integer $refer
 * @property string $avatar
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
 * @property Role[] $roles
 * @property Activity[] $activities
 *
 * Class User
 * @package App\Models
 */
class User extends Authenticatable
{
	use CanResetPassword, EntrustUserTrait, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    	'refer',
        'name',
        'family',
        'email',
	    'lang',
	    'country',
	    'date',
	    'auth_err',
	    'auth_err_date',
	    'auth_err_ip',
	    'ip',
	    'online',
	    'role',
	    'discount',
	    'total_exchange',
	    'document_number',
	    'verification_image',
	    'verification_ok',
    ];

	protected $guarded = [
		'activation',
		'password',
		'avatar',
	];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $dates = [
    	'online',
	    'auth_err_date',
    ];

	protected static $ignoreChangedAttributes = [
		'password'
	];

	protected static $logAttributes = [
		'refer',
		'name',
		'family',
		'email',
		'lang',
		'country',
		'date',
		'auth_err',
		'auth_err_date',
		'auth_err_ip',
		'ip',
		'online',
		'role',
		'discount',
		'total_exchange',
		'document_number',
		'verification_image',
		'verification_ok',
		'activation',
		'avatar',
	];

	protected static $logOnlyDirty = true;

	public function getDescriptionForEvent($eventName)
	{
		return 'This user "' . $this->email . '" has been ' . $eventName;
	}

	public function getLogNameToUse($eventName = '')
	{
		return $eventName;
	}

    public function roles()
    {
	    return $this->belongsToMany(Role::class);
    }

	public function activities()
	{
		return $this->hasMany(Activity::class, 'causer_id', 'id');
	}

	public static function generatePassword($number)
	{
		$result = '';
		$arr = array('a','b','c','d','e','f',
		             'g','h','i','j','k','l',
		             'm','n','o','p','r','s',
		             't','u','v','x','y','z',
		             'A','B','C','D','E','F',
		             'G','H','I','J','K','L',
		             'M','N','O','P','R','S',
		             'T','U','V','X','Y','Z',
		             '1','2','3','4','5','6',
		             '7','8','9','0','.',',',
		             '(',')','[',']','!','?',
		             '&','^','%','@','*','$',
		             '<','>','/','|','+','-',
		             '{','}','`','~');

		for($i = 0; $i < $number; $i++) {
			$index = mt_rand(0, count($arr) - 1);
			$result .= $arr[$index];
		}

		return $result;
	}
}
