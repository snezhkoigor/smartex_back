<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Models\Commission;
use App\Repositories\CommissionRepository;
use App\Repositories\PaymentSystemRepository;
use App\Repositories\WalletRepository;
use App\Transformers\CommissionTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommissionController extends Controller
{
	public function rules()
	{
		return [
			'name' => 'required|max:100',
		];
	}

	public function messages()
	{
		return [
			'name.required' => 'Enter payment system name',
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
		    'currencies' => array_values(PaymentSystemRepository::getAvailableCurrencies())
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
				'wallets' => array_values(WalletRepository::getAvailableWallets()),
				'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems()),
				'currencies' => array_values(PaymentSystemRepository::getAvailableCurrencies())
			])
			->respond();
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
