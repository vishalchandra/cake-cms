<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateNotifications extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('notifications');
        $table->addColumn('recipient_user_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('actor_user_id', 'integer', [
            'null' => true,
        ]);
        $table->addColumn('type', 'string', [
            'limit' => 50,
            'null' => false,
        ]);
        $table->addColumn('entity_type', 'string', [
            'limit' => 20,
            'null' => false,
        ]);
        $table->addColumn('entity_id', 'biginteger', [
            'null' => false,
        ]);
        $table->addColumn('is_read', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);
        
        $table->addForeignKey('recipient_user_id', 'users', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('actor_user_id', 'users', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);
        $table->addIndex(['recipient_user_id', 'created'], ['name' => 'idx_notifications_recipient_created']);
        $table->addIndex(['recipient_user_id', 'is_read'], ['name' => 'idx_notifications_unread']);
        
        $table->create();
    }
}
