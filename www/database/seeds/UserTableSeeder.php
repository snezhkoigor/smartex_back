<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->delete();
        $user = new \App\Models\User();
        $user->email = 'admin@cryptocoinofcina.com';
        $user->activation = 1;
        $user->password = \Illuminate\Support\Facades\Hash::make('123admin123');
        
        $user->save();
    }
}
