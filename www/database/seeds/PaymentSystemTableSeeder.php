<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentSystem;

class PaymentSystemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    Model::unguard();
	    DB::table('payment_systems')->delete();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Bank';
	    $payment_system->code = 'bank';
	    $payment_system->logo = null;
	    $payment_system->is_account_multi_line = true;
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Perfect Money';
	    $payment_system->code = 'pm';
	    $payment_system->logo = 'perfect-money.png';
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Payeer';
	    $payment_system->code = 'payeer';
	    $payment_system->logo = 'payeer.png';
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'ADV';
	    $payment_system->code = 'adv';
	    $payment_system->logo = 'adv-cash.png';
	    $payment_system->fields = 'secret,user,password,id_payee,adv_sci';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'BTC';
	    $payment_system->code = 'btc';
	    $payment_system->logo = 'bitcoin.png';
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'ETH';
	    $payment_system->code = 'eth';
	    $payment_system->logo = 'etherium.png';
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Dash Coin';
	    $payment_system->code = 'eth';
	    $payment_system->logo = 'dash.png';
	    $payment_system->active = 0;
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'ZCash';
	    $payment_system->code = 'zcash';
	    $payment_system->logo = 'zcash.png';
	    $payment_system->active = 0;
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Ripple';
	    $payment_system->code = 'ripple';
	    $payment_system->logo = 'ripple.png';
	    $payment_system->active = 0;
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Monero';
	    $payment_system->code = 'monero';
	    $payment_system->logo = 'monero.png';
	    $payment_system->active = 0;
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    $payment_system = new PaymentSystem();
	    $payment_system->name = 'Lite Coin';
	    $payment_system->code = 'lcoin';
	    $payment_system->logo = 'litecoin.png';
	    $payment_system->active = 0;
	    $payment_system->fields = 'secret,user,password';
	    $payment_system->save();

	    Model::reguard();
    }
}
