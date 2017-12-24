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
	    $fieldsets = $this->getFieldsets($request);
	    $includes = $this->getIncludes($request);
	    $relations = $this->getRelationsFromIncludes($request);

	    $commissions = CommissionRepository::getCommissions($relations, ['*']);

	    $data = fractal($commissions, new CommissionTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->toArray();

	    foreach ($data['data'] as $item) {
	    	$result['data'][$item['wallet_id']][] = $item;
	    }

	    return $result;
    }
}
