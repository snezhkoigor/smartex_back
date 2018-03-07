<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\SystemErrorException;
use App\Models\Role;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Transformers\UserTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
	public function rules(Request $request, $user = null)
	{
		return [
			'email' => 'required|email' . ($user ? $this->emailRulesByChanging($request, $user) : '')
		];
	}


	public function emailRulesByChanging(Request $request, User $user)
	{
		$email = $request->get('email');
		if ($user && $email !== $user->email) {
			return User::query()->where('email', '=', $email)->exists() ? '|unique:users' : '';
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


    public function getFormMeta()
	{
		return fractal(null, new UserTransformer())
			->addMeta([
				'roles' => array_values(RoleRepository::getAvailableRoles())
			])
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
	    $this->validate($request, $this->rules($request), $this->messages());

	    try
	    {
	    	$user = new User();
		    $user->fill($request->all());
		    $user->activation = (bool)$request->get('activation');
		    $user->verification_ok = true;
		    $user->date = Carbon::now()->toDateTimeString();

		    if ($request->get('password'))
		    {
		    	$user->password = Hash::make($request->get('password'));
		    }

		    $user->save();

		    if ($request->get('role_id'))
		    {
		        $user->roles()->attach($request->get('role_id'));
		    }
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Adding user failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }


    /**
	 * @param $news_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function getUserById($news_id): JsonResponse
	{
		$user = User::query()->find($news_id);
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		return fractal($user, new UserTransformer())
			->addMeta([
				'roles' => array_values(RoleRepository::getAvailableRoles())
			])
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

		    if ($request->get('activation'))
		    {
		        $user->activation = (bool)$request->get('activation');
		    }
		    if ($request->get('password'))
		    {
		    	$user->password = Hash::make($request->get('password'));
		    }
		    if ($request->get('verification_ok'))
		    {
		    	$user->verification_ok = $request->get('verification_ok');
		    }

		    $user->save();

		    if ($request->get('role_id'))
		    {
		    	DB::table('role_user')
				    ->where('user_id', $user->id)
				    ->delete();

		    	$role = Role::query()->where('id', $request->get('role_id'))->first();
		    	
		    	if ($role && !$user->hasRole($role->id))
			    {
			        $user->roles()->attach($request->get('role_id'));
			    }
		    }
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
