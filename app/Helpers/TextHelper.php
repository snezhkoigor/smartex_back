<?php

namespace App\Helpers;

class TextHelper
{
	/**
	 * @param string $text
	 * @return array array
	 */
	public function getUrlsFromText($text)
	{
		$images = [];

		if (preg_match_all('/<img[^>]+>/i', $text, $matches)) {
			foreach ($matches[0] as $img_tag) {
				if (!empty($img_tag)) {
					if (preg_match_all('/(alt|title|src)=("[^"]*")/i', $img_tag, $tag)) {
						if (!empty($tag[2][0])) {
							$images[$img_tag] = str_replace('"', '', $tag[2][0]);
						}
					}
				}
			}
		}

		return $images;
	}
}