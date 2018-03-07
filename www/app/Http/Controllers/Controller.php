<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	protected function getFilters(Request $request)
	{
		$filters = $request->get('filters');

		if (empty($filters)) {
			return [];
		}

		$filters = json_decode($filters, true);

		if (json_last_error() === JSON_ERROR_NONE) {
			return $filters;
		}

		return [];
	}

	protected function getSortParameters(Request $request)
	{
		$sort_parameters = $request->get('sort');

		if (empty($sort_parameters)) {
			return [];
		}

		$sort_parameters = json_decode($sort_parameters, true);

		if (json_last_error() === JSON_ERROR_NONE) {
			return $sort_parameters;
		}

		return [];
	}

	protected function getSearchString(Request $request)
	{
		return $request->get('q');
	}

	protected function getFieldsets(Request $request)
	{
		$fieldsets = $request->get('fieldsets');

		if (empty($fieldsets)) {
			return [];
		}

		$fieldsets = json_decode($fieldsets, true);

		if (json_last_error() === JSON_ERROR_NONE) {
			return $fieldsets;
		}

		return [];
	}

	protected function getIncludes(Request $request)
	{
		if ($request->get('include') === null) {
			return [];
		}

		return explode(',', $request->get('include'));
	}

	protected function getPaginationLimit(Request $request)
	{
		$pagination = json_decode($request->get('pagination'), true);

		return !empty($pagination['limit']) ? $pagination['limit'] : null;
	}

	protected function getPaginationOffset(Request $request)
	{
		$pagination = json_decode($request->get('pagination'), true);

		return !empty($pagination['offset']) ? $pagination['offset'] : null;
	}

	protected function getRelationsFromIncludes(Request $request)
	{
		$includes = $this->getIncludes($request);

		array_walk($includes, function(&$item) {
			$item = preg_replace('/\:.*/', '', $item);
		});

		return $includes;
	}
}
