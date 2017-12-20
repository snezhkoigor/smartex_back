<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    Model::unguard();
	    DB::table('roles')->delete();

	    $role = new Role();
	    $role->name = Role::ROLE_ADMIN;
	    $role->save();

	    $role = new Role();
	    $role->name = Role::ROLE_OPERATOR;
	    $role->save();

	    Model::reguard();
    }
}
