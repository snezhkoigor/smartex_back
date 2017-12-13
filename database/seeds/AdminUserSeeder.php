<?php

use Illuminate\Database\Seeder;
use \App\Models\User;
use \Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
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

	    $users = array([
	    	'first_name' => 'Admin',
		    'last_name' => 'Admin',
		    'email' => 'admin@admin.com',
		    'password' => Hash::make('1secret2')
        ]);

	    foreach ($users as $user) {
		    User::create($user);
	    }

	    Model::reguard();
    }
}
