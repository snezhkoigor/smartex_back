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
        $this->call(RoleTableSeeder::class);
        $this->call(UserTableSeeder::class);
	    $this->call(UserRoleTableSeeder::class);
	    $this->call(PaymentSystemTableSeeder::class);
	    $this->call(NewsTableSeeder::class);
    }
}