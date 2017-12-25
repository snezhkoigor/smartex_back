<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Models\PaymentSystem;
use App\Repositories\PaymentSystemRepository;
use App\Services\authDTO;
use App\Services\MerchantWebService;
use App\Transformers\PaymentSystemTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentSystemController extends Controller
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

    public function getPaymentSystems(Request $request)
    {
	    $filters = $this->getFilters($request);
	    $sorts = $this->getSortParameters($request);
	    $search_string = $this->getSearchString($request);
	    $fieldsets = $this->getFieldsets($request);
	    $includes = $this->getIncludes($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $payment_systems = PaymentSystemRepository::getPaymentSystems($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

	    $meta = [
		    'count' => PaymentSystemRepository::getPaymentSystemsCount($filters, $search_string),
		    'required' => PaymentSystemRepository::getRequireFields(),
		    'payment_systems' => PaymentSystemRepository::getAvailablePaymentSystems(),
		    'currencies' => PaymentSystemRepository::getAvailableCurrencies()
	    ];

	    return fractal($payment_systems, new PaymentSystemTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }

	public function getPaymentSystemById($payment_system_id)
	{
		$payment_system = PaymentSystem::find($payment_system_id);
		if ($payment_system === null) {
			throw new NotFoundHttpException('Payment system not found');
		}

		$filters = ['id' => $payment_system_id];
		$meta = [
			'required' => PaymentSystemRepository::getRequireFields($filters),
			'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems($filters)),
			'currencies' => array_values(PaymentSystemRepository::getAvailableCurrencies())
		];

		return fractal($payment_system, new PaymentSystemTransformer())
			->addMeta($meta)
			->respond();
	}

    public function add(Request $request)
    {
    	$this->validate($request, $this->rules(), $this->messages());

    	try
	    {
		    $payment_system = new PaymentSystem();
		    $payment_system->fill($request->all());
//		    $payment_system->logo = app()->make('news_service')->getProcessedNewsText($request->get('text'));
		    $payment_system->active = $request->get('active', true);
		    $payment_system->save([], true);
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Adding payment system failed', $e);
	    }

	    return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
    }

    public function updateById(Request $request, $payment_system_id)
    {
	    $this->validate($request, $this->rules(), $this->messages());

	    $payment_system = PaymentSystem::find($payment_system_id);
	    if ($payment_system === null) {
		    throw new NotFoundHttpException('Payment system not found');
	    }

	    try
	    {
		    $payment_system->fill($request->all());
		    $payment_system->active = $request->get('active');
//		    $payment_system->logo = app()->make('news_service')->getProcessedNewsText($request->get('text'));
		    $payment_system->save([], true);
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Updating payment system failed', $e);
	    }

	    return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
    }

    public function deleteById($payment_system_id)
    {
	    $payment_system = PaymentSystem::find($payment_system_id);
	    if ($payment_system === null) {
		    throw new NotFoundHttpException('Payment system not found');
	    }

	    try
	    {
		    $payment_system->is_deleted = true;
		    $payment_system_id->save([], true);
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Updating payment system failed', $e);
	    }

	    return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
    }
}
