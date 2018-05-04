<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Helpers\CurrencyHelper;
use App\Mail\ExchangeCreatedMail;
use App\Models\Commission;
use App\Models\Exchange;
use App\Models\PaymentSystem;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\CurrencyRepository;
use App\Repositories\ExchangeRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentSystemRepository;
use App\Repositories\UserRepository;
use App\Transformers\ExchangeTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Hash;

class ExchangeController extends Controller
{
	public function rules(): array
	{
		return [
			'in_amount' => 'required',
			'out_payee' => 'required'
		];
	}


	public function messages(): array
	{
		return [
			'in_amount.required' => 'Enter IN amount',
			'out_payee.required' => 'Enter OUT wallet'
		];
	}


	/**
	 *
	 * @param Request $request
	 * @param $hash
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function notAuthUserExchange(Request $request, $hash = null): JsonResponse
	{
		if ($hash)
		{
			$id = Redis::get($hash);
			if (!$id)
		    {
		        throw new NotFoundHttpException('Not found your exchange hash information');
		    }
		    $exchange = Exchange::query()->where('id', $id)->first();
			if ($exchange === null)
		    {
		        throw new NotFoundHttpException('Not found your exchange information');
		    }
	
		    return fractal($exchange, new ExchangeTransformer())
			    ->parseIncludes(['inPayment', 'outPayment', 'inPayment.paymentSystem', 'outPayment.paymentSystem'])
			    ->respond();
		}
		else
		{
			$filters = $this->getFilters($request);
		    $sorts = $this->getSortParameters($request);
		    $limit = $this->getPaginationLimit($request);
		    $offset = $this->getPaginationOffset($request);

		    $relations = $this->getRelationsFromIncludes($request);
	
		    $exchanges = ExchangeRepository::getExchanges($filters, $sorts, $relations, ['*'], null, $limit, $offset);

		    $meta = [
			    'count' => ExchangeRepository::getExchangesCount($filters, null)
		    ];

		    return fractal($exchanges, new ExchangeTransformer())
			    ->parseIncludes(['inPayment', 'outPayment', 'inPayment.paymentSystem', 'outPayment.paymentSystem', 'user'])
			    ->parseFieldsets([
				        '' => [ 'date', 'in_prefix', 'in_amount', 'comment', 'rating', 'status', 'in_prefix', 'in_amount', 'out_prefix', 'out_amount', 'inPayment', 'outPayment', 'user' ],
					    'inPayment' => [ 'paymentSystem' ],
					    'outPayment' => ['paymentSystem'],
				        'user' => [ 'name', 'family' ],
				        'paymentSystem' => [ 'name', 'logo_link' ]
				    ])
			    ->addMeta($meta)
			    ->respond();
		}
	}


	/**
	 * @param Request $request
	 * @return mixed
	 * @throws \Exception
	 */
	public function userExchanges(Request $request)
	{
		$user = \Auth::user();
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}

