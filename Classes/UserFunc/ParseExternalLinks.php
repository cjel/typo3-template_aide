<?php
namespace Cjel\TemplatesAide\UserFunc;

/**
 * This file is part of the "Site Templates" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Philipp Dieter <philipp@glanzstueck.agency>, Glanzstück GmbH
 */

use DiDom\Document;
use DiDom\Element;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ParseExternalLinks
{
    /**
     * @return string
     */
    public function render($content, $conf = [])
    {
        $domDocument = new \DOMDocument();
        $testdocument = $domDocument->loadXML($content);
        if ($testdocument === false) {
            return $content;
        }
        $document = new Document($content);
        $a = $document->find('a')[0];
        $href = $a->getAttribute('href');
        if (substr($href, 0, 7) != "http://"
            && substr($href, 0, 8) != "https://"
        ) {
            return $content;
        }
        $parsedHref = parse_url($href);
        if ($_SERVER['SERVER_NAME'] == $parsedHref['host']) {
            return $content;
        }
        if ($a->getAttribute('target') == '_blank') {
            $a->setAttribute('rel', 'noopener');
        }
        $class = $a->getAttribute('class');
        if ($class) {
            $class .= ' link link-external';
        } else {
            $class = 'link link-external';
        }
        $a->setAttribute('class', $class);
        if ($conf['linkText']) {
            $screenreaderHint = new Element('span', $conf['linkText'] . ' ');
            $screenreaderHint->setAttribute('class', 'link-external-sr-only');
            $a->prependChild($screenreaderHint);
        }
        if ($conf['iconFile']) {
            $iconDataFile = GeneralUtility::getFileAbsFileName(
                $conf['iconFile']
            );
            $iconData = file_get_contents($iconDataFile);
            $icon = new Element('span');
            $icon->setInnerHtml($iconData);
            $icon->setAttribute('class', 'icon-link-external');
            if ($iconPosition == 'start') {
                $a->prepentChild($icon);
            } else {
                $a->appendChild($icon);
            }
        }
        $innerHtml = $a->innerHtml();
        $a->setInnerHtml(
            '<span class="link-external-inner">'
            . $innerHtml
            . '</span>'
        );
        return $document->find('body')[0]->innerHtml();
    }
}
