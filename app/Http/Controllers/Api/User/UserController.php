<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\SystemErrorException;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
	public function rules(Request $request, $user)
	{
		return [
			'email' => 'required|email|' . $this->emailRulesByChanging($request, $user)
		];
	}


	public function emailRulesByChanging(Request $request, User $user)
	{
		$email = $request->get('email');
		if ($user && $email !== $user->email) {
			return User::query()->where('email', '=', $email)->exists() ? 'unique:users' : '';
		}
	}


	public function messages()
	{
		return [
			'email.required' => 'Enter email',
			'email.exists' => 'This email use someone else',
			'email.email' => 'Bad email format'
		];
	}


	/**
	 * @param Request $request
	 * @return mixed
	 */
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
	
	
	/**
	 * @param Request $request
	 * @param $user_id
	 * @return JsonResponse
	 *
	 * @throws \Exception
	 */
    public function updateById(Request $request, $user_id): JsonResponse
    {
    	$user = User::query()->find($user_id);
	    if ($user === null) {
		    throw new NotFoundHttpException('User not found');
	    }

	    $this->validate($request, $this->rules($request, $user), $this->messages());

	    try
	    {
		    $user->fill($request->all());
		    $user->verification_ok = $request->get('verification_ok');
		    $user->save();
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Updating user failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }


	/**
	 * @param $user_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
    public function deleteById($user_id): JsonResponse
    {
	    $user = User::query()->find($user_id);
	    if ($user === null) {
		    throw new NotFoundHttpException('User not found');
	    }

	    try
	    {
		    $user->delete();
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Deleting user failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
