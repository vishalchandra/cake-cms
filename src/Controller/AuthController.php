<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Mailer\MailerAwareTrait;

class AuthController extends AppController
{
    use MailerAwareTrait;

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow unauthenticated access to auth actions
        $this->Authentication->addUnauthenticatedActions(['login', 'register', 'verify', 'forgotPassword', 'resetPassword']);
    }

    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        
        // If user is already logged in, redirect
        if ($result && $result->isValid()) {
            $redirect = $this->request->getQuery('redirect', '/');
            return $this->redirect($redirect);
        }
        
        // Display error if login failed
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Invalid email or password'));
        }
    }

    public function register()
    {
        $this->request->allowMethod(['get', 'post']);
        
        $user = $this->fetchTable('Users')->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->fetchTable('Users')->patchEntity($user, $this->request->getData());
            $user->email_verified = false;
            $user->email_verification_token = bin2hex(random_bytes(32));
            $user->role = 'user';
            
            if ($this->fetchTable('Users')->save($user)) {
                // Send verification email
                $this->getMailer('User')->send('emailVerification', [$user]);
                
                $this->Flash->success(__('Registration successful! Please check your email to verify your account.'));
                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('Unable to register. Please try again.'));
        }
        $this->set(compact('user'));
    }

    public function verify()
    {
        $token = $this->request->getQuery('token');
        if (!$token) {
            $this->Flash->error(__('Invalid verification link.'));
            return $this->redirect(['action' => 'login']);
        }

        $user = $this->fetchTable('Users')->find()
            ->where(['email_verification_token' => $token, 'email_verified' => false])
            ->first();

        if (!$user) {
            $this->Flash->error(__('Invalid or expired verification link.'));
            return $this->redirect(['action' => 'login']);
        }

        $user->email_verified = true;
        $user->email_verification_token = null;

        if ($this->fetchTable('Users')->save($user)) {
            $this->Flash->success(__('Email verified successfully! You can now login.'));
        } else {
            $this->Flash->error(__('Unable to verify email. Please try again.'));
        }

        return $this->redirect(['action' => 'login']);
    }

    public function forgotPassword()
    {
        $this->request->allowMethod(['get', 'post']);
        
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $user = $this->fetchTable('Users')->find()
                ->where(['email' => $email, 'email_verified' => true])
                ->first();

            if ($user) {
                // Create password reset token
                $passwordReset = $this->fetchTable('PasswordResets')->newEntity([
                    'user_id' => $user->id,
                    'token' => bin2hex(random_bytes(32)),
                    'expires_at' => new \DateTime('+1 hour')
                ]);

                if ($this->fetchTable('PasswordResets')->save($passwordReset)) {
                    $this->getMailer('User')->send('passwordReset', [$user, $passwordReset->token]);
                }
            }
            
            // Always show success message for security
            $this->Flash->success(__('If the email exists, a password reset link has been sent.'));
            return $this->redirect(['action' => 'login']);
        }
    }

    public function resetPassword()
    {
        $this->request->allowMethod(['get', 'post']);
        
        $token = $this->request->getQuery('token');
        if (!$token) {
            $this->Flash->error(__('Invalid reset link.'));
            return $this->redirect(['action' => 'login']);
        }

        $passwordReset = $this->fetchTable('PasswordResets')->find()
            ->contain(['Users'])
            ->where([
                'PasswordResets.token' => $token,
                'PasswordResets.expires_at >' => new \DateTime()
            ])
            ->first();

        if (!$passwordReset) {
            $this->Flash->error(__('Invalid or expired reset link.'));
            return $this->redirect(['action' => 'login']);
        }

        if ($this->request->is('post')) {
            $password = $this->request->getData('password');
            $confirmPassword = $this->request->getData('confirm_password');

            if ($password !== $confirmPassword) {
                $this->Flash->error(__('Passwords do not match.'));
            } elseif (strlen($password) < 8) {
                $this->Flash->error(__('Password must be at least 8 characters long.'));
            } else {
                $passwordReset->user->password = $password;
                if ($this->fetchTable('Users')->save($passwordReset->user)) {
                    // Delete the reset token
                    $this->fetchTable('PasswordResets')->delete($passwordReset);
                    
                    $this->Flash->success(__('Password reset successfully! You can now login.'));
                    return $this->redirect(['action' => 'login']);
                }
                $this->Flash->error(__('Unable to reset password. Please try again.'));
            }
        }

        $this->set(compact('token'));
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            $this->Flash->success(__('You have been logged out.'));
        }
        return $this->redirect(['action' => 'login']);
    }
}