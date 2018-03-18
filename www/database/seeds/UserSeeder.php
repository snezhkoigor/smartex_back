<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
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
	        $user = new \App\Models\User();
	        $user->email = 'admin@cryptocoinofcina.com';
	        $user->password = \Illuminate\Support\Facades\Hash::make('123admin123');
	        
	        $user->save();
        Model::reguard();
    }
}
