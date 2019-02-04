<?php
//declare(strict_types=1);

namespace Cjel\TemplatesAide\FormEngine;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AdditionalHelpText extends AbstractNode
{
    /**
     * Handler for single nodes
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render(): array
    {
        $result = $this->initializeResultArray();
        $text = $this->data['renderData']['fieldInformationOptions']['text'];
        if (substr($text, 0, 4) !== 'LLL:') {
            $result['html'] = $text;
        } else {
            $result['html'] = LocalizationUtility::translate($text, 'templates_aide');
        }
        if (array_key_exists('linebreak', $this->data['renderData']['fieldInformationOptions'])) {
            $result['html'] .= '<br />&nbsp;';
        }
        return $result;
    }
}
