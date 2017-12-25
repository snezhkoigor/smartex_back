<?php

namespace App\Http\Controllers\Api;

use App\Repositories\CommissionRepository;
use App\Repositories\PaymentSystemRepository;
use App\Transformers\CommissionTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

	    $commissions = CommissionRepository::getCommissions($filters, $relations, ['*'], $search_string);

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
}
