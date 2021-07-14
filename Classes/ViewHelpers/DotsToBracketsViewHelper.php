<?php
namespace Cjel\TemplatesAide\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class DotsToBracketsViewHelper extends AbstractViewHelper
{
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $parts = explode('.', $renderChildrenClosure());
        $_  = '';
        foreach ($parts as $part) {
            $_ .= '[';
            if (substr($part, 0, 1) === '#') {
                $_ .= substr($part, 1);
            } else {
                $_ .= '\'';
                $_ .= $part;
                $_ .= '\'';
            }
            $_ .= ']';
        }
        return $_;
    }
}
