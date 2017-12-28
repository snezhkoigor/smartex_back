<?php

namespace App\Transformers;

use App\Models\News;
use League\Fractal\TransformerAbstract;

/**
 * Class NewsTransformer
 * @package App\Transformers
 */
class NewsTransformer extends TransformerAbstract
{
	protected $availableIncludes = [];


	/**
	 * @param News $news
	 * @return array
	 */
	public function transform(News $news)
	{
		$data = [
			'id' => (int)$news->id,
			'title' => $news->title,
			'text' => $news->text,
			'date' => $news->date,
			'meta_description' => $news->meta_description,
			'meta_key' => $news->meta_key,
			'active' => (bool)$news->active,
			'created_at' => $news->created_at,
			'updated_at' => $news->updated_at
		];

		return $data;
	}
}