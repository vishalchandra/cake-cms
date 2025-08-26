<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PasswordResetsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PasswordResetsTable Test Case
 */
class PasswordResetsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PasswordResetsTable
     */
    protected $PasswordResets;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.PasswordResets',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PasswordResets') ? [] : ['className' => PasswordResetsTable::class];
        $this->PasswordResets = $this->getTableLocator()->get('PasswordResets', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PasswordResets);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PasswordResetsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\PasswordResetsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
