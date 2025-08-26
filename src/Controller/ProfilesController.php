<?php
declare(strict_types=1);

namespace App\Controller;

class ProfilesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Paginator');
    }

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

        $postsTable = $this->fetchTable('Posts');
        
        // Configure pagination for user's posts
        $this->paginate = [
            'limit' => 10,
            'order' => [
                'Posts.created' => 'DESC'
            ],
            'conditions' => [
                'Posts.user_id' => $user->id
            ],
            'contain' => [
                'Comments' => [
                    'Users' => [
                        'fields' => ['id', 'username', 'display_name']
                    ],
                    'limit' => 3,
                    'order' => ['Comments.created' => 'DESC']
                ]
            ]
        ];
        
        // Get paginated posts
        $posts = $this->paginate($postsTable);

        // Get user statistics
        $postCount = $this->fetchTable('Posts')->find()
            ->where(['user_id' => $user->id])
            ->count();

        $commentCount = $this->fetchTable('Comments')->find()
            ->where(['user_id' => $user->id])
            ->count();

        // Get join date
        $joinDate = $user->created;

        $this->set(compact('user', 'posts', 'postCount', 'commentCount', 'joinDate'));
    }
}