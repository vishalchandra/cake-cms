<?php
declare(strict_types=1);

namespace App\Controller;

class ProfilesController extends AppController
{
    public function view($username = null)
    {
        if (!$username) {
            throw new \Cake\Http\Exception\NotFoundException();
        }

        // Get user by username
        $user = $this->fetchTable('Users')->find()
            ->where(['username' => $username, 'email_verified' => true])
            ->first();

        if (!$user) {
            $this->Flash->error(__('User not found.'));
            return $this->redirect('/');
        }

        // Get user's posts with comment counts
        $posts = $this->fetchTable('Posts')->find()
            ->contain([
                'Users' => ['fields' => ['id', 'username', 'display_name']]
            ])
            ->select([
                'Posts.id',
                'Posts.title', 
                'Posts.body',
                'Posts.created',
                'Posts.user_id',
                'comment_count' => $this->fetchTable('Posts')->find()->func()->count('Comments.id')
            ])
            ->leftJoinWith('Comments')
            ->where(['Posts.user_id' => $user->id])
            ->groupBy(['Posts.id'])
            ->orderByDesc('Posts.created')
            ->limit(20);

        // Get post count
        $postCount = $this->fetchTable('Posts')->find()
            ->where(['user_id' => $user->id])
            ->count();

        $this->set(compact('user', 'posts', 'postCount'));
    }
}