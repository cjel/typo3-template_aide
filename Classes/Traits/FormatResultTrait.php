<?php
namespace Cjel\TemplatesAide\Traits;

/***
 *
 * This file is part of the "Templates Aide" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2021 Philipp Dieter <philippdieter@attic-media.net>
 *
 ***/

/**
 * ValidationTrait
 */
trait FormatResultTrait
{

    /**
     *
     */
    public function formatResult($result) {
        if ($result == null) {
            $result = [];
        }
        if (!empty($this->errors)) {
            $result = array_merge(
                $result,
                ['errors' => $this->errors]
            );
        }
        if (!empty($this->errorLabels)) {
            $result = array_merge(
                $result,
                ['errorLabels' => $this->errorLabels]
            );
        }
        if (is_array($this->responseStatus)) {
            $result = array_merge(
                $result,
                ['errorType' => reset($this->responseStatus)]
            );
        }
        if ($this->pageType) {
            if (is_array($this->responseStatus)) {
                $this->response->setStatus(
                    array_key_first($this->responseStatus)
                );
            } else {
                $this->response->setStatus($this->responseStatus);
            }
            if ($this->pageType == $this->ajaxPageType) {
                if ($this->environmentService->isEnvironmentInBackendMode()) {
                    header('Content-Type: application/json');
                } else {
                    $GLOBALS['TSFE']->setContentType('application/json');
                }
            }
            unset($result['data']);
            if ($this->redirect) {
                $result['redirect'] = $this->redirect;
            }
            if ($this->reload) {
                $result['reload'] = true;
            }
            return json_encode($result);
        }
        $result = array_merge(
            $result,
            ['cid'           => $this->contentObjectUid],
            ['isValid'       => $this->isValid],
            ['componentMode' => $this->componentMode]
        );
        if (!empty($this->ajaxEnv)) {
            $result = array_merge(
                $result,
                ['ajaxEnv' => $this->ajaxEnv]
            );
        }
        return $result;
    }
}
