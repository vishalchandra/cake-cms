<?php
declare(strict_types=1);

namespace App\Controller;

class FeedController extends AppController
{
    public function index()
    {
        $postsTable = $this->fetchTable('Posts');
        
        // Get recent posts with their authors and comment counts
        $posts = $postsTable->find()
            ->contain([
                'Users' => [
                    'fields' => ['id', 'username', 'display_name']
                ]
            ])
            ->select([
                'Posts.id',
                'Posts.title', 
                'Posts.body',
                'Posts.created',
                'Posts.user_id',
                'comment_count' => $postsTable->find()->func()->count('Comments.id')
            ])
            ->leftJoinWith('Comments')
            ->groupBy(['Posts.id', 'Posts.title', 'Posts.body', 'Posts.created', 'Posts.user_id', 'Users.id', 'Users.username', 'Users.display_name'])
            ->orderByDesc('Posts.created')
            ->limit(20);

        $this->set(compact('posts'));
    }
}