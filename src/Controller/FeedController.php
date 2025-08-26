<?php
declare(strict_types=1);

namespace App\Controller;

class FeedController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Paginator');
    }

    public function index()
    {
        $postsTable = $this->fetchTable('Posts');
        
        // Configure pagination settings
        $this->paginate = [
            'limit' => 20,
            'order' => [
                'Posts.created' => 'DESC'
            ],
            'contain' => [
                'Users' => [
                    'fields' => ['id', 'username', 'display_name']
                ],
                'Comments' => [
                    'Users' => [
                        'fields' => ['id', 'username', 'display_name']
                    ],
                    'limit' => 3,
                    'order' => ['Comments.created' => 'DESC']
                ]
            ]
        ];
        
        // Get paginated posts with eager-loaded authors and recent comments
        $posts = $this->paginate($postsTable);

        $this->set(compact('posts'));
    }
}