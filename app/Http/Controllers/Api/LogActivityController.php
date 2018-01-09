<?php

namespace App\Http\Controllers\Api;

use App\Models\LogActivity;
use App\Repositories\LogActivityRepository;
use App\Repositories\UserRepository;
use App\Transformers\LogActivityTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LogActivityController extends Controller
{
	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getLogActivities(Request $request)
    {
	    $filters = $this->getFilters($request);
	    $fieldsets = $this->getFieldsets($request);
	    $sorts = $this->getSortParameters($request);
	    $includes = $this->getIncludes($request);
	    $search_string = $this->getSearchString($request);
	    $relations = $this->getRelationsFromIncludes($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $activities = LogActivityRepository::getLogActivities($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

	    $meta = [
		    'count' => LogActivityRepository::getLogActivitiesCount($filters, $search_string),
		    'subject_types' => array_values(LogActivityRepository::getAvailableSubjectTypesForMeta()),
		    'users' => array_values(UserRepository::getAvailableUsersInLogActivitiesForMeta()),
		    'log_names' => array_values(LogActivityRepository::getAvailableLogNamesForMeta())
	    ];

	    return fractal($activities, new LogActivityTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }


	/**
	 * @param Request $request
	 * @param $log_id
	 * @return mixed
	 *
	 * @throws \Exception
	 */
    public function getLogActivityById(Request $request, $log_id)
    {
	    $activity = LogActivity::find($log_id);
	    if ($activity === null) {
		    throw new NotFoundHttpException('Log activity not found');
	    }

	    $fieldsets = $this->getFieldsets($request);
	    $includes = $this->getIncludes($request);

	    $meta = [
		    'subject_types' => array_values(LogActivityRepository::getAvailableSubjectTypesForMeta()),
		    'users' => array_values(UserRepository::getAvailableUsersInLogActivitiesForMeta()),
		    'log_names' => array_values(LogActivityRepository::getAvailableLogNamesForMeta())
	    ];

	    return fractal($activity, new LogActivityTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }
}
