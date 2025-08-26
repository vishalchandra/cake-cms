<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'email' => 'Lorem ipsum dolor sit amet',
                'email_verified' => 1,
                'email_verification_token' => 'Lorem ipsum dolor sit amet',
                'password_hash' => 'Lorem ipsum dolor sit amet',
                'username' => 'Lorem ipsum dolor sit amet',
                'display_name' => 'Lorem ipsum dolor sit amet',
                'role' => 'Lorem ipsum dolor ',
                'created' => '2025-08-26 22:28:32',
                'modified' => '2025-08-26 22:28:32',
            ],
        ];
        parent::init();
    }
}
