<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PasswordResetsFixture
 */
class PasswordResetsFixture extends TestFixture
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
                'user_id' => 1,
                'token' => 'Lorem ipsum dolor sit amet',
                'expires_at' => '2025-08-26 22:30:09',
                'created' => '2025-08-26 22:30:09',
            ],
        ];
        parent::init();
    }
}
