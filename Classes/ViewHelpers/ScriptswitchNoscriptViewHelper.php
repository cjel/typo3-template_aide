<?php
namespace Cjel\TemplatesAide\ViewHelpers;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

class ScriptswitchNoscriptViewHelper extends AbstractViewHelper {

    /**
      * As this ViewHelper renders HTML, the output must not be escaped.
      *
      * @var bool
      */
    protected $escapeOutput = false;

    /**
     * @return string HTML Content
     */
    public function render()
    {

        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
        //    $GLOBALS['TSFE']->fe_user->getKey('ses', 'scriptstate')
        //);

        $scriptstate = $GLOBALS['TSFE']->fe_user->getKey('ses', 'scriptstate');

        if ($scriptstate) {
            $_  = '<noscript inline-template>';
            $_ .=   '<div class="here">';
            $_ .=     '<iframe src="/script/disabled">';
            $_ .=     '</iframe>';
            $_ .=     '<meta http-equiv="refresh" content="1" />';
            $_ .=   '</div>';
            $_ .= '</noscript>';
        } else {
            $_ = '
                <script type="application/javascript">
                    var url = "/script/enabled"
                    var xmlhttp;
                    xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function(){}
                    xmlhttp.open("GET", url, true);
                    xmlhttp.send();
                </script>
            ';
        }

        return $_;
    }
}
