<?php
namespace Cjel\TemplatesAide\Utility;

/***
 *
 * This file is part of the "Templates Aide" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Philipp Dieter <philipp.dieter@attic-media.net>
 *
 ***/

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 *
 */
class MailUtility
{
    /**
     * Parse text with a simple "template system" to be used as data for
     * sendMail function
     *
     * @param string $text
     * @param array $markers
     * @return array
     */
    public static function parseContentTemplate(
        $text,
        $markers = [],
        $lineEnd = "\r\n"
    ) {
        $textParts = explode($lineEnd . $lineEnd, $text);
        $result = [];
        foreach ($textParts as $textPart) {
            $type = 'text';
            if (substr($textPart, 0, 2) === '# ') {
                $type = 'headline';
                $textPart = substr($textPart, 2);
            }
            if (substr($textPart, 0, 3) === '## ') {
                $type = 'headline2';
                $textPart = substr($textPart, 3);
            }
            if (substr($textPart, 0, 4) === '### ') {
                $type = 'headline3';
                $textPart = substr($textPart, 4);
            }
            if (substr($textPart, 0, 2) === '- ') {
                $type = 'list';
                $textPart = substr($textPart, 2);
            }
            if (substr($textPart, 0, 3) === '+< ') {
                $type = 'buttonleft';
                $textPart = substr($textPart, 3);
            }
            if (substr($textPart, 0, 3) === '+| ') {
                $type = 'buttoncenter';
                $textPart = substr($textPart, 3);
            }
            if (substr($textPart, 0, 3) === '+> ') {
                $type = 'buttonright';
                $textPart = substr($textPart, 3);
            }
            if (substr($textPart, 0, 2) === '| ') {
                $type = 'table';
                $textPart = substr($textPart, 2);
            }
            if (substr($textPart, 0, 3) === '|| ') {
                $type = 'tableLayout';
                $textPart = substr($textPart, 3);
            }
            if (substr($textPart, 0, 9) === '%subject ') {
                $type = 'subject';
                $textPart = substr($textPart, 9);
            }
            foreach ($markers as $markerName => $markerContent) {
                $textPart = str_replace(
                    '###' . $markerName . '###',
                    $markerContent,
                    $textPart
                );
            }
            switch($type) {
            case 'table':
            case 'tableLayout':
                if (
                    $result[count($result) - 1]['type'] == $type
                    && count($result[count($result) - 1]['data']) == 1
                ) {
                    $result[count($result) - 1]['data'][] = $textPart;
                } else {
                    $result[] = [
                        'type' => $type,
                        'data' => [$textPart],
                    ];
                }
                break;
            case 'list':
                if (
                    $result[count($result) - 1]['type'] == 'list'
                ) {
                    $result[count($result) - 1]['data'][] = $textPart;
                } else {
                    $result[] = [
                        'type' => 'list',
                        'data' => [$textPart],
                    ];
                }
                break;
            default:
                $result[] = [
                    'type' => $type,
                    'data' => $textPart,
                ];
                break;
            }
        }
        return $result;
    }

    /**
     * Gets row from content by given type
     *
     * @param array $content the mail content
     * @param string $type the type to search
     */
    public static function extractByType($content, $type)
    {
        $elementPosition = array_search(
            $type,
            array_column($content, 'type')
        );
        if (!$elementPosition === false) {
            return;
        }
        if (!array_key_exists('data', $content[$elementPosition])) {
            return;
        }
        return $content[$elementPosition]['data'];
    }


