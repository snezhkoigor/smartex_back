<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ClientRepository;
use Illuminate\Http\Response;

/**
 * Class WidgetController
 * @package App\Http\Controllers\Api
 */
class WidgetController extends Controller
{
	/**
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function totalClientRegistrations()
	{
		return response()->json(['data' => ClientRepository::widgetsTotalRegistrations()], Response::HTTP_OK);
	}


	/**
	 * @param null $period_type
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function totalClientRegistrationsAndActivations($period_type = null)
	{
		return response()->json(['data' => ClientRepository::widgetsRegistrationsAndActivations($period_type)], Response::HTTP_OK);
	}
}