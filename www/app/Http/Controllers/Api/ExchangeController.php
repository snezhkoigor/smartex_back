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
	 * @param $hash
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function notAuthUserExchange($hash): JsonResponse
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
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function add(Request $request): JsonResponse
	{
		$ps_commission = Commission::find($request->get('commission_id'));
		if ($ps_commission === null) {
			throw new NotFoundHttpException('Commission not found');
		}
		$wallet = Wallet::find($ps_commission->wallet_id);
		if ($wallet === null) {
			throw new NotFoundHttpException('Wallet not found');
		}
		$payment_system = PaymentSystem::find($ps_commission->payment_system_id);
		if ($payment_system === null) {
			throw new NotFoundHttpException('Out payment system not found');
		}

		$this->validate($request, $this->rules(), $this->messages());

		if (UserRepository::canDoExchange($request->get('in_amount'), $wallet->currency) === false)
		{
			return response()->json(['data' => ['code' => UserRepository::getErrorCodeByCreatingExchange($request->get('in_amount'), $wallet->currency)]], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		$user = \Auth::user();
		if ($user === null && !$request->get('email'))
		{
			return response()->json(['errors' => ['email' => 'Enter your email']], Response::HTTP_UNPROCESSABLE_ENTITY);
		}
//		if ($request->get('email') && User::query()->where('email', $request->get('email'))->first())
//		{
//			return response()->json(['errors' => ['email' => 'This email used another user']], Response::HTTP_UNPROCESSABLE_ENTITY);
//		}

		try {
			// псевдорегистрация
			$user = User::query()->where('email', $request->get('email'))->first();
			if ($user === null)
			{
				$user = new User();
				$user->email = $request->get('email');
				$user->password = Hash::make($request->get('email'));

				$user->save();
			}

			$discount = round($ps_commission->commission * (int)$user->discount/100, 4);
			$fee = round($request->get('in_amount') * ($ps_commission->commission/100 - $discount), 4);
			$amount = (float)$request->get('in_amount') - $fee;

			$exchange = new Exchange();
			$exchange->date = Carbon::today()->format('Y-m-d H:i:s');
			$exchange->id_user = $user->id;
			$exchange->in_payment = $wallet->ps_type;
			$exchange->in_id_pay = 0;
			$exchange->in_currency = $wallet->currency;
			$exchange->in_amount = $amount;
			$exchange->in_fee = $fee;
			$exchange->out_payment = $payment_system->code;
			$exchange->out_id_pay = 0;
			$exchange->out_currency = $ps_commission->currency;
			$exchange->out_amount = CurrencyHelper::convert($wallet->currency, $ps_commission->currency, $amount);
			$exchange->out_payee = $request->get('out_payee');
			$exchange->out_fee = 0;
			$exchange->in_discount = (int)$user->discount;

			$exchange->save();

			Mail::to($user->email)->send(new ExchangeCreatedMail($user, $exchange));
		}
		catch (\Exception $e)
	    {
		    throw new SystemErrorException('Adding exchange by user failed', $e);
	    }

	    $form = PaymentRepository::getFormRedirect($wallet->ps_type, $ps_commission->wallet_id, $exchange->in_amount, $exchange->in_currency, $exchange->id);
		return response()->json([ 'data' => $form ],Response::HTTP_OK);
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
		    		'label' => 'no income',
				    'value' => 'create'
			    ],
			    [
		    		'label' => 'has income without withdrawal',
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
