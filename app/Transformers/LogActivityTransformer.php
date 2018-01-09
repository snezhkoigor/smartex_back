<?php

namespace App\Transformers;

use App\Models\LogActivity;
use League\Fractal\TransformerAbstract;

class LogActivityTransformer extends TransformerAbstract
{
	protected $availableIncludes = [
		'user'
	];

	public function transform(LogActivity $activity)
	{
		$data = [
			'id' => (int)$activity->id,
			'description' => $activity->description,
			'log_name' => $activity->log_name,
			'subject_id' => (int)$activity->subject_id,
			'subject_type' => $activity->subject_type,
			'causer_id' => (int)$activity->causer_id,
			'causer_type' => $activity->causer_type,
			'properties' => $activity->properties,
			'created_at' => $activity->created_at,
			'updated_at' => $activity->updated_at,
		];

		return $data;
	}

	public function includeUser(LogActivity $activity)
	{
		return $this->item($activity->user, new UserTransformer(), 'user');
	}
}