    /**
     * tages maildata, builds html and text mails an decides where to send them
     * allows to intercept sender for testing
     *
     * @param string $target email or group identifier
     * @param string $subject mail subject, prefixed by setting in ts
     * @param array $data content for email, gets parsed in different ways
     * @return void
     */
    public static function sendMail(
        $target,
        $sender,
        $subject,
        $data,
        $templateNameHtml = null,
        $templateNameText = null,
        $templatePaths    = null,
        $assetDomain      = null
    ) {
        if (!$templateNameHtml) {
            $templateNameHtml = 'Mails/DefaultHtml';
        }
        if (!$templateNameText) {
            $templateNameText = 'Mails/DefaultText';
        }
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $objectManager->get(
            ConfigurationManagerInterface::class
        );
        $typoScript = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $settings =
            (array)$typoScript['module.']['tx_templatesaide.']['settings.'];
        $settings = GeneralUtility::removeDotsFromTS($settings);
        $htmlView = $objectManager->get(StandaloneView::class);
        $htmlView->setTemplate($templateNameHtml);
        $textView = $objectManager->get(StandaloneView::class);
        if ($templatePaths) {
            $partialRootPaths = $htmlView->getPartialRootPaths();
            $partialRootPaths[] = GeneralUtility::getFileAbsFileName(
                'EXT:templates_aide/Resources/Private/Partials/'
            );
            $htmlView->setTemplateRootPaths(
                $templatePaths->getTemplateRootPaths()
            );
            $htmlView->setPartialRootPaths(
                $partialRootPaths
            );
            $textView->setTemplateRootPaths(
                $templatePaths->getTemplateRootPaths()
            );
            $textView->setPartialRootPaths(
                $partialRootPaths
            );
        } else {
            $htmlView->getTemplatePaths()->fillDefaultsByPackageName(
                'templates_aide'
            );
            $textView->getTemplatePaths()->fillDefaultsByPackageName(
                'templates_aide'
            );
        }
        $textView->setTemplate($templateNameText);
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->setFrom($sender);
        $mail->setSubject($subject);
        $bodydataText = [];
        $bodydataHtml = [];
        foreach ($data as $row) {
            switch($row['type']) {
                case 'text':
                case 'table':
                case 'tableLayout':
                case 'list':
                case 'textbold':
                case 'headline':
                case 'headline2':
                case 'headline3':
                case 'button':
                case 'buttonleft':
                case 'buttoncenter':
                case 'buttonright':
                    $row['data'] = str_replace(
                        "\\\n",
                        '',
                        $row['data']
                    );
                    $htmlRow = $row;
                    $htmlRow['data'] = preg_replace_callback(
                        '/\[.*\]/mU',
                        function($matches) use ($row) {
                            foreach ($matches as $match) {
                                return preg_replace_callback(
                                    '/\[(\S*)\s(.*)\]/mU',
                                    function($matchesInner) use ($row) {
                                        switch($row['type']) {
                                        case 'button':
                                        case 'buttonleft':
                                        case 'buttoncenter':
                                        case 'buttonright':
                                            return '<a style="display: inline-block;" href="'
                                                . $matchesInner[1]
                                                . '">'
                                                . '<span style="display: inline-block; padding: 10px 15px; border-radius: 3px; background-color: red;">'
                                                . 'Button!!!! '
                                                . $matchesInner[2]
                                                . '</span>'
                                                . '</a>';
                                            break;
                                        default:
                                            return '<a href="'
                                                . $matchesInner[1]
                                                . '">'
                                                . $matchesInner[2]
                                                . '</a>';
                                            break;
                                        }
                                    },
                                    $match
                                );
                            }
                        },
                        $htmlRow['data']
                    );
                    $htmlRow['data'] = preg_replace_callback(
                        '/\*.*\*/mU',
                        function($matches) {
                            foreach ($matches as $match) {
                                return '<b>'
                                    . substr($match, 1, -1)
                                    . '</b>';
                            }
                        },
                        $htmlRow['data']
                    );
                    $textRow = $row;
                    $textRow['data'] = preg_replace_callback(
                        '/\[.*\]/mU',
                        function($matches) {
                            foreach ($matches as $match) {
                                return preg_replace_callback(
                                    '/\[(\S*)\s(.*)\]/mU',
                                    function($matchesInner) {
                                        if (
                                            $matchesInner[2] == $matchesInner[1]
                                        ) {
                                            return $matchesInner[1];
                                        }
                                        return $matchesInner[2]
                                            . ': '
                                            . $matchesInner[1];
                                    },
                                    $match
                                );
                            }
                        },
                        $textRow['data']
                    );
                    $bodydataText[] = $textRow;
                    $bodydataHtml[] = $htmlRow;
                    break;
                //case 'button':
                //    $row['data'] = str_replace(
                //        "\\\n",
                //        '',
                //        $row['data']
                //    );
                //    $htmlRow = $row;
                //    $htmlRow['data'] = preg_replace_callback(
                //        '/\[.*\]/mU',
                //        function($matches) {
                //            foreach ($matches as $match) {
                //                $test = preg_replace_callback(
                //                    '/\[(\S*)\s(.*)\]/mU',
                //                    function($matchesInner) {
                //
                //                        return '<a href="'
                //                            . $matchesInner[1]
                //                            . '">'
                //                            . $matchesInner[2]
                //                            . '</a>';
                //                    },
                //                    $match
                //                );
                //                \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
                //                    $test, null, 3, true, false
                //                );
                //                return $test;
                //            }
                //        },
                //        $htmlRow['data']
                //    );
                //    \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
                //        $htmlRow['data'], null, 3, true, false
                //    );
                //    $htmlRow['data'] = preg_replace_callback(
                //        '/\*.*\*/mU',
                //        function($matches) {
                //            foreach ($matches as $match) {
                //                return '<b>'
                //                    . substr($match, 1, -1)
                //                    . '</b>';
                //            }
                //        },
                //        $htmlRow['data']
                //    );
                //    $textRow = $row;
                //    $textRow['data'] = preg_replace_callback(
                //        '/\[.*\]/mU',
                //        function($matches) {
                //            foreach ($matches as $match) {
                //                return preg_replace_callback(
                //                    '/\[(\S*)\s(.*)\]/mU',
                //                    function($matchesInner) {
                //                        if (
                //                            $matchesInner[2] == $matchesInner[1]
                //                        ) {
                //                            return $matchesInner[1];
                //                        }
                //                        return $matchesInner[2]
                //                            . ': '
                //                            . $matchesInner[1];
                //                    },
                //                    $match
                //                );
                //            }
                //        },
                //        $textRow['data']
                //    );
                //    $bodydataText[] = $textRow;
                //    $bodydataHtml[] = $htmlRow;
                //    break;
                case 'attachment':
                    $mail->attach(new \Swift_Attachment(
                        $row['data'][0],
                        $row['data'][1],
                        $row['data'][2]
                    ));
                    break;
                case 'attachmentBase64':
                    $attachmentdata = explode(',', $row['data']);
                    preg_match('/\w*:(.*);\w*/', $attachmentdata[0], $matches);
                    $mimetype = $matches[1];
                    preg_match('/\w*\/(.*);\w*/', $attachmentdata[0], $matches);
                    $fileextension = $matches[1];
                    $mail->attach(new \Swift_Attachment(
                        base64_decode($attachmentdata[1]),
                        'attachment.' . $fileextension,
                        $mimetype
                    ));
                    break;
            }
        }
        $textView->assign('content', $bodydataText);
        $htmlView->assign('content', $bodydataHtml);
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
        //    $bodydataHtml, null, 8, true, false
        //);
        $domain = $settings['mailDomain'];
        if ($assetDomain) {
            $domain = $assetDomain;
        }
        $htmlView->assign('domain', $domain);
        $textBody = $textView->render();
        $htmlBody = $htmlView->render();
        if ($domain) {
            $htmlBody = str_replace(
                'src="/assets',
                'src="' . $domain . '/assets',
                $htmlBody
            );
            $htmlBody = str_replace(
                'src="/public',
                'src="' . $domain . '/public',
                $htmlBody
            );
        }
        if (version_compare(TYPO3_branch, '10.0', '>=')) {
            $mail->html($htmlBody);
            $mail->text($textBody);
        } else {
            $mail->setBody($textBody);
            $mail->addPart($htmlBody, 'text/html');
        }
        $recipients = explode(
            ',',
            $target
        );
        if ($GLOBALS['TYPO3_CONF_VARS']['MAIL']['intercept_to']) {
            $subjectOrig = $mail->getSubject();
            $recipientsIntercecpted = explode(
                ',',
                $GLOBALS['TYPO3_CONF_VARS']['MAIL']['intercept_to']
            );
            foreach ($recipientsIntercecpted as $recipientIntercepted) {
                foreach ($recipients as $recipient) {
                    $mail->setSubject(
                        $subjectOrig . ' [ORIG-TO: ' . trim($recipient) . ']'
                    );
                    $mail->setTo(trim($recipientIntercepted));
                    $mail->send();
                }
            }
        } else {
            foreach ($recipients as $recipient) {
                $mail->setTo(trim($recipient));
                $mail->send();
            }
        }
    }
}
