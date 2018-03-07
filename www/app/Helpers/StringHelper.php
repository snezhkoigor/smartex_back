<?php

namespace App\Helpers;

class StringHelper
{
	/**
	 * @param $string
	 * @param int $maxChars
	 * @param string $postfix
	 * @return string
	 */
	public static function truncate($string, $maxChars = 10, $postfix = '...'): string
	{
		return substr($string, 0, $maxChars) . (\strlen($string) > $maxChars ? $postfix : '');
	}
}