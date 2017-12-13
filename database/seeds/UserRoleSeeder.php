<?php

use Illuminate\Database\Seeder;
use \App\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    $role = new Role();
	    $role->name = Role::ROLE_ADMIN;
	    $role->save();

	    $role = new Role();
	    $role->name = Role::ROLE_OPERATOR;
	    $role->save();
    }
}
