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
        $templateNameText = null
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
        $htmlView->getTemplatePaths()->fillDefaultsByPackageName(
            'templates_aide'
        );
        $htmlView->setTemplate($templateNameHtml);
        $textView = $objectManager->get(StandaloneView::class);
        $textView->getTemplatePaths()->fillDefaultsByPackageName(
            'templates_aide'
        );
        $textView->setTemplate($templateNameText);
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->setFrom($sender);
        $mail->setSubject($subject);
        $bodydataText = [];
        $bodydataHtml = [];
        foreach ($data as $row) {
            switch($row['type']) {
                case 'text':
                case 'headline':
                    $htmlRow = $row;
                    $htmlRow['data'] = preg_replace_callback(
                        '/\[.*\]/mU',
                        function($matches) {

                            foreach ($matches as $match) {
                                return preg_replace_callback(
                                    '/\[(\S*)\s(.*)\]/mU',
                                    function($matchesInner) {
                                        return '<a href="'
                                            . $matchesInner[1]
                                            . '">'
                                            . $matchesInner[2]
                                            . '</a>';
                                    },
                                    $match
                                );
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
                case 'button':
                case 'buttons':
                    $htmlRow = $row;
                    //$htmlRow['targets'] = preg_replace_callback(
                    //    '/\[.*\]/mU',
                    //    function($matches) {
                    //        foreach ($matches as $match) {
                    //            return preg_replace_callback(
                    //                '/\[(\S*)\s(.*)\]/mU',
                    //                function($matchesInner) {
                    //                    return $matchesInner;
                    //                    //return '<a href="'
                    //                    //    . $matchesInner[1]
                    //                    //    . '">'
                    //                    //    . $matchesInner[2]
                    //                    //    . '</a>';
                    //                },
                    //                $match
                    //            );
                    //        }
                    //    },
                    //    $htmlRow['targets']
                    //);
                    $textRow = $row;
                    //$textRow['targets'] = preg_replace_callback(
                    //    '/\[.*\]/mU',
                    //    function($matches) {
                    //        foreach ($matches as $match) {
                    //            return preg_replace_callback(
                    //                '/\[(\S*)\s(.*)\]/mU',
                    //                function($matchesInner) {
                    //                    return $matchesInner;
                    //                    //return $matchesInner[2]
                    //                    //    . ': '
                    //                    //    . $matchesInner[1];
                    //                },
                    //                $match
                    //            );
                    //        }
                    //    },
                    //    $textRow['targets']
                    //);
                    $bodydataText[] = $textRow;
                    $bodydataHtml[] = $htmlRow;
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
        $domain = $settings['mailDomain'];
        $htmlView->assign('domain', $domain);
        $textBody = $textView->render();
        $htmlBody = $htmlView->render();
        $mail->setBody($textBody);
        $mail->addPart($htmlBody, 'text/html');
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
