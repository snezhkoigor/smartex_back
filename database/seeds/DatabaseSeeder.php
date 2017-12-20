<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $this->call(AdminUserSeeder::class);
//	    $this->call(UserRoleSeeder::class);
//	    $this->call(UserAssignRole::class);
	    $this->call(PaymentSystemSeeder::class);
    }
}
