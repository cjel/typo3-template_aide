<?php
namespace Cjel\TemplatesAide\ViewHelpers;

use \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper as ParentImageViewHelper;

class ImageViewHelper extends ParentImageViewHelper
{
    public function render()
    {
        $result = parent::render();
        $this->tag->removeAttribute('height');
        $this->tag->removeAttribute('width');
        return $this->tag->render();
    }
}
