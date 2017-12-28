<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Models\Commission;
use App\Models\PaymentSystem;
use App\Models\Wallet;
use App\Repositories\CommissionRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\PaymentSystemRepository;
use App\Repositories\WalletRepository;
use App\Transformers\CommissionTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommissionController extends Controller
{
	public function rules(Request $request)
	{
		return [
			'wallet_id' => 'required|exists:payment_account,id|' . $this->checkUniqueCommission($request),
			'payment_system_id' => 'required|exists:payment_systems,id',
			'currency' => 'required|in:' . implode(',', array_keys(CurrencyRepository::getAvailableCurrencies())),
			'commission' => 'required|numeric',
			'active' => 'boolean',
			'is_deleted' => 'boolean',
		];
	}

	public function checkUniqueCommission(Request $request)
	{
		$id = $request->get('id');
		$wallet_id = $request->get('wallet_id');
		$payment_system_id = $request->get('payment_system_id');
		$currency = $request->get('currency');

		if ($wallet_id && $payment_system_id && $currency) {
			$query = Commission::query()
				->where('wallet_id', $wallet_id)
				->where('payment_system_id', $payment_system_id)
				->where('currency', $currency)
				->where('is_deleted', '=', 0);

			if ($id) {
				$query->where('id', '<>', $id);
			}

			if ($query->first()) {
				return 'unique:commissions,wallet_id';
			}
		}

		return '';
	}

	public function messages()
	{
		return [
			'wallet_id.required' => 'Enter wallet',
			'wallet_id.exists' => 'Wrong wallet',
			'wallet_id.unique' => 'This commission already exists',
			'payment_system_id.required' => 'Enter payment system',
			'payment_system_id.exists' => 'Wrong payment system',
			'currency.required' => 'Enter payment system currency',
			'currency.in' => 'Wrong payment system currency',
			'commission.required' => 'Enter transaction commission',
			'commission.numeric' => 'Wrong format transaction commission, use digits',
			'active.boolean' => 'Wrong format',
			'is_deleted.boolean' => 'Wrong format',
		];
	}

    public function getCommissions(Request $request)
    {
	    $filters = $this->getFilters($request);
	    $fieldsets = $this->getFieldsets($request);
	    $search_string = $this->getSearchString($request);
	    $includes = $this->getIncludes($request);
	    $relations = $this->getRelationsFromIncludes($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $commissions = CommissionRepository::getCommissions($filters, $relations, ['*'], $search_string, $limit, $offset);

	    $meta = [
		    'count' => CommissionRepository::getCommissionsCount($filters),
		    'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems()),
		    'currencies' => array_values(CurrencyRepository::getAvailableCurrencies())
	    ];

	    return fractal($commissions, new CommissionTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->toArray();
    }

	public function getFormMeta()
	{
		return fractal(null, new CommissionTransformer())
			->addMeta([
				'wallets' => array_values(WalletRepository::getAvailableWalletsForCommission()),
				'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems()),
				'currencies' => array_values(CurrencyRepository::getAvailableCurrencies())
			])
			->respond();
	}

	public function getCommissionById($commission_id)
	{
		$commission = Commission::find($commission_id);
		if ($commission === null) {
			throw new NotFoundHttpException('Commission not found');
		}

		$meta = [
			'wallets' => array_values(WalletRepository::getAvailableWalletsForCommission()),
			'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems()),
			'currencies' => array_values(CurrencyRepository::getAvailableCurrencies())
		];

		return fractal($commission, new CommissionTransformer())
			->addMeta($meta)
			->respond();
	}

	public function add(Request $request)
	{
		$this->validate($request, $this->rules($request), $this->messages());

		try
		{
			//TODO удалить это после переделывания внешнего сайта
			$wallet = Wallet::find($request->get('wallet_id'));
			$payment_system = PaymentSystem::find($request->get('payment_system_id'));

			$commission = new Commission();
			$commission->fill($request->all());
			$commission->active = $request->get('active', true);

			//TODO удалить это после переделывания внешнего сайта
			$commission->ps_in_type = $wallet->ps_type;
			$commission->ps_out_type = $payment_system->code;
			$commission->ps_out_currency = $request->get('currency');
			$commission->ps_in_currency = $wallet->currency;

			$commission->save([], true);
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Adding commission failed', $e);
		}

		return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
	}

	public function updateById(Request $request, $commission_id)
	{
		$commission = Commission::find($commission_id);
		if ($commission === null) {
			throw new NotFoundHttpException('Commission not found');
		}

		$this->validate($request, $this->rules($request), $this->messages());

		try
		{
			//TODO удалить это после переделывания внешнего сайта
			$wallet = Wallet::find($request->get('wallet_id'));
			$payment_system = PaymentSystem::find($request->get('payment_system_id'));

			$commission->fill($request->all());
			$commission->active = $request->get('active', true);

			//TODO удалить это после переделывания внешнего сайта
			$commission->ps_in_type = $wallet->ps_type;
			$commission->ps_out_type = $payment_system->code;
			$commission->ps_out_currency = $request->get('currency');
			$commission->ps_in_currency = $wallet->currency;

			$commission->save([], true);
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Updating commission failed', $e);
		}

		return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
	}

	public function deleteById($commission_id)
	{
		$commission = Commission::find($commission_id);
		if ($commission === null) {
			throw new NotFoundHttpException('Commission not found');
		}

		try
		{
			$commission->is_deleted = true;
			$commission->save([], true);
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Updating commission failed', $e);
		}

		return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
	}
}
