<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}