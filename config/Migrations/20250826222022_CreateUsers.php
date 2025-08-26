<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateUsers extends BaseMigration
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
        $table = $this->table('users');
        $table->addColumn('email', 'string', [
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('email_verified', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->addColumn('email_verification_token', 'string', [
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('password_hash', 'string', [
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('username', 'string', [
            'limit' => 30,
            'null' => false,
        ]);
        $table->addColumn('display_name', 'string', [
            'limit' => 100,
            'null' => false,
        ]);
        $table->addColumn('role', 'string', [
            'limit' => 20,
            'default' => 'user',
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'update' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);
        
        $table->addIndex(['email'], ['unique' => true]);
        $table->addIndex(['username'], ['unique' => true]);
        
        $table->create();
    }
}
