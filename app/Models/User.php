<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Zizaco\Entrust\Traits\EntrustUserTrait;

/**
 *
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property boolean $active
 * @property boolean $is_verified
 * @property string $created_at
 * @property string $updated_at
 *
 * Class User
 * @package App\Models
 */
class User extends Authenticatable
{
	use CanResetPassword, EntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function roles()
    {
	    return $this->belongsToMany(Role::class);
    }

	public static function generate_password($number)
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
