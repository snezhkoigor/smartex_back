<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\SystemErrorException;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Transformers\PaymentTransformer;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
	private $user_service;

	public function __construct(UserService $user_service)
	{
		$this->user_service = $user_service;
	}

	public function rules(Request $request, $user)
	{
		return [
			'email' => 'required|email|' . $this->emailRulesByChanging($request, $user),
			'current_password' => 'required_with:new_password|' . $this->checkCurrentPassword($request, $user)
		];
	}

	public function checkCurrentPassword(Request $request, User $user)
	{
		if ($request->get('new_password') && !Hash::check($request->get('current_password'), $user->password)) {
			return 'same:password';
		}
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
			'email.email' => 'Bad email format',
			'current_password.required_with' => 'Enter your current password for changing in new',
			'current_password.same' => 'Wrong password value'
		];
	}


	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function profile(Request $request): JsonResponse
	{
		$fieldsets = $this->getFieldsets($request);
		$includes = $this->getIncludes($request);
		$user = \Auth::user();

		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		return fractal($user, new UserTransformer())
			->parseIncludes($includes)
			->parseFieldsets($fieldsets)
			->respond();
	}


	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function updateProfile(Request $request): JsonResponse
	{
		$user = \Auth::user();
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		$this->validate($request, $this->rules($request, $user), $this->messages());

		try {
			$oldUser = clone $user;

			$user->fill($request->all());

			if ($request->get('new_password')) {
				$user->password = $request->get('new_password') ? Hash::make($request->get('new_password')) : $user->password;
			}
			$user->verification_image = $this->user_service->getProcessedUserDocument($user, $request->get('verification_image_64_base'));

			if ($oldUser->verification_image !== $user->verification_image) {
				$user->document_number = (int)$user->document_number + 1;
			}
			
			$user->save();
		} catch (\Exception $e) {
			throw new SystemErrorException('Update user profile failed', $e);
		}

		return fractal($user, new UserTransformer())
			->parseIncludes('roles')
			->respond();
	}
	
	
	/**
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function referrers(): JsonResponse
	{
		$user = \Auth::user();
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		$filters = $this->getFilters($request);
	    $sorts = $this->getSortParameters($request);
	    $search_string = $this->getSearchString($request);
	    $fieldsets = $this->getFieldsets($request);
	    $includes = $this->getIncludes($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $filters['referer'] = $user->id;
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
}
