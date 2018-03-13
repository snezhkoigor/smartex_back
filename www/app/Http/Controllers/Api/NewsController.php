<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Models\News;
use App\Models\Test;
use App\Repositories\NewsRepository;
use App\Services\NewsService;
use App\Transformers\NewsTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
	private $news_service;

	public function __construct(NewsService $news_service)
	{
		$this->news_service = $news_service;
	}

	public function rules(): array
	{
		return [
			'title' => 'required',
			'text' => 'required'
		];
	}

	public function messages(): array
	{
		return [
			'title.required' => 'Enter news title',
			'text.required' => 'Enter news text',
		];
	}

	public function view(Request $request)
    {
	    $filters = $this->getFilters($request);
	    $sorts = $this->getSortParameters($request);
	    $search_string = $this->getSearchString($request);
	    $fieldsets = $this->getFieldsets($request);
	    $includes = $this->getIncludes($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $news = NewsRepository::getNews($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

	    $meta = [
		    'count' => NewsRepository::getNewsCount($filters, $search_string)
	    ];

	    return fractal($news, new NewsTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }
	
	
	/**
	 * @param $news_id
	 * @return JsonResponse
	 *
	 * @throws \Exception
	 */
    public function show($news_id): JsonResponse
    {
	    $news = News::query()->find($news_id);
		if ($news === null) {
			throw new NotFoundHttpException('News not found');
		}

		return fractal($news, new NewsTransformer())
			->respond();
    }

    public function getNews(Request $request)
    {
	    $filters = $this->getFilters($request);
	    $sorts = $this->getSortParameters($request);
	    $search_string = $this->getSearchString($request);
	    $fieldsets = $this->getFieldsets($request);
	    $includes = $this->getIncludes($request);
	    $limit = $this->getPaginationLimit($request);
	    $offset = $this->getPaginationOffset($request);

	    $relations = $this->getRelationsFromIncludes($request);

	    $news = NewsRepository::getNews($filters, $sorts, $relations, ['*'], $search_string, $limit, $offset);

	    $meta = [
		    'count' => NewsRepository::getNewsCount($filters, $search_string),
		    'languages' => array_values(News::$languages)
	    ];

	    return fractal($news, new NewsTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }


    public function getFormMeta()
	{
		return fractal(null, new NewsTransformer())
			->addMeta([
				'languages' => array_values(News::$languages)
			])
			->respond();
	}


	/**
	 * @param $news_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
	public function getNewsById($news_id): JsonResponse
	{
		$news = News::query()->find($news_id);
		if ($news === null) {
			throw new NotFoundHttpException('News not found');
		}

		return fractal($news, new NewsTransformer())
			->addMeta([
				'languages' => array_values(News::$languages)
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
    	$this->validate($request, $this->rules(), $this->messages());

    	try
	    {
		    $news = new News();
		    $news->fill($request->all());
		    $news->text = $this->news_service->getProcessedNewsText($request->get('text'));
		    $news->active = $request->get('active', true);
		    $news->save();
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Adding news failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }


	/**
	 * @param Request $request
	 * @param $news_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
    public function updateById(Request $request, $news_id): JsonResponse
    {
	    $this->validate($request, $this->rules(), $this->messages());

	    $news = News::query()->find($news_id);
	    if ($news === null) {
		    throw new NotFoundHttpException('News not found');
	    }

	    try
	    {
		    $news->fill($request->all());
		    $news->active = $request->get('active');
		    $news->text = $this->news_service->getProcessedNewsText($request->get('text'));
		    $news->save();
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Updating news failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }


	/**
	 * @param $news_id
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Exception
	 */
    public function deleteById($news_id): JsonResponse
    {
	    $news = News::query()->find($news_id);
	    if ($news === null) {
		    throw new NotFoundHttpException('News not found');
	    }

	    try
	    {
		    $news->delete();
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Deleting news failed', $e);
	    }

	    return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
