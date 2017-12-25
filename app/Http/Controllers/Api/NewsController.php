<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemErrorException;
use App\Models\News;
use App\Repositories\NewsRepository;
use App\Services\NewsService;
use App\Transformers\NewsTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewsController extends Controller
{
	public function rules()
	{
		return [
			'title' => 'required',
			'text' => 'required'
		];
	}

	public function messages()
	{
		return [
			'title.required' => 'Enter news title',
			'text.required' => 'Enter news text',
		];
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
	    ];

	    return fractal($news, new NewsTransformer())
		    ->parseIncludes($includes)
		    ->parseFieldsets($fieldsets)
		    ->addMeta($meta)
		    ->respond();
    }

	public function getNewsById($news_id)
	{
		$news = News::find($news_id);
		if ($news === null) {
			throw new NotFoundHttpException('News not found');
		}

		return fractal($news, new NewsTransformer())
			->respond();
	}

    public function add(Request $request)
    {
    	$this->validate($request, $this->rules(), $this->messages());

    	try
	    {
		    $news = new News();
		    $news->fill($request->all());
		    $news->text = app()->make('news_service')->getProcessedNewsText($request->get('text'));
		    $news->active = $request->get('active', true);
		    $news->save([], true);
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Adding news failed', $e);
	    }

	    return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
    }

    public function updateById(Request $request, $news_id)
    {
	    $this->validate($request, $this->rules(), $this->messages());

	    $news = News::find($news_id);
	    if ($news === null) {
		    throw new NotFoundHttpException('News not found');
	    }

	    try
	    {
		    $news->fill($request->all());
		    $news->active = $request->get('active');
		    $news->text = app()->make('news_service')->getProcessedNewsText($request->get('text'));
		    $news->save([], true);
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Updating news failed', $e);
	    }

	    return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
    }

    public function deleteById($news_id)
    {
	    $news = News::find($news_id);
	    if ($news === null) {
		    throw new NotFoundHttpException('News not found');
	    }

	    try
	    {
		    $news->is_deleted = true;
		    $news->save([], true);
	    }
	    catch (\Exception $e)
	    {
		    throw new SystemErrorException('Updating news failed', $e);
	    }

	    return response()->json(['data' => null], Response::HTTP_NO_CONTENT);
    }
}
