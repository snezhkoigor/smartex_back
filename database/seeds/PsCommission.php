<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PsCommission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $commissions = DB::connection('old')
	        ->table('ps_comission')
	        ->get();


        foreach ($commissions as $commission) {

        }
        var_dump($commissions);
    }
}
