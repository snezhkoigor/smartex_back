<?php

namespace App\Helpers;

class TextHelper
{
	/**
	 * @param string $text
	 * @return array
	 */
	public function getUrlsFromText($text): array
	{
		$images = [];

		if (preg_match_all('/<img[^>]+>/i', $text, $matches)) {
			foreach ($matches[0] as $img_tag) {
				if (!empty($img_tag) && preg_match_all('/(alt|title|src)=("[^"]*")/i', $img_tag, $tag) && !empty($tag[2][0])) {
					$images[$img_tag] = str_replace('"', '', $tag[2][0]);
				}
			}
		}

		return $images;
	}
}