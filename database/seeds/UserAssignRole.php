<?php

use Illuminate\Database\Seeder;
use \App\Models\User;
use \App\Models\Role;

class UserAssignRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    $users = User::all();

	    $role = Role::where('name', '=', Role::ROLE_ADMIN)->first();

	    foreach ($users as $user) {
		    $user->roles()->attach($role->id);
	    }
    }
}
