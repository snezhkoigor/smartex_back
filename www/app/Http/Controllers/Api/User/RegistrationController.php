<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\SystemErrorException;
use App\Mail\RegistrationSuccessMail;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
	public function rules(): array
	{
		return [
			'email' => 'required|email|unique:users,email',
			'name' => 'max:100',
			'password' => 'required'
		];
	}

	public function messages(): array
	{
		return [
			'email.required' => 'Enter email',
			'email.unique' => 'This email somebody use already',
			'email.email' => 'Bad email format',
			'password.required' => 'Enter password',
			'name.max' => 'Your name is too long'
		];
	}


	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function registration(Request $request): JsonResponse
	{
		$this->validate($request, $this->rules(), $this->messages());

		try {
			$user = new User();
			$user->email = $request->get('email');
			$user->password = Hash::make($request->get('password'));
			$user->verification_ok = false;
			$user->activation = false;
			if ($request->get('name')) {
				$user->name = $request->get('name');
			}
			if ($request->get('refer')) {
				$user->refer = $request->get('refer');
			}
			$user->save();

			if ($user) {
				Mail::to($user->email)->send(new RegistrationSuccessMail($user, $request->get('password')));
			}
		} catch (\Exception $e) {
			throw new SystemErrorException('Registration user failed', $e);
		}

		return response()->json(null, Response::HTTP_OK);
	}
}
