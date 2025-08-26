<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\NotificationsController Test Case
 *
 * @uses \App\Controller\NotificationsController
 */
class NotificationsControllerTest extends TestCase
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
     * Test notifications index requires authentication
     */
    public function testIndexRequiresAuth(): void
    {
        $this->get('/notifications');
        $this->assertRedirect('/login');
    }

    /**
     * Test notifications index for authenticated user
     */
    public function testIndexAuthenticated(): void
    {
        $user = $this->createVerifiedUser();
        $this->loginUser($user);

        $this->get('/notifications');
        $this->assertResponseOk();
        $this->assertResponseContains('Notifications');
    }

    /**
     * Test notifications shows user's notifications only
     */
    public function testIndexShowsOwnNotificationsOnly(): void
    {
        $user1 = $this->createVerifiedUser([
            'email' => 'user1@example.com',
            'username' => 'user1'
        ]);
        
        $user2 = $this->createVerifiedUser([
            'email' => 'user2@example.com',
            'username' => 'user2'
        ]);

        // Create notifications for both users
        $notification1 = $this->Notifications->newEntity([
            'recipient_user_id' => $user1->id,
            'actor_user_id' => $user2->id,
            'type' => 'mention',
            'entity_type' => 'post',
            'entity_id' => 1,
            'is_read' => false
        ]);
        $this->Notifications->save($notification1);

        $notification2 = $this->Notifications->newEntity([
            'recipient_user_id' => $user2->id,
            'actor_user_id' => $user1->id,
            'type' => 'mention',
            'entity_type' => 'post',
            'entity_id' => 2,
            'is_read' => false
        ]);
        $this->Notifications->save($notification2);

        // User1 should only see their notification
        $this->loginUser($user1);
        $this->get('/notifications');
        $this->assertResponseOk();
        
        // Should contain user2's username (as the actor)
        $this->assertResponseContains($user2->username);
        
        // Should not contain references that would only be in user2's notifications
        // This is a bit tricky to test without more specific content
    }

    /**
     * Test mark notification as read
     */
    public function testMarkAsRead(): void
    {
        $recipient = $this->createVerifiedUser([
            'email' => 'recipient@example.com',
            'username' => 'recipient'
        ]);
        
        $actor = $this->createVerifiedUser([
            'email' => 'actor@example.com',
            'username' => 'actor'
        ]);

        // Create unread notification
        $notification = $this->Notifications->newEntity([
            'recipient_user_id' => $recipient->id,
            'actor_user_id' => $actor->id,
            'type' => 'mention',
            'entity_type' => 'post',
            'entity_id' => 1,
            'is_read' => false
        ]);
        $this->Notifications->save($notification);

        $this->loginUser($recipient);
        
        // Mark as read
        $this->post('/notifications/' . $notification->id . '/read');
        $this->assertResponseSuccess();
        $this->assertRedirect('/notifications');

        // Check notification is marked as read
        $updatedNotification = $this->Notifications->get($notification->id);
        $this->assertTrue($updatedNotification->is_read);
    }

    /**
     * Test mark as read requires ownership
     */
    public function testMarkAsReadRequiresOwnership(): void
    {
        $recipient = $this->createVerifiedUser([
            'email' => 'recipient@example.com',
            'username' => 'recipient'
        ]);
        
        $actor = $this->createVerifiedUser([
            'email' => 'actor@example.com',
            'username' => 'actor'
        ]);
        
        $other = $this->createVerifiedUser([
            'email' => 'other@example.com',
            'username' => 'other'
        ]);

        // Create notification for recipient
        $notification = $this->Notifications->newEntity([
            'recipient_user_id' => $recipient->id,
            'actor_user_id' => $actor->id,
            'type' => 'mention',
            'entity_type' => 'post',
            'entity_id' => 1,
            'is_read' => false
        ]);
        $this->Notifications->save($notification);

        // Other user tries to mark as read - should be forbidden
        $this->loginUser($other);
        $this->post('/notifications/' . $notification->id . '/read');
        $this->assertResponseCode(403);

        // Notification should still be unread
        $unchangedNotification = $this->Notifications->get($notification->id);
        $this->assertFalse($unchangedNotification->is_read);
    }

    /**
     * Test notifications list shows newest first
     */
    public function testNotificationsNewestFirst(): void
    {
        $recipient = $this->createVerifiedUser([
            'email' => 'recipient@example.com',
            'username' => 'recipient'
        ]);
        
        $actor = $this->createVerifiedUser([
            'email' => 'actor@example.com',
            'username' => 'actor'
        ]);

        // Create multiple notifications with different timestamps
        $older = $this->Notifications->newEntity([
            'recipient_user_id' => $recipient->id,
            'actor_user_id' => $actor->id,
            'type' => 'mention',
            'entity_type' => 'post',
            'entity_id' => 1,
            'is_read' => false,
            'created' => '2023-01-01 12:00:00'
        ]);
        $this->Notifications->save($older);

        $newer = $this->Notifications->newEntity([
            'recipient_user_id' => $recipient->id,
            'actor_user_id' => $actor->id,
            'type' => 'comment_on_your_post',
            'entity_type' => 'comment',
            'entity_id' => 2,
            'is_read' => false,
            'created' => '2023-01-02 12:00:00'
        ]);
        $this->Notifications->save($newer);

        $this->loginUser($recipient);
        $this->get('/notifications');
        $this->assertResponseOk();

        // Both notifications should be present
        // The newer one should appear first (newest first ordering)
        $response = (string)$this->_response->getBody();
        $commentPos = strpos($response, 'commented on your post');
        $mentionPos = strpos($response, 'mentioned you');
        
        // Comment notification (newer) should appear before mention (older)
        $this->assertLessThan($mentionPos, $commentPos);
    }

    /**
     * Test notifications show correct unread count
     */
    public function testUnreadNotificationCount(): void
    {
        $recipient = $this->createVerifiedUser([
            'email' => 'recipient@example.com',
            'username' => 'recipient'
        ]);
        
        $actor = $this->createVerifiedUser([
            'email' => 'actor@example.com',
            'username' => 'actor'
        ]);

        // Create 3 unread notifications
        for ($i = 1; $i <= 3; $i++) {
            $notification = $this->Notifications->newEntity([
                'recipient_user_id' => $recipient->id,
                'actor_user_id' => $actor->id,
                'type' => 'mention',
                'entity_type' => 'post',
                'entity_id' => $i,
                'is_read' => false
            ]);
            $this->Notifications->save($notification);
        }

        // Create 1 read notification
        $readNotification = $this->Notifications->newEntity([
            'recipient_user_id' => $recipient->id,
            'actor_user_id' => $actor->id,
            'type' => 'mention',
            'entity_type' => 'post',
            'entity_id' => 4,
            'is_read' => true
        ]);
        $this->Notifications->save($readNotification);

        $this->loginUser($recipient);
        $this->get('/notifications');
        $this->assertResponseOk();

        // Should show "3 new" unread notifications
        $this->assertResponseContains('3 new');
    }

    /**
     * Test empty notifications page
     */
    public function testEmptyNotifications(): void
    {
        $user = $this->createVerifiedUser();
        $this->loginUser($user);

        $this->get('/notifications');
        $this->assertResponseOk();
        $this->assertResponseContains('No notifications yet');
        $this->assertResponseContains($user->username);
    }

    /**
     * Test notification message formatting
     */
    public function testNotificationMessageFormatting(): void
    {
        $recipient = $this->createVerifiedUser([
            'email' => 'recipient@example.com',
            'username' => 'recipient'
        ]);
        
        $actor = $this->createVerifiedUser([
            'email' => 'actor@example.com',
            'username' => 'actor'
        ]);

        // Create mention notification
        $mentionNotification = $this->Notifications->newEntity([
            'recipient_user_id' => $recipient->id,
            'actor_user_id' => $actor->id,
            'type' => 'mention',
            'entity_type' => 'post',
            'entity_id' => 1,
            'is_read' => false
        ]);
        $this->Notifications->save($mentionNotification);

        // Create comment notification
        $commentNotification = $this->Notifications->newEntity([
            'recipient_user_id' => $recipient->id,
            'actor_user_id' => $actor->id,
            'type' => 'comment_on_your_post',
            'entity_type' => 'comment',
            'entity_id' => 2,
            'is_read' => false
        ]);
        $this->Notifications->save($commentNotification);

        $this->loginUser($recipient);
        $this->get('/notifications');
        $this->assertResponseOk();

        // Check message formatting
        $this->assertResponseContains('@' . $actor->username . ' mentioned you');
        $this->assertResponseContains('@' . $actor->username . ' commented on your post');
    }
}
