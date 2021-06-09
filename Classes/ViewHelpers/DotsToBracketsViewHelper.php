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
        $_  = '[\'';
        $_ .= implode('\'][\'', $parts);
        $_ .= '\']';
        return $_;
    }
}
