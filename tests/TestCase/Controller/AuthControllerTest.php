<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

/**
 * App\Controller\AuthController Test Case
 *
 * @uses \App\Controller\AuthController
 */
class AuthControllerTest extends TestCase
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

    public function setUp(): void
    {
        parent::setUp();
        $this->Users = TableRegistry::getTableLocator()->get('Users');
    }

    /**
     * Test register method
     */
    public function testRegisterGet(): void
    {
        $this->get('/register');
        $this->assertResponseOk();
        $this->assertResponseContains('Register');
    }

    /**
     * Test successful registration
     */
    public function testRegisterPostSuccess(): void
    {
        $data = [
            'email' => 'newuser@example.com',
            'username' => 'newuser',
            'display_name' => 'New User',
            'password' => 'password123'
        ];

        $this->post('/register', $data);
        $this->assertResponseSuccess();
        $this->assertRedirect('/login');

        // Check user was created but not verified
        $user = $this->Users->find()->where(['email' => 'newuser@example.com'])->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->email_verified);
        $this->assertNotEmpty($user->email_verification_token);
        $this->assertEquals('newuser', $user->username);
        $this->assertEquals('New User', $user->display_name);
        $this->assertEquals('user', $user->role);
    }

    /**
     * Test registration with duplicate email
     */
    public function testRegisterDuplicateEmail(): void
    {
        // Create existing user
        $existingUser = $this->Users->newEntity([
            'email' => 'existing@example.com',
            'username' => 'existing',
            'display_name' => 'Existing User',
            'password' => 'password123',
            'email_verified' => true
        ]);
        $this->Users->save($existingUser);

        $data = [
            'email' => 'existing@example.com',
            'username' => 'newuser',
            'display_name' => 'New User',
            'password' => 'password123'
        ];

        $this->post('/register', $data);
        $this->assertResponseContains('email');
        $this->assertResponseContains('already');
    }

    /**
     * Test registration with duplicate username
     */
    public function testRegisterDuplicateUsername(): void
    {
        // Create existing user
        $existingUser = $this->Users->newEntity([
            'email' => 'existing@example.com',
            'username' => 'existing',
            'display_name' => 'Existing User',
            'password' => 'password123',
            'email_verified' => true
        ]);
        $this->Users->save($existingUser);

        $data = [
            'email' => 'newuser@example.com',
            'username' => 'existing',
            'display_name' => 'New User',
            'password' => 'password123'
        ];

        $this->post('/register', $data);
        $this->assertResponseContains('username');
        $this->assertResponseContains('already');
    }

    /**
     * Test email verification
     */
    public function testEmailVerification(): void
    {
        // Create unverified user
        $user = $this->Users->newEntity([
            'email' => 'test@example.com',
            'username' => 'testuser',
            'display_name' => 'Test User',
            'password' => 'password123',
            'email_verified' => false,
            'email_verification_token' => 'test-token-123'
        ]);
        $this->Users->save($user);

        // Verify email with token
        $this->get('/verify?token=test-token-123');
        $this->assertResponseSuccess();
        $this->assertRedirect('/login');

        // Check user is now verified
        $user = $this->Users->get($user->id);
        $this->assertTrue($user->email_verified);
        $this->assertNull($user->email_verification_token);
    }

    /**
     * Test login for verified user
     */
    public function testLoginVerifiedUser(): void
    {
        // Create verified user
        $user = $this->Users->newEntity([
            'email' => 'verified@example.com',
            'username' => 'verified',
            'display_name' => 'Verified User',
            'password' => 'password123',
            'email_verified' => true
        ]);
        $this->Users->save($user);

        $this->post('/login', [
            'email' => 'verified@example.com',
            'password' => 'password123'
        ]);

        $this->assertResponseSuccess();
        $this->assertRedirect('/');
    }

    /**
     * Test login for unverified user should fail
     */
    public function testLoginUnverifiedUserFails(): void
    {
        // Create unverified user
        $user = $this->Users->newEntity([
            'email' => 'unverified@example.com',
            'username' => 'unverified',
            'display_name' => 'Unverified User',
            'password' => 'password123',
            'email_verified' => false,
            'email_verification_token' => 'token123'
        ]);
        $this->Users->save($user);

        $this->post('/login', [
            'email' => 'unverified@example.com',
            'password' => 'password123'
        ]);

        // Should redirect back to login with error message
        $this->assertRedirect('/login');
        $this->assertFlashMessage('Please verify your email address before continuing.');
    }

    /**
     * Test complete registration flow: register → verify → login
     */
    public function testCompleteRegistrationFlow(): void
    {
        // Step 1: Register
        $data = [
            'email' => 'flow@example.com',
            'username' => 'flowuser',
            'display_name' => 'Flow User',
            'password' => 'password123'
        ];

        $this->post('/register', $data);
        $this->assertResponseSuccess();
        $this->assertRedirect('/login');

        // Get the created user and verification token
        $user = $this->Users->find()->where(['email' => 'flow@example.com'])->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->email_verified);
        $token = $user->email_verification_token;
        $this->assertNotEmpty($token);

        // Step 2: Verify email
        $this->get('/verify?token=' . $token);
        $this->assertResponseSuccess();
        $this->assertRedirect('/login');

        // Check user is verified
        $user = $this->Users->get($user->id);
        $this->assertTrue($user->email_verified);

        // Step 3: Login
        $this->post('/login', [
            'email' => 'flow@example.com',
            'password' => 'password123'
        ]);

        $this->assertResponseSuccess();
        $this->assertRedirect('/');
    }

    /**
     * Test password reset flow
     */
    public function testPasswordResetFlow(): void
    {
        // Create verified user
        $user = $this->Users->newEntity([
            'email' => 'reset@example.com',
            'username' => 'resetuser',
            'display_name' => 'Reset User',
            'password' => 'oldpassword123',
            'email_verified' => true
        ]);
        $this->Users->save($user);

        // Request password reset
        $this->post('/password/forgot', [
            'email' => 'reset@example.com'
        ]);
        $this->assertResponseSuccess();

        // Get the reset token
        $passwordResets = TableRegistry::getTableLocator()->get('PasswordResets');
        $reset = $passwordResets->find()->where(['user_id' => $user->id])->first();
        $this->assertNotNull($reset);

        // Use reset token to reset password
        $this->post('/password/reset?token=' . $reset->token, [
            'password' => 'newpassword123',
            'password_confirm' => 'newpassword123'
        ]);
        $this->assertResponseSuccess();
        $this->assertRedirect('/login');

        // Test login with new password
        $this->post('/login', [
            'email' => 'reset@example.com',
            'password' => 'newpassword123'
        ]);
        $this->assertResponseSuccess();
        $this->assertRedirect('/');
    }

    /**
     * Test logout
     */
    public function testLogout(): void
    {
        // Create and login user
        $user = $this->Users->newEntity([
            'email' => 'logout@example.com',
            'username' => 'logoutuser',
            'display_name' => 'Logout User',
            'password' => 'password123',
            'email_verified' => true
        ]);
        $this->Users->save($user);

        // Login first
        $this->post('/login', [
            'email' => 'logout@example.com',
            'password' => 'password123'
        ]);

        // Then logout
        $this->post('/logout');
        $this->assertResponseSuccess();
        $this->assertRedirect('/');
    }
}