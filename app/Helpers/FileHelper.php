<?php

namespace App\Helpers;

use App\Exceptions\SystemErrorException;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
	/**
	 * @param array $external_urls
	 * @param $storage_disk
	 * @return array
	 * @throws \Exception
	 */
	public function uploadExternalFilesByToStorage($external_urls, $storage_disk)
	{
		$uploaded_files = [];

		if ($external_urls) {
			foreach ($external_urls as $url) {
				$file = file_get_contents($url);

				if ($storage_disk) {
					$file_name = $this->getFileNameFromUrl(parse_url($url)['path']);

					if (!empty($file_name)) {
						try {
							$this->saveFileToDisk($storage_disk, $file_name, $file);
							$uploaded_files[$url] = $file_name;
						} catch (\Exception $e) {
							throw new SystemErrorException('Copy file failed', $e);
						}
					}
				} else {
					throw new SystemErrorException('Error with loading file from ' . $url);
				}
			}
		}

		return $uploaded_files;
	}


	/**
	 * @param string $base64_string
	 * @param string $storage_disk
	 * @return string
	 * @throws \Exception
	 */
	public function uploadFileFromStringInBase64($base64_string, $storage_disk)
	{
		$extension = $this->getExtensionByMimeTypeFromBase64String($base64_string);
		if (empty($extension)) {
			throw new SystemErrorException('Can not get file extension');
		}

		try {
			$base64_string = $this->removeDataTypeFromBase64String($base64_string);
			$image_string = base64_decode($base64_string);

			$file_name = self::getRandomFileName() . '.' . $extension;
			$this->saveFileToDisk($storage_disk, $file_name, $image_string);
		} catch (\Exception $e) {
		    throw new SystemErrorException('Copy file failed', $e);
	    }

		return $file_name;
	}


	/**
	 * @return string
	 */
	public static function getRandomFileName()
	{
		return md5(mt_rand(0, 1000000) . microtime());
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
		}

		return null;
	}


	/**
	 * @param $mime
	 * @return mixed
	 */
	public function getExtensionByMime($mime)
	{
		$map = [
			'image/jpeg' => 'jpg',
			'image/jpg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/bmp' => 'bmp',
			'image/svg+xml' => 'svg',
			'video/mpeg' => 'mpeg',
			'video/mp4' => 'mp4',
			'video/x-ms-wmv' => 'wmv',
			'video/x-flv' => 'flv',
			'video/3gpp' => '3gp',
			'video/x-msvideo' => 'avi',
		];

		return $map[$mime];
	}


	/**
	 * @param $storage_disk
	 * @param $file_name
	 */
	public function removeFileFromDisk($storage_disk, $file_name)
	{
		Storage::disk($storage_disk)->delete($file_name);
	}


	/**
	 * @param $base64_string
	 * @return mixed|string
	 */
	private function getExtensionByMimeTypeFromBase64String($base64_string)
	{
		$result = '';
		if (preg_match("/(data[^']*,)([^']*)/i", $base64_string, $matches)) {
			if (!empty($matches[1])) {
				$mime = str_replace(['data:', ';base64,'], '', $matches[1]);
				$result = $this->getExtensionByMime($mime);
			}
		}

		return $result;
	}


	/**
	 * @param $storage_disk
	 * @param $file_name
	 * @param $file_content
	 */
	private function saveFileToDisk($storage_disk, $file_name, $file_content)
	{
		Storage::disk($storage_disk)->put($file_name, $file_content);
	}


	/**
	 * @param $base64_string
	 * @return mixed
	 */
	private function removeDataTypeFromBase64String($base64_string)
	{
		if (preg_match("/(data[^']*,)([^']*)/i", $base64_string, $matches)) {
			if (!empty($matches[2])) {
				$base64_string = $matches[2];
			}
		}

		return $base64_string;
	}
}