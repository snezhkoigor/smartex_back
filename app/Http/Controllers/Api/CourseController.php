<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Models\Course;
use App\Repositories\CourseRepository;
use App\Transformers\CourseTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
	public function rules()
	{
		return [
			'course' => 'required|numeric',
			'in_currency' => 'required',
			'out_currency' => 'required',
			'date' => 'required',
		];
	}

	public function messages()
	{
		return [
			'course.required' => 'Enter course',
			'course.numeric' => 'Bad format of course value. Must be numeric',
			'in_currency.required' => 'Enter "From" currency',
			'out_currency.required' => 'Enter "To" currency',
			'date.required' => 'Enter date of course',
		];
	}

    public function getCourses(Request $request)
    {
	    $filters = $this->getFilters($request);
	    $sorts = $this->getSortParameters($request);
	    $search_string = $this->getSearchString($request);
	    $fieldsets = $this->getFieldsets($request);
	    $includes = $this->getIncludes($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $lastDateInCourses = CourseRepository::getLastDateFromCourses();
	    if ($lastDateInCourses) {
		    $filters['date'] = $lastDateInCourses->date;
	    }

	    $courses = CourseRepository::getCourses($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

	    $meta = [
		    'count' => CourseRepository::getNewsCount($filters, $search_string),
	    ];

	    return fractal($courses, new CourseTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }


	/**
	 * @param Request $request
	 * @param $course_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
    public function updateById(Request $request, $course_id): JsonResponse
    {
	    $this->validate($request, $this->rules(), $this->messages());

	    $course = Course::query()->find($course_id);
	    if ($course === null) {
		    throw new NotFoundHttpException('Course not found');
	    }

	    try
	    {
		    $course->fill($request->all());
		    $course->save();
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Updating course failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
