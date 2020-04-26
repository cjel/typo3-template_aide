<?php
namespace Cjel\TemplatesAide\Tests\Unit\Controller;

/**
 * Test case.
 *
 * @author Philipp Dieter <philippdieter@attic-media.net>
 */
class DummyControllerTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Cjel\TemplatesAide\Controller\DummyController
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(\Cjel\TemplatesAide\Controller\DummyController::class)
            ->setMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function listActionFetchesAllDummiesFromRepositoryAndAssignsThemToView()
    {

        $allDummies = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dummyRepository = $this->getMockBuilder(\::class)
            ->setMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $dummyRepository->expects(self::once())->method('findAll')->will(self::returnValue($allDummies));
        $this->inject($this->subject, 'dummyRepository', $dummyRepository);

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('dummies', $allDummies);
        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }
}
