<?php
declare(strict_types=1);

namespace App\Controller;

class FeedController extends AppController
{
    public function index()
    {
        // Get recent posts with their authors and comment counts
        $posts = $this->fetchTable('Posts')->find()
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
                'comment_count' => $this->fetchTable('Posts')->find()->func()->count('Comments.id')
            ])
            ->leftJoinWith('Comments')
            ->groupBy(['Posts.id'])
            ->orderByDesc('Posts.created')
            ->limit(20);

        $this->set(compact('posts'));
    }
}