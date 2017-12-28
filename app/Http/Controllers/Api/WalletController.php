<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Models\PaymentSystem;
use App\Models\Wallet;
use App\Repositories\CurrencyRepository;
use App\Repositories\PaymentSystemRepository;
use App\Repositories\WalletRepository;
use App\Services\Advcash\AdvcashService;
use App\Services\BtcService;
use App\Services\PayeerService;
use App\Services\PerfectMoneyService;
use App\Transformers\WalletTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Mockery\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WalletController extends Controller
{
	public function messages()
	{
		return [
			'payment_system_id.required' => 'Enter wallet payment system',
			'currency.required' => 'Enter wallet currency',
			'currency.in' => 'We can not find your currency in available list',
			'balance.required' => 'Enter wallet balance',
			'balance.numeric' => 'Wallet balance must be numeric',
			'user.required' => 'Enter wallet user login in payment system website',
			'password.required' => 'Enter payment account password for login in payment system website',
			'secret.required' => 'Enter wallet secret for transactions',
			'adv_sci.required' => 'Enter ADV SCI for transactions',
			'id_payee.required' => 'Enter ID PAYEE for transactions',
			'account.required' => 'Enter wallet number',
		];
	}

    public function getWallets(Request $request)
    {
	    $filters = $this->getFilters($request);
	    $search_string = $this->getSearchString($request);
	    $fieldsets = $this->getFieldsets($request);
	    $includes = $this->getIncludes($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $wallets = WalletRepository::getWallets($filters, $relations, ['*'], $search_string);

	    $meta = [
		    'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems()),
		    'currencies' => array_values(CurrencyRepository::getAvailableCurrencies())
	    ];

	    return fractal($wallets, new WalletTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }

	public function getFormMeta()
	{
		return fractal(null, new WalletTransformer())
			->addMeta([
				'required' => PaymentSystemRepository::getRequireFields(),
				'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems()),
				'currencies' => array_values(CurrencyRepository::getAvailableCurrencies())
			])
			->respond();
	}

    public function getWalletById($wallet_id)
    {
	    $wallet = Wallet::find($wallet_id);
	    if ($wallet === null) {
		    throw new NotFoundHttpException('Payment system wallet not found');
	    }

	    $filters = ['id' => $wallet->payment_system_id];
	    $meta = [
		    'required' => PaymentSystemRepository::getRequireFields($filters),
		    'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems($filters)),
		    'currencies' => array_values(CurrencyRepository::getAvailableCurrencies())
	    ];

	    return fractal($wallet, new WalletTransformer())
		    ->addMeta($meta)
		    ->respond();
    }

    public function add(Request $request)
    {
	    $this->validate($request, PaymentSystem::walletRulesById($request->get('payment_system_id')), $this->messages());

    	try
	    {
	    	$payment_system = PaymentSystem::find($request->get('payment_system_id'));
		    $wallet = new Wallet();
		    $wallet->fill($request->all());
		    $wallet->payment_system_id = $request->get('payment_system_id');
		    // TODO убрать потом это, потому что можно связывать по идентификатору!
		    $wallet->ps_type = $payment_system->code;
		    $wallet->active = $request->get('active', true);

		    $wallet->save([], true);
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Adding wallet failed', $e);
	    }

	    return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
    }

    public function updateById(Request $request, $wallet_id)
    {
	    $wallet = Wallet::find($wallet_id);
	    if ($wallet === null) {
		    throw new NotFoundHttpException('Wallet not found');
	    }

	    $this->validate($request, PaymentSystem::walletRulesById($request->get('payment_system_id')), $this->messages());

	    try
	    {
		    $payment_system = PaymentSystem::find($request->get('payment_system_id'));
		    $wallet->fill($request->all());
		    $wallet->payment_system_id = $request->get('payment_system_id');
		    // TODO убрать потом это, потому что можно связывать по идентификатору!
		    $wallet->ps_type = $payment_system->code;
		    $wallet->active = $request->get('active', true);
		    $wallet->save([], true);
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Updating wallet failed', $e);
	    }

	    return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
    }

	public function checkAccess(Request $request)
	{
		$payment_system = PaymentSystem::find($request->get('payment_system_id'));
		if ($payment_system === null) {
			throw new NotFoundHttpException('Payment system not found');
		}

		try {
			switch ($payment_system->code) {
				case 'bank':
				case 'eth':
					return response()->json(['message' => 'This payment system not support access check'], Response::HTTP_UNPROCESSABLE_ENTITY);

				case 'pm':
					return response()->json(['balance' => PerfectMoneyService::getWalletBalance($request->get('user', ''), $request->get('password', ''), $request->get('account', ''))], Response::HTTP_OK);

				case 'payeer':
					return response()->json(['balance' => PayeerService::getWalletBalance($request->get('user', ''), $request->get('password', ''), $request->get('account', ''), $request->get('currency', ''))], Response::HTTP_OK);

				case 'adv':
					return response()->json(['balance' => AdvcashService::getWalletBalance($request->get('id_payeer', ''), $request->get('user', ''), $request->get('password', ''), $request->get('account', ''))], Response::HTTP_OK);

				case 'btc':
					return response()->json(['balance' => BtcService::getWalletBalance($request->get('user', ''))], Response::HTTP_OK);
			}
		} catch (\Exception $e) {
			return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
		}
	}

	public function deleteById($wallet_id)
	{
		$wallet = Wallet::find($wallet_id);
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}

		try
		{
			$wallet->is_deleted = true;
			$wallet->save([], true);
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Updating payment system wallet failed', $e);
		}

		return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
	}
}
