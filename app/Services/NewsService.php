<?php

namespace App\Services;

use App\Helpers\FileHelper;
use App\Helpers\TextHelper;

class NewsService
{
	private $text_helper;
	private $file_helper;

	public function __construct(TextHelper $text_helper, FileHelper $file_helper)
	{
		$this->text_helper = $text_helper;
		$this->file_helper = $file_helper;
	}

	public function getProcessedNewsText($text)
	{
		$text = $this->processExternalImages($text);

		return $text;
	}

	private function processExternalImages($text)
	{
		$urls = $this->text_helper->getUrlsFromText($text);
		$storage_urls = $this->file_helper->uploadFilesByFraolaToStorage($urls, config('froala_wysiwyg.storage_path'));
		$text = $this->replaceExternalUrlsByStorageUrls($text, $storage_urls);

		return $text;
	}

	private function replaceExternalUrlsByStorageUrls($text, $urls)
	{
		if ($urls) {
			foreach ($urls as $path => $name) {
				$text = str_replace($path, url('api/files/' . config('froala_wysiwyg.storage_path'), ['filename' => $name]), $text);
			}
		}

		return $text;
	}
}