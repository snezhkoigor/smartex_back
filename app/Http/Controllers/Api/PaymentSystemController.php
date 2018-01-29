<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Models\PaymentSystem;
use App\Repositories\CurrencyRepository;
use App\Repositories\PaymentSystemRepository;
use App\Transformers\PaymentSystemTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\JsonResponse;

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
		    'currencies' => CurrencyRepository::getAvailableCurrencies()
	    ];

	    return fractal($payment_systems, new PaymentSystemTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }


	/**
	 * @param $payment_system_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function getPaymentSystemById($payment_system_id): JsonResponse
	{
		$payment_system = PaymentSystem::query()->find($payment_system_id);
		if ($payment_system === null) {
			throw new NotFoundHttpException('Payment system not found');
		}

		$filters = ['id' => $payment_system_id];
		$meta = [
			'required' => PaymentSystemRepository::getRequireFields($filters),
			'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems($filters)),
			'currencies' => array_values(CurrencyRepository::getAvailableCurrencies())
		];

		return fractal($payment_system, new PaymentSystemTransformer())
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

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }


	/**
	 * @param Request $request
	 * @param $payment_system_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
    public function updateById(Request $request, $payment_system_id): JsonResponse
    {
	    $this->validate($request, $this->rules(), $this->messages());

	    $payment_system = PaymentSystem::query()->find($payment_system_id);
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

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }


	/**
	 * @param $payment_system_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
    public function deleteById($payment_system_id): JsonResponse
    {
	    $payment_system = PaymentSystem::query()->find($payment_system_id);
	    if ($payment_system === null) {
		    throw new NotFoundHttpException('Payment system not found');
	    }

	    try
	    {
		    $payment_system->delete();
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Updating payment system failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
