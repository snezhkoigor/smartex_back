<?php

namespace App\Services;

use App\Repositories\ResetPasswordRepository;
use App\Models\User;

class ResetPasswordService
{
    protected $resetPasswordRepository;
    private $resendAfter = 24;

    public function __construct(ResetPasswordRepository $resetPasswordRepository)
    {
        $this->resetPasswordRepository = $resetPasswordRepository;
    }

    public function token(User $user)
    {
        if (!$this->shouldSend($user)) {
            return null;
        }

        return $this->resetPasswordRepository->create($user);
    }

    public function delete($token)
    {
        return $this->resetPasswordRepository->delete($token);
    }

    public function get(User $user)
    {
        return $this->resetPasswordRepository->get($user);
    }

    public function getByToken($token)
    {
        return $this->resetPasswordRepository->getByToken($token);
    }

    private function shouldSend(User $user)
    {
        $record = $this->resetPasswordRepository->get($user);

        return $record === null || strtotime($record->created_at) + 60 * 60 * $this->resendAfter < time();
    }
}