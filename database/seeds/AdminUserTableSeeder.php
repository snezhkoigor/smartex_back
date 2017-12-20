<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    Model::unguard();
	    DB::table('users')->delete();

	    $users = array(
	    	[
	           'first_name' => 'Сергей',
	           'last_name' => 'Ермолов',
	           'email' => 'dj_ermoloff@mail.ru',
	           'password' => Hash::make('1secret2')
            ],
		    [
			    'first_name' => 'ADMIN',
			    'last_name' => 'ACCOUNT SMARTEX',
			    'email' => 'illya.yefyemnko23@gmail.com',
			    'password' => Hash::make('1secret2')
		    ],
		    [
			    'first_name' => 'Игорь',
			    'last_name' => 'Снежко',
			    'email' => 'i.s.sergeevich@yandex.ru',
			    'password' => Hash::make('1secret2')
		    ]
	    );

	    foreach ($users as $user) {
		    User::create($user);
	    }

	    Model::reguard();
    }
}
