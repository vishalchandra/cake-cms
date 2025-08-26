<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Core\Configure;
use Cake\Mailer\Mailer;

class UserMailer extends Mailer
{
    public function emailVerification($user)
    {
        $this
            ->setTo($user->email)
            ->setSubject('Verify your email address')
            ->setEmailFormat('both')
            ->setViewVars([
                'user' => $user,
                'verifyUrl' => Configure::read('App.fullBaseUrl') . '/verify?token=' . $user->email_verification_token
            ])
            ->viewBuilder()
            ->setTemplate('email_verification');
    }

    public function passwordReset($user, $token)
    {
        $this
            ->setTo($user->email)
            ->setSubject('Reset your password')
            ->setEmailFormat('both')
            ->setViewVars([
                'user' => $user,
                'resetUrl' => Configure::read('App.fullBaseUrl') . '/password/reset?token=' . $token
            ])
            ->viewBuilder()
            ->setTemplate('password_reset');
    }
}