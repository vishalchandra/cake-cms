<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateComments extends BaseMigration
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
        $table = $this->table('comments');
        $table->addColumn('post_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('user_id', 'integer', [
            'null' => false,
        ]);
        $table->addColumn('body', 'text', [
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
        
        $table->addForeignKey('post_id', 'posts', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('user_id', 'users', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);
        $table->addIndex(['post_id', 'created'], ['name' => 'idx_comments_post_created']);
        
        $table->create();
    }
}
