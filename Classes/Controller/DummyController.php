<?php
namespace Cjel\TemplatesAide\Controller;

/***
 *
 * This file is part of the "Templates Aide" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 Philipp Dieter <philippdieter@attic-media.net>
 *
 ***/

/**
 * DummyController
 */
class DummyController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $dummies = $this->dummyRepository->findAll();
        $this->view->assign('dummies', $dummies);
    }
}
