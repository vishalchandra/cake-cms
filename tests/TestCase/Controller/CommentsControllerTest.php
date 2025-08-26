<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\CommentsController Test Case
 *
 * @uses \App\Controller\CommentsController
 */
class CommentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Posts',
        'app.Comments',
        'app.Notifications',
        'app.PasswordResets'
    ];

    protected $Users;
    protected $Posts;
    protected $Comments;
    protected $Notifications;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->Users = TableRegistry::getTableLocator()->get('Users');
        $this->Posts = TableRegistry::getTableLocator()->get('Posts');
        $this->Comments = TableRegistry::getTableLocator()->get('Comments');
        $this->Notifications = TableRegistry::getTableLocator()->get('Notifications');
    }

    private function createVerifiedUser(array $data = []): object
    {
        $defaultData = [
            'email' => 'user@example.com',
            'username' => 'testuser',
            'display_name' => 'Test User',
            'password' => 'password123',
            'email_verified' => true
        ];
        
        $userData = array_merge($defaultData, $data);
        $user = $this->Users->newEntity($userData);
        return $this->Users->save($user);
    }

    private function loginUser(object $user): void
    {
        $this->session([
            'Auth' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'email_verified' => $user->email_verified,
                'role' => $user->role
            ]
        ]);
    }

    /**
     * Test add comment requires authentication
     */
    public function testAddCommentRequiresAuth(): void
    {
        $user = $this->createVerifiedUser();
        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        $this->post('/posts/' . $post->id . '/comments/add', [
            'body' => 'Test comment'
        ]);
        
        $this->assertRedirect('/login');
    }

    /**
     * Test successful comment addition
     */
    public function testAddCommentSuccess(): void
    {
        $postAuthor = $this->createVerifiedUser([
            'email' => 'author@example.com',
            'username' => 'author'
        ]);
        
        $commenter = $this->createVerifiedUser([
            'email' => 'commenter@example.com',
            'username' => 'commenter'
        ]);

        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $postAuthor->id
        ]);
        $this->Posts->save($post);

        $this->loginUser($commenter);
        
        $this->post('/posts/' . $post->id . '/comments/add', [
            'body' => 'This is a test comment'
        ]);
        
        $this->assertResponseSuccess();
        $this->assertRedirect('/posts/' . $post->id);

        // Check comment was created
        $comment = $this->Comments->find()
            ->where(['post_id' => $post->id, 'user_id' => $commenter->id])
            ->first();
        
        $this->assertNotNull($comment);
        $this->assertEquals('This is a test comment', $comment->body);
    }

    /**
     * Test comment creates notification for post author
     */
    public function testCommentCreatesNotificationForPostAuthor(): void
    {
        $postAuthor = $this->createVerifiedUser([
            'email' => 'author@example.com',
            'username' => 'author'
        ]);
        
        $commenter = $this->createVerifiedUser([
            'email' => 'commenter@example.com',
            'username' => 'commenter'
        ]);

        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $postAuthor->id
        ]);
        $this->Posts->save($post);

        $this->loginUser($commenter);
        
        $this->post('/posts/' . $post->id . '/comments/add', [
            'body' => 'This is a test comment'
        ]);

        // Check notification was created for post author
        $notification = $this->Notifications->find()
            ->where([
                'recipient_user_id' => $postAuthor->id,
                'actor_user_id' => $commenter->id,
                'type' => 'comment_on_your_post',
                'entity_type' => 'comment'
            ])
            ->first();
        
        $this->assertNotNull($notification);
        $this->assertFalse($notification->is_read);
    }

    /**
     * Test comment does not create notification if author comments on own post
     */
    public function testCommentNoSelfNotification(): void
    {
        $user = $this->createVerifiedUser();
        
        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        $this->loginUser($user);
        
        $this->post('/posts/' . $post->id . '/comments/add', [
            'body' => 'Self comment'
        ]);

        // Should not create notification for self
        $notification = $this->Notifications->find()
            ->where([
                'recipient_user_id' => $user->id,
                'type' => 'comment_on_your_post'
            ])
            ->first();
        
        $this->assertNull($notification);
    }

    /**
     * Test comment with @mentions creates notifications
     */
    public function testCommentWithMentionsCreatesNotifications(): void
    {
        $postAuthor = $this->createVerifiedUser([
            'email' => 'author@example.com',
            'username' => 'author'
        ]);
        
        $commenter = $this->createVerifiedUser([
            'email' => 'commenter@example.com',
            'username' => 'commenter'
        ]);
        
        $mentioned = $this->createVerifiedUser([
            'email' => 'mentioned@example.com',
            'username' => 'mentioned'
        ]);

        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $postAuthor->id
        ]);
        $this->Posts->save($post);

        $this->loginUser($commenter);
        
        $this->post('/posts/' . $post->id . '/comments/add', [
            'body' => 'Hey @mentioned, check this comment! Also @nonexistent should be ignored.'
        ]);

        // Check mention notification was created
        $mentionNotification = $this->Notifications->find()
            ->where([
                'recipient_user_id' => $mentioned->id,
                'actor_user_id' => $commenter->id,
                'type' => 'mention',
                'entity_type' => 'comment'
            ])
            ->first();
        
        $this->assertNotNull($mentionNotification);

        // Check post author notification was also created
        $postNotification = $this->Notifications->find()
            ->where([
                'recipient_user_id' => $postAuthor->id,
                'actor_user_id' => $commenter->id,
                'type' => 'comment_on_your_post'
            ])
            ->first();
        
        $this->assertNotNull($postNotification);
    }

    /**
     * Test comment deletion requires ownership
     */
    public function testDeleteCommentRequiresOwnership(): void
    {
        $owner = $this->createVerifiedUser([
            'email' => 'owner@example.com',
            'username' => 'owner'
        ]);
        
        $other = $this->createVerifiedUser([
            'email' => 'other@example.com',
            'username' => 'other'
        ]);

        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $owner->id
        ]);
        $this->Posts->save($post);

        $comment = $this->Comments->newEntity([
            'post_id' => $post->id,
            'user_id' => $owner->id,
            'body' => 'Owner comment'
        ]);
        $this->Comments->save($comment);

        // Other user tries to delete - should be forbidden
        $this->loginUser($other);
        $this->post('/comments/delete/' . $comment->id);
        $this->assertResponseCode(403);

        // Comment should still exist
        $existingComment = $this->Comments->find()->where(['id' => $comment->id])->first();
        $this->assertNotNull($existingComment);
    }

    /**
     * Test owner can delete own comment
     */
    public function testOwnerCanDeleteComment(): void
    {
        $user = $this->createVerifiedUser();

        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        $comment = $this->Comments->newEntity([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'My comment'
        ]);
        $this->Comments->save($comment);

        $this->loginUser($user);
        $this->post('/comments/delete/' . $comment->id);
        $this->assertResponseSuccess();

        // Comment should be deleted
        $deletedComment = $this->Comments->find()->where(['id' => $comment->id])->first();
        $this->assertNull($deletedComment);
    }

    /**
     * Test admin can delete any comment
     */
    public function testAdminCanDeleteAnyComment(): void
    {
        $user = $this->createVerifiedUser([
            'email' => 'user@example.com',
            'username' => 'user'
        ]);
        
        $admin = $this->createVerifiedUser([
            'email' => 'admin@example.com',
            'username' => 'admin',
            'role' => 'admin'
        ]);

        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        $comment = $this->Comments->newEntity([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'User comment'
        ]);
        $this->Comments->save($comment);

        // Admin can delete any comment
        $this->loginUser($admin);
        $this->post('/comments/delete/' . $comment->id);
        $this->assertResponseSuccess();

        // Comment should be deleted
        $deletedComment = $this->Comments->find()->where(['id' => $comment->id])->first();
        $this->assertNull($deletedComment);
    }

    /**
     * Test comment validation - body required
     */
    public function testCommentValidationBodyRequired(): void
    {
        $user = $this->createVerifiedUser();
        
        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        $this->loginUser($user);
        
        $this->post('/posts/' . $post->id . '/comments/add', [
            'body' => ''
        ]);
        
        $this->assertResponseContains('body');
        $this->assertResponseContains('required');
    }

    /**
     * Test comment validation - body length limit
     */
    public function testCommentValidationBodyLength(): void
    {
        $user = $this->createVerifiedUser();
        
        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        $this->loginUser($user);
        
        // Body too long (over 2000 chars)
        $longBody = str_repeat('a', 2001);
        
        $this->post('/posts/' . $post->id . '/comments/add', [
            'body' => $longBody
        ]);
        
        $this->assertResponseContains('body');
        $this->assertResponseContains('2000');
    }
}
