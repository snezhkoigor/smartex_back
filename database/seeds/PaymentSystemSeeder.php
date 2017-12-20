<?php

use Illuminate\Database\Seeder;
use \App\Models\PaymentSystem;

class PaymentSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Bank';
	    $payment_system->code = 'bank';
	    $payment_system->is_account_multi_line = true;
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Perfect Money';
	    $payment_system->code = 'pm';
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Payeer';
	    $payment_system->code = 'payeer';
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'ADV';
	    $payment_system->code = 'adv';
	    $payment_system->fields = 'secret,user,password,id_payee,adv_sci';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'BTC';
	    $payment_system->code = 'btc';
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'ETH';
	    $payment_system->code = 'eth';
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();
    }
}
