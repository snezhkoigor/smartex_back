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
	private $user;


	/**
	 * UserService constructor.
	 * @param User $user
	 * @param FileHelper $file_helper
	 */
	public function __construct(User $user, FileHelper $file_helper)
	{
		$this->user = $user;
		$this->file_helper = $file_helper;
	}


	/**
	 * @param string $base64_string
	 * @return string
	 * @throws \Exception
	 */
	public function getProcessedUserAvatar($base64_string)
	{
		if ($base64_string) {
			return $this->file_helper->uploadFileFromStringInBase64($base64_string, 'avatars');
		}

		$this->file_helper->removeFileFromDisk('avatars', $this->user->avatar);

		return null;
	}
}