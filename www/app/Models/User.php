<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Support\Facades\Redis;
use Spatie\Activitylog\Models\Activity;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
 * @property string $comment
 * @property string $address
 * @property integer $discount
 * @property double $total_exchange
 * @property string $document_number
 * @property string $verification_image
 * @property boolean $verification_ok
 * @property string $verification_kyc
 * @property boolean $verification_kyc_ok
 * @property string $phone
 * @property boolean $verification_phone_ok
 *
 * @method static User|QueryBuilder|EloquentBuilder query()
 *
 * @property Role[] $roles
 * @property Activity[] $activities
 * @property LoginLog[] $loginLogs
 *
 * Class User
 * @package App\Models
 */
class User extends Authenticatable
{
	use HasApiTokens, CanResetPassword, EntrustUserTrait, LogsActivity;

	const CREATED_AT = 'date';
	
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
	    'comment',
	    'address',
	    'verification_kyc',
	    'verification_kyc_ok',
	    'phone',
	    'verification_phone_ok'
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
//    	'online',
//	    'auth_err_date',
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
		'address',
		'verification_kyc',
	    'verification_kyc_ok'
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
	
	public function saveSmsCode($code, $phone = null)
	{
		return Redis::set($this->smsCodeKey($phone), $code, 'EX', 3600);
	}

	public function checkSmsCode($code): bool
	{
		$data = Redis::get($this->smsCodeKey());
		if ($data)
		{
		    return $data === $code;
		}

		return false;
	}
	
	private function smsCodeKey($phone = null): string
	{
		return 'user:'.$this->id.':sms:phone:'.($phone ?: $this->phone).':verification';
	}

	public function loginLogs()
	{
		return $this->hasMany(LoginLog::class, 'user_id', 'id');
	}

	public static function generatePassword($number): string
	{
		$result = '';
		$arr = [
			'a','b','c','d','e','f',
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
			'{','}','`','~'
		];

		for($i = 0; $i < $number; $i++) {
			$index = random_int(0, \count($arr) - 1);
			$result .= $arr[$index];
		}

		return $result;
	}
}
