<?php

namespace App\Services;

use App\Helpers\FileHelper;
use App\Helpers\TextHelper;
use Illuminate\Support\Facades\Storage;

/**
 * Class NewsService
 * @package App\Services
 */
class NewsService
{
	private $text_helper;
	private $file_helper;


	/**
	 * NewsService constructor.
	 * @param TextHelper $text_helper
	 * @param FileHelper $file_helper
	 */
	public function __construct(TextHelper $text_helper, FileHelper $file_helper)
	{
		$this->text_helper = $text_helper;
		$this->file_helper = $file_helper;
	}


	/**
	 * @param $text
	 * @return mixed
	 * @throws \Exception
	 */
	public function getProcessedNewsText($text)
	{
		$text = $this->processExternalImages($text);

		return $text;
	}


	/**
	 * @param $text
	 * @return mixed
	 * @throws \Exception
	 */
	private function processExternalImages($text)
	{
		$urls = $this->text_helper->getUrlsFromText($text);
		$storage_urls = $this->file_helper->uploadExternalFilesByToStorage($urls, 'froala');
		$text = $this->replaceExternalUrlsByStorageUrls($text, $storage_urls, 'froala');

		return $text;
	}


	/**
	 * @param $text
	 * @param $urls
	 * @param $storage_disk
	 * @return mixed
	 */
	private function replaceExternalUrlsByStorageUrls($text, $urls, $storage_disk)
	{
		if ($urls) {
			foreach ($urls as $path => $name) {
				$text = str_replace($path, Storage::disk($storage_disk)->url($name), $text);
			}
		}

		return $text;
	}
}