		$filters = $this->getFilters($request);
	    $sorts = $this->getSortParameters($request);
	    $search_string = $this->getSearchString($request);
	    $fieldsets = $this->getFieldsets($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $filters['id_user'] = $user->id;
	    $exchanges = ExchangeRepository::getExchanges($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

	    return fractal($exchanges, new ExchangeTransformer())
		    ->parseIncludes(['inPayment', 'outPayment', 'inPayment.paymentSystem', 'outPayment.paymentSystem'])
		    ->parseFieldsets($fieldsets)
		    ->addMeta([ 'count' => ExchangeRepository::getExchangesCount($filters, $search_string) ])
		    ->respond();
	}


	/**
	 * @param Request $request
	 * @param $exchange_id
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function saveComment(Request $request, $exchange_id): JsonResponse
	{
		$user = \Auth::user();
		if ($user === null) {
			throw new NotFoundHttpException('User not found');
		}
		$exchange = Exchange::query()->where([
			['id', '=', $exchange_id],
			['id_user', '=', $user->id]
		])->first();
		if ($exchange === null) {
			throw new NotFoundHttpException('Exchange not found');
		}

		try
		{
			$exchange->comment = $request->get('comment') ?? '';
			$exchange->rating = $request->get('rating') ?? 0;
			$exchange->save();
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Adding exchange comment failed', $e);
		}

		return response()->json(null, Response::HTTP_OK);
	}
	

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function canExecuteCurrentUser(Request $request): JsonResponse
	{
		if (UserRepository::canDoExchange($request->get('amount'), $request->get('currency')) === false)
		{
			return response()->json(
				[
					'data' => [
						'code' => UserRepository::getErrorCodeByCreatingExchange(
							$request->get('amount'),
							$request->get('currency')
						)
					]
				],
				Response::HTTP_OK
			);
		}

		return response()->json(null, Response::HTTP_OK);
	}


	/**
	 * @param $exchange_id
	 * @return JsonResponse
	 *
	 * @throws \Exception
	 */
	public function moderateComment($exchange_id): JsonResponse
	{
		$exchange = Exchange::query()->where('id', '=', $exchange_id)->first();
		if ($exchange === null) {
			throw new NotFoundHttpException('Exchange not found');
		}
		
		try
		{
			$exchange->is_moderated = true;
			$exchange->save();
		}
		catch (\Exception $e)
		{
			throw new SystemErrorException('Exchange comment moderation failed', $e);
		}

		return response()->json(null, Response::HTTP_OK);
	}


	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function add(Request $request): JsonResponse
	{
		$ps_commission = Commission::query()->where('id', $request->get('commission_id'))->first();
		if ($ps_commission === null) {
			throw new NotFoundHttpException('Commission not found');
		}
		$wallet = Wallet::query()->where('id', $ps_commission->wallet_id)->first();
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}
		$payment_system = PaymentSystem::query()->where('id', $ps_commission->payment_system_id)->first();
		if ($payment_system === null) {
			throw new NotFoundHttpException('Out payment system not found');
		}
		if (UserRepository::canDoExchange($request->get('in_amount'), $wallet->currency) === false)
		{
			return response()->json(['data' => ['code' => UserRepository::getErrorCodeByCreatingExchange($request->get('in_amount'), $wallet->currency)]], Response::HTTP_UNPROCESSABLE_ENTITY);
		}
		$user = \Auth::user();
		if ($user === null && !$request->get('email'))
		{
			return response()->json(['errors' => ['email' => 'Enter your email']], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		$this->validate($request, $this->rules(), $this->messages());

		$email = $user ? $user->email : $request->get('email');

		try {
			// псевдорегистрация
			$user = User::query()->where('email', $email)->first();
			if ($user === null)
			{
				$user = new User();
				$user->email = $request->get('email');
				$user->password = Hash::make($request->get('email'));

				$user->save();
			}

			$exchange = ExchangeRepository::createExchange($user, $request->get('commission_id'), $request->get('in_amount'), $request->get('out_payee'));
			if ($exchange)
			{
				$form = PaymentRepository::getFormRedirect($wallet->ps_type, $ps_commission->wallet_id, $exchange->in_amount, $exchange->in_currency, $exchange->id);
				Mail::to($user->email)->send(new ExchangeCreatedMail($user, $exchange, $form['hash']));
			}
		}
		catch (\Exception $e)
	    {
		    throw new SystemErrorException('Adding exchange by user failed', $e);
	    }

		return response()->json([ 'data' => $form ],Response::HTTP_OK);
	}


	public function notAuthUserExchangeView(Request $request)
	{
		$filters = $this->getFilters($request);
	    $sorts = $this->getSortParameters($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $payment_systems = ExchangeRepository::getExchanges($filters, $sorts, $relations, ['*'], null, $limit, $offset);

	    $meta = [
		    'count' => ExchangeRepository::getExchangesCount($filters, null)
	    ];

	    return fractal($payment_systems, new ExchangeTransformer())
		    ->parseIncludes(['inPayment', 'outPayment', 'inPayment.paymentSystem', 'outPayment.paymentSystem'])
		    ->parseFieldsets([
			        '' => [ 'date', 'in_prefix', 'in_amount', 'comment', 'rating', 'status', 'in_prefix', 'in_amount', 'out_prefix', 'out_amount', 'inPayment', 'outPayment' ],
				    'inPayment' => [ 'paymentSystem' ],
				    'outPayment' => ['paymentSystem'],
			        'paymentSystem' => [ 'name' ]
			    ])
		    ->addMeta($meta)
		    ->respond();
	}


	public function getExchanges(Request $request)
    {
	    $filters = $this->getFilters($request);
	    $sorts = $this->getSortParameters($request);
	    $search_string = $this->getSearchString($request);
	    $fieldsets = $this->getFieldsets($request);
	    $includes = $this->getIncludes($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $payment_systems = ExchangeRepository::getExchanges($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

	    $meta = [
		    'count' => ExchangeRepository::getExchangesCount($filters, $search_string),
		    'users' => ExchangeRepository::getAvailableUsers(),
		    'payment_systems' => array_values(PaymentSystemRepository::getAvailablePaymentSystems()),
			'currencies' => array_values(CurrencyRepository::getAvailableCurrencies()),
		    'statuses' => [
		    	[
		    		'label' => 'no income / not confirmed',
				    'value' => 'create'
			    ],
			    [
		    		'label' => 'without withdrawal / not confirmed',
				    'value' => 'start'
			    ],
			    [
		    		'label' => 'finished',
				    'value' => 'finish'
			    ]
		    ]
	    ];

	    return fractal($payment_systems, new ExchangeTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }
}
