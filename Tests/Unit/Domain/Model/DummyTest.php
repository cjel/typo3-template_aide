<?php
namespace Cjel\TemplatesAide\Tests\Unit\Domain\Model;

/**
 * Test case.
 *
 * @author Philipp Dieter <philippdieter@attic-media.net>
 */
class DummyTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Cjel\TemplatesAide\Domain\Model\Dummy
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \Cjel\TemplatesAide\Domain\Model\Dummy();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function dummyTestToNotLeaveThisFileEmpty()
    {
        self::markTestIncomplete();
    }
}
