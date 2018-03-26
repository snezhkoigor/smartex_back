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
use Illuminate\Http\JsonResponse;

class CommissionController extends Controller
{
	public function rules(Request $request)
	{
		return [
			'wallet_id' => 'required|exists:payment_account,id|' . $this->checkUniqueCommission($request),
			'payment_system_id' => 'required|exists:payment_systems,id',
			'currency' => 'required|in:' . implode(',', array_keys(CurrencyRepository::getAvailableCurrencies())),
			'commission' => 'required|numeric',
			'active' => 'boolean'
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
				->where('currency', $currency);

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
			'active.boolean' => 'Wrong format'
		];
	}


	public function view(Request $request)
	{
		$filters = $this->getFilters($request);
	    $search_string = $this->getSearchString($request);
	    $relations = $this->getRelationsFromIncludes($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $commissions = CommissionRepository::getCommissions($filters, $relations, ['*'], $search_string, $limit, $offset);

	    return fractal($commissions, new CommissionTransformer())
		    ->parseIncludes(['paymentSystem', 'wallet.paymentSystem'])
		    ->respond();
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
		    ->respond();
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


	/**
	 * @param $commission_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function getCommissionById($commission_id): JsonResponse
	{
		$commission = Commission::query()->find($commission_id);
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


	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function add(Request $request): JsonResponse
	{
		$this->validate($request, $this->rules($request), $this->messages());

		try
		{
			//TODO удалить это после переделывания внешнего сайта
			$wallet = Wallet::query()->find($request->get('wallet_id'));
			$payment_system = PaymentSystem::query()->find($request->get('payment_system_id'));

			$commission = new Commission();
			$commission->fill($request->all());
			$commission->active = $request->get('active', true);

			//TODO удалить это после переделывания внешнего сайта
			$commission->ps_in_type = $wallet->ps_type;
			$commission->ps_out_type = $payment_system->code;
			$commission->ps_out_currency = $request->get('currency');
			$commission->ps_in_currency = $wallet->currency;

			$commission->save();
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Adding commission failed', $e);
		}

		return response()->json(null, Response::HTTP_NO_CONTENT);
	}


	/**
	 * @param Request $request
	 * @param $commission_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function updateById(Request $request, $commission_id): JsonResponse
	{
		$commission = Commission::query()->find($commission_id);
		if ($commission === null) {
			throw new NotFoundHttpException('Commission not found');
		}

		$this->validate($request, $this->rules($request), $this->messages());

		try
		{
			//TODO удалить это после переделывания внешнего сайта
			$wallet = Wallet::query()->find($request->get('wallet_id'));
			$payment_system = PaymentSystem::query()->find($request->get('payment_system_id'));

			$commission->fill($request->all());
			$commission->active = $request->get('active', true);

			//TODO удалить это после переделывания внешнего сайта
			$commission->ps_in_type = $wallet->ps_type;
			$commission->ps_out_type = $payment_system->code;
			$commission->ps_out_currency = $request->get('currency');
			$commission->ps_in_currency = $wallet->currency;

			$commission->save();
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Updating commission failed', $e);
		}

		return response()->json(null, Response::HTTP_NO_CONTENT);
	}


	/**
	 * @param $commission_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function deleteById($commission_id): JsonResponse
	{
		$commission = Commission::query()->find($commission_id);
		if ($commission === null) {
			throw new NotFoundHttpException('Commission not found');
		}

		try
		{
			$commission->delete();
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Updating commission failed', $e);
		}

		return response()->json(null, Response::HTTP_NO_CONTENT);
	}
}
