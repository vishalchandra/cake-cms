<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\PostsController Test Case
 *
 * @uses \App\Controller\PostsController
 */
class PostsControllerTest extends TestCase
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
    protected $Notifications;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->Users = TableRegistry::getTableLocator()->get('Users');
        $this->Posts = TableRegistry::getTableLocator()->get('Posts');
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
     * Test add method GET - requires authentication
     */
    public function testAddGetRequiresAuth(): void
    {
        $this->get('/posts/add');
        $this->assertRedirect('/login');
    }

    /**
     * Test add method GET for authenticated user
     */
    public function testAddGetAuthenticated(): void
    {
        $user = $this->createVerifiedUser();
        $this->loginUser($user);

        $this->get('/posts/add');
        $this->assertResponseOk();
        $this->assertResponseContains('Create New Post');
    }

    /**
     * Test successful post creation
     */
    public function testAddPostSuccess(): void
    {
        $user = $this->createVerifiedUser();
        $this->loginUser($user);

        $data = [
            'title' => 'Test Post Title',
            'body' => 'This is a test post body with some content.'
        ];

        $this->post('/posts/add', $data);
        $this->assertResponseSuccess();

        // Check post was created
        $post = $this->Posts->find()->where(['title' => 'Test Post Title'])->first();
        $this->assertNotNull($post);
        $this->assertEquals($user->id, $post->user_id);
        $this->assertEquals('This is a test post body with some content.', $post->body);
    }

    /**
     * Test post creation with @mentions
     */
    public function testAddPostWithMentions(): void
    {
        $author = $this->createVerifiedUser([
            'email' => 'author@example.com',
            'username' => 'author'
        ]);
        
        $mentioned = $this->createVerifiedUser([
            'email' => 'mentioned@example.com',
            'username' => 'mentioned'
        ]);
        
        $this->loginUser($author);

        $data = [
            'title' => 'Post with Mentions',
            'body' => 'Hey @mentioned, check this out! Also @nonexistent should be ignored.'
        ];

        $this->post('/posts/add', $data);
        $this->assertResponseSuccess();

        // Check notification was created for mentioned user
        $notification = $this->Notifications->find()
            ->where([
                'recipient_user_id' => $mentioned->id,
                'actor_user_id' => $author->id,
                'type' => 'mention',
                'entity_type' => 'post'
            ])
            ->first();
        
        $this->assertNotNull($notification);
        $this->assertFalse($notification->is_read);
    }

    /**
     * Test post view
     */
    public function testView(): void
    {
        $user = $this->createVerifiedUser();
        
        $post = $this->Posts->newEntity([
            'title' => 'Test Post',
            'body' => 'Test post body',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        $this->get('/posts/' . $post->id);
        $this->assertResponseOk();
        $this->assertResponseContains('Test Post');
        $this->assertResponseContains('Test post body');
    }

    /**
     * Test edit access - only owner can edit
     */
    public function testEditOnlyOwnerCanAccess(): void
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
            'title' => 'Owner Post',
            'body' => 'This belongs to owner',
            'user_id' => $owner->id
        ]);
        $this->Posts->save($post);

        // Other user tries to edit - should be forbidden
        $this->loginUser($other);
        $this->get('/posts/edit/' . $post->id);
        $this->assertResponseCode(403);

        // Owner can edit
        $this->loginUser($owner);
        $this->get('/posts/edit/' . $post->id);
        $this->assertResponseOk();
        $this->assertResponseContains('Owner Post');
    }

    /**
     * Test admin can edit any post
     */
    public function testAdminCanEditAnyPost(): void
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
            'title' => 'User Post',
            'body' => 'This belongs to user',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        // Admin can edit any post
        $this->loginUser($admin);
        $this->get('/posts/edit/' . $post->id);
        $this->assertResponseOk();
        $this->assertResponseContains('User Post');
    }

    /**
     * Test successful post edit
     */
    public function testEditPostSuccess(): void
    {
        $user = $this->createVerifiedUser();
        $this->loginUser($user);
        
        $post = $this->Posts->newEntity([
            'title' => 'Original Title',
            'body' => 'Original body',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        $data = [
            'title' => 'Updated Title',
            'body' => 'Updated body content'
        ];

        $this->post('/posts/edit/' . $post->id, $data);
        $this->assertResponseSuccess();

        // Check post was updated
        $updatedPost = $this->Posts->get($post->id);
        $this->assertEquals('Updated Title', $updatedPost->title);
        $this->assertEquals('Updated body content', $updatedPost->body);
    }

    /**
     * Test delete access - only owner can delete
     */
    public function testDeleteOnlyOwnerCanAccess(): void
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
            'title' => 'Owner Post',
            'body' => 'This belongs to owner',
            'user_id' => $owner->id
        ]);
        $this->Posts->save($post);

        // Other user tries to delete - should be forbidden
        $this->loginUser($other);
        $this->post('/posts/delete/' . $post->id);
        $this->assertResponseCode(403);

        // Post should still exist
        $existingPost = $this->Posts->find()->where(['id' => $post->id])->first();
        $this->assertNotNull($existingPost);

        // Owner can delete
        $this->loginUser($owner);
        $this->post('/posts/delete/' . $post->id);
        $this->assertResponseSuccess();
        $this->assertRedirect('/');

        // Post should be deleted
        $deletedPost = $this->Posts->find()->where(['id' => $post->id])->first();
        $this->assertNull($deletedPost);
    }

    /**
     * Test admin can delete any post
     */
    public function testAdminCanDeleteAnyPost(): void
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
            'title' => 'User Post',
            'body' => 'This belongs to user',
            'user_id' => $user->id
        ]);
        $this->Posts->save($post);

        // Admin can delete any post
        $this->loginUser($admin);
        $this->post('/posts/delete/' . $post->id);
        $this->assertResponseSuccess();

        // Post should be deleted
        $deletedPost = $this->Posts->find()->where(['id' => $post->id])->first();
        $this->assertNull($deletedPost);
    }

    /**
     * Test post validation - title required
     */
    public function testPostValidationTitleRequired(): void
    {
        $user = $this->createVerifiedUser();
        $this->loginUser($user);

        $data = [
            'title' => '',
            'body' => 'Body without title'
        ];

        $this->post('/posts/add', $data);
        $this->assertResponseContains('title');
        $this->assertResponseContains('required');
    }

    /**
     * Test post validation - body required
     */
    public function testPostValidationBodyRequired(): void
    {
        $user = $this->createVerifiedUser();
        $this->loginUser($user);

        $data = [
            'title' => 'Title without body',
            'body' => ''
        ];

        $this->post('/posts/add', $data);
        $this->assertResponseContains('body');
        $this->assertResponseContains('required');
    }

    /**
     * Test post validation - title length limits
     */
    public function testPostValidationTitleLength(): void
    {
        $user = $this->createVerifiedUser();
        $this->loginUser($user);

        // Title too long (over 160 chars)
        $longTitle = str_repeat('a', 161);
        $data = [
            'title' => $longTitle,
            'body' => 'Valid body'
        ];

        $this->post('/posts/add', $data);
        $this->assertResponseContains('title');
        $this->assertResponseContains('160');
    }
}
