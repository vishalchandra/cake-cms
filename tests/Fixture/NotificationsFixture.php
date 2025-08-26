<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * NotificationsFixture
 */
class NotificationsFixture extends TestFixture
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
                'recipient_user_id' => 1,
                'actor_user_id' => 1,
                'type' => 'Lorem ipsum dolor sit amet',
                'entity_type' => 'Lorem ipsum dolor ',
                'entity_id' => 1,
                'is_read' => 1,
                'created' => '2025-08-26 22:29:59',
            ],
        ];
        parent::init();
    }
}
