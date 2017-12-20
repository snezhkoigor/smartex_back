<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

class UserRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    Model::unguard();
	    DB::table('role_user')->delete();

	    $users = User::all();
	    $role = Role::where('name', '=', Role::ROLE_ADMIN)->first();

	    foreach ($users as $user) {
		    $user->roles()->attach($role->id);
	    }

	    Model::reguard();
    }
}
