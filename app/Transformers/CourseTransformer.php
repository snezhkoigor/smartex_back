<?php

namespace App\Transformers;

use App\Models\Course;
use League\Fractal\TransformerAbstract;

/**
 * Class CourseTransformer
 * @package App\Transformers
 */
class CourseTransformer extends TransformerAbstract
{
	protected $availableIncludes = [];


	/**
	 * @param Course $course
	 * @return array
	 */
	public function transform(Course $course)
	{
		$data = [
			'id' => (int)$course->id,
			'date' => $course->date,
			'in_currency' => $course->in_currency,
			'out_currency' => $course->out_currency,
			'course' => (float)$course->course,
			'created_at' => $course->created_at,
			'updated_at' => $course->updated_at
		];

		return $data;
	}
}