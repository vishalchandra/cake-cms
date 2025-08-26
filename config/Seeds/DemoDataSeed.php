<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * DemoData seed.
 */
class DemoDataSeed extends BaseSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/migrations/4/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Clear existing demo data first
        $this->execute('DELETE FROM comments WHERE user_id IN (SELECT id FROM users WHERE email IN ("alice@example.com", "bob@example.com", "carol@example.com"))');
        $this->execute('DELETE FROM posts WHERE user_id IN (SELECT id FROM users WHERE email IN ("alice@example.com", "bob@example.com", "carol@example.com"))');
        $this->execute('DELETE FROM users WHERE email IN ("alice@example.com", "bob@example.com", "carol@example.com")');
        
        // Create 3 demo users: alice, bob, carol
        $users = [
            [
                'email' => 'alice@example.com',
                'email_verified' => true,
                'email_verification_token' => null,
                'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'username' => 'alice',
                'display_name' => 'Alice Johnson',
                'role' => 'user',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'email' => 'bob@example.com',
                'email_verified' => true,
                'email_verification_token' => null,
                'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'username' => 'bob',
                'display_name' => 'Bob Smith',
                'role' => 'user',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'email' => 'carol@example.com',
                'email_verified' => true,
                'email_verification_token' => null,
                'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'username' => 'carol',
                'display_name' => 'Carol Davis',
                'role' => 'admin',
                'created' => $now,
                'modified' => $now,
            ],
        ];

        $usersTable = $this->table('users');
        $usersTable->insert($users)->save();

        // Get the user IDs after insertion
        $aliceId = $this->fetchRow('SELECT id FROM users WHERE email = "alice@example.com"')['id'];
        $bobId = $this->fetchRow('SELECT id FROM users WHERE email = "bob@example.com"')['id'];
        $carolId = $this->fetchRow('SELECT id FROM users WHERE email = "carol@example.com"')['id'];

        // Create 5 demo posts
        $posts = [
            [
                'user_id' => $aliceId,
                'title' => 'Welcome to our mini CMS',
                'body' => 'This is the first post in our new CMS system. Hello @bob and @carol!',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'user_id' => $bobId,
                'title' => 'Getting Started with CakePHP',
                'body' => 'I\'ve been exploring CakePHP and it\'s amazing! @alice you should check out the documentation.',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'user_id' => $carolId,
                'title' => 'Admin Features Coming Soon',
                'body' => 'As an admin, I\'m working on some new features. Stay tuned @alice and @bob!',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'user_id' => $aliceId,
                'title' => 'Community Guidelines',
                'body' => 'Let\'s keep our discussions respectful and helpful for everyone.',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'user_id' => $bobId,
                'title' => 'Tips and Tricks',
                'body' => 'Share your favorite development tips here! @carol what are your thoughts?',
                'created' => $now,
                'modified' => $now,
            ],
        ];

        $postsTable = $this->table('posts');
        $postsTable->insert($posts)->save();

        // Get the post IDs after insertion
        $postIds = $this->fetchAll('SELECT id FROM posts ORDER BY id');

        // Create 8 demo comments
        $comments = [
            [
                'post_id' => $postIds[0]['id'],
                'user_id' => $bobId,
                'body' => 'Great to see this CMS up and running! Thanks @alice for setting this up.',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'post_id' => $postIds[0]['id'],
                'user_id' => $carolId,
                'body' => 'Looking forward to contributing more content!',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'post_id' => $postIds[1]['id'],
                'user_id' => $aliceId,
                'body' => 'Thanks for the recommendation @bob! The docs are really comprehensive.',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'post_id' => $postIds[1]['id'],
                'user_id' => $carolId,
                'body' => 'CakePHP has been great for rapid development.',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'post_id' => $postIds[2]['id'],
                'user_id' => $aliceId,
                'body' => 'Excited to see what new features you\'ll add @carol!',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'post_id' => $postIds[2]['id'],
                'user_id' => $bobId,
                'body' => 'Admin features sound interesting. Keep us posted!',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'post_id' => $postIds[3]['id'],
                'user_id' => $bobId,
                'body' => 'Absolutely agree with these guidelines @alice.',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'post_id' => $postIds[4]['id'],
                'user_id' => $carolId,
                'body' => 'One tip: always test your code thoroughly! What do you think @bob?',
                'created' => $now,
                'modified' => $now,
            ],
        ];

        $commentsTable = $this->table('comments');
        $commentsTable->insert($comments)->save();
    }
}
