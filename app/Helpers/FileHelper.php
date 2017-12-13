<?php

namespace App\Helpers;

class FileHelper
{
	public function uploadFilesToStorage($urls, $storage_path)
	{
		$storage_urls = [];

		if ($urls) {
			foreach ($urls as $url) {
				$file = file_get_contents($url);

				if ($storage_path) {
					$file_name = $this->getFileNameFromUrl(parse_url($url)['path']);

					if (!empty($file_name)) {
						file_put_contents(storage_path() . '/' . $storage_path . '/' . $file_name, $file);
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