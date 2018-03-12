<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\SystemErrorException;
use App\Mail\EmailActivationSuccessMail;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActivationController extends Controller
{
	/**
	 * @param $hash
	 * @return JsonResponse
	 *
	 * @throws \Exception
	 */
	public function activation($hash): JsonResponse
	{
		$user = User::query()->where(DB::raw('MD5(email)'), '=', $hash)->first();
		if (!$user) {
			throw new NotFoundHttpException('No user in DB');
		}

		try {
			$user->activation = true;
			$user->save();

			Mail::to($user->email)->send(new EmailActivationSuccessMail($user));
		} catch (\Exception $e) {
			throw new SystemErrorException('Activation user failed', $e);
		}

		return response()->json(null, Response::HTTP_OK);
	}
}
