<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class FileHelper
{
	public function uploadFilesByFraolaToStorage($urls, $storage_path)
	{
		$storage_urls = [];

		if ($urls) {
			foreach ($urls as $url) {
				$file = file_get_contents($url);

				if ($storage_path) {
					$file_name = $this->getFileNameFromUrl(parse_url($url)['path']);

					if (!empty($file_name)) {
						Storage::disk('froala')->put($file_name, $file);
					}

					$storage_urls[$url] = $file_name;
				} else {
					throw new \Exception('Error with loading file from ' . $url);
				}
			}
		}

		return $storage_urls;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	public function getFileNameFromUrl($url)
	{
		$pos = strrpos($url, '/');

		if ($pos !== false) {
			return substr($url, $pos + 1);
		} else {
			return null;
		}
	}
}