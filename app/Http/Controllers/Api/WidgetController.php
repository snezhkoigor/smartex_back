<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentSystem;
use App\Repositories\ExchangeRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentSystemRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

/**
 * Class WidgetController
 * @package App\Http\Controllers\Api
 *
 *
 */
class WidgetController extends Controller
{
	/**
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function totalClientRegistrations(): JsonResponse
	{
		return response()->json(['data' => UserRepository::widgetsTotalRegistrations()], Response::HTTP_OK);
	}


	/**
	 * @param null $period_type
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function totalClientRegistrationsAndActivations($period_type = null): JsonResponse
	{
		return response()->json(['data' => UserRepository::widgetsRegistrationsAndActivations($period_type)], Response::HTTP_OK);
	}
	

	/**
	 * @return JsonResponse
	 */
	public function totalFinishedExchages(): JsonResponse
	{
		return response()->json(['data' => ExchangeRepository::getFinishedExchages()], Response::HTTP_OK);
	}


	/**
	 * @return JsonResponse
	 */
	public function totalFinishedInPayments(): JsonResponse
	{
		return response()->json(['data' => ExchangeRepository::getStartedExchages()], Response::HTTP_OK);
	}


	/**
	 * @return JsonResponse
	 */
	public function totalNewExchages(): JsonResponse
	{
		return response()->json(['data' => ExchangeRepository::getNewExchages()], Response::HTTP_OK);
	}


	/**
	 * @return JsonResponse
	 */
	public function currenciesInPaymentsByLastMonth(): JsonResponse
	{
		return response()->json(['data' => PaymentRepository::getCurrenciesInPayments('2017-01-01', '2017-09-01')], Response::HTTP_OK);
	}


	/**
	 * @return JsonResponse
	 */
	public function currenciesOutPaymentsByLastMonth(): JsonResponse
	{
		return response()->json(['data' => PaymentRepository::getCurrenciesOutPayments('2017-01-01', '2017-09-01')], Response::HTTP_OK);
	}


	/**
	 * @return JsonResponse
	 */
	public function paymentSystemsPaymentsByLastMonth(): JsonResponse
	{
		return response()->json(['data' => PaymentSystemRepository::getPaymentSystemsPayments('2017-01-01', '2017-09-01')], Response::HTTP_OK);
	}


	/**
	 * @return JsonResponse
	 */
	public function getExchangesDynamicByLastMonth(): JsonResponse
	{
		return response()->json(['data' => ExchangeRepository::getExchagesDynamic('2017-01-01', '2017-12-31')], Response::HTTP_OK);
	}
}