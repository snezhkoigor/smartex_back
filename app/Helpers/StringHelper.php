<?php

namespace App\Helpers;

class StringHelper
{
	public static function truncate($string, $maxChars = 10, $postfix = '...')
	{
		return substr($string, 0, $maxChars) . (strlen($string) > $maxChars ? $postfix : '');
	}
}