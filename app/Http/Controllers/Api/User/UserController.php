<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\SystemErrorException;
use App\Repositories\UserRepository;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
	public function rules()
	{
		return [
			'email' => 'required|exists:users|email'
		];
	}

	public function messages()
	{
		return [
			'email.required' => 'Enter email',
			'email.exists' => 'This email use someone else',
			'email.email' => 'Bad email format'
		];
	}

	public function getUsers(Request $request)
	{
		$filters = $this->getFilters($request);
		$sorts = $this->getSortParameters($request);
		$search_string = $this->getSearchString($request);
		$fieldsets = $this->getFieldsets($request);
		$includes = $this->getIncludes($request);
		$limit = $this->getPaginationLimit($request);
		$offset = $this->getPaginationOffset($request);

		$relations = $this->getRelationsFromIncludes($request);

		$users = UserRepository::getUsers($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

		$meta = [
			'count' => UserRepository::getUsersCount($filters, $search_string),
		];

		return fractal($users, new UserTransformer())
			->parseIncludes($includes)
			->parseFieldsets($fieldsets)
			->addMeta($meta)
			->respond();
	}

	public function changeActiveFieldById(Request $request, $user_id)
	{
		$this->validate($request, $this->rules(), $this->messages());

		$user = User::find($user_id);
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		try
		{
			$user->active = $request->get('active');
			$user->save([], true);

			$response = response()->json(['message' => $user->active ? 'Success activate user' : 'Success deactivate user'], Response::HTTP_CREATED);
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Updating user failed', $e);
		}

		return $response;
	}
}
