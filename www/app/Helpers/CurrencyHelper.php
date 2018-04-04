<?php

namespace App\Helpers;

use App\Repositories\CourseRepository;

class CurrencyHelper
{
	/**
	 * @param $in
	 * @param $out
	 * @param $amount
	 * @return float
	 */
	public static function convert($in, $out, $amount): float
	{
		if (mb_strtoupper($in) !== mb_strtoupper($out))
		{
			$course = CourseRepository::getCourse($in, $out);
			return round((float)$course * $amount, 4);
		}

		return (float)$amount;
	}
}