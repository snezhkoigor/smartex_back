<?php

namespace App\Http\Controllers\Api;

use App\Repositories\CurrencyRepository;
use App\Repositories\ExchangeRepository;
use App\Repositories\PaymentSystemRepository;
use App\Transformers\ExchangeTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExchangeController extends Controller
{
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
	    $users = ExchangeRepository::getExchanges($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

	    $meta = [
		    'count' => ExchangeRepository::getExchangesCount($filters, $search_string),
	    ];

	    return fractal($users, new ExchangeTransformer())
		    ->parseIncludes(['inPayment', 'outPayment'])
		    ->parseFieldsets($fieldsets)
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
