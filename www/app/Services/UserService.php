<?php

namespace App\Services;

use App\Helpers\FileHelper;
use App\Models\User;

/**
 * Class UserService
 * @package App\Services
 */
class UserService
{
	private $file_helper;


	/**
	 * UserService constructor.
	 * @param FileHelper $file_helper
	 */
	public function __construct(FileHelper $file_helper)
	{
		$this->file_helper = $file_helper;
	}


	/**
	 * @param User $user
	 * @param $base64_string
	 * @return string|null
	 * @throws \Exception
	 */
	public function getProcessedUserAvatar(User $user, $base64_string)
	{
		if (!empty($base64_string)) {
			return $this->file_helper->uploadFileFromStringInBase64($base64_string, 'avatars');
		}

		if ($user->avatar)
		{
			$this->file_helper->removeFileFromDisk('avatars', $user->avatar);
		}

		return null;
	}
}