<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreatePasswordResets extends BaseMigration
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
        $table = $this->table('password_resets');
        $table->addColumn('user_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('token', 'string', [
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('expires_at', 'datetime', [
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);
        
        $table->addForeignKey('user_id', 'users', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);
        $table->addIndex(['token'], ['unique' => true]);
        $table->addIndex(['user_id', 'expires_at'], ['name' => 'idx_password_resets_user_expires']);
        
        $table->create();
    }
}
