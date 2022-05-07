<?php
namespace Cjel\TemplatesAide\ViewHelpers;

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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Imaging\ImageMagickFile;

/**
 *
 */
class ImageAppendViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * ImageService
     *
     * @var ImageService
     */
    protected $imageService;

    /**
     * @param
     */
    public function injectImageService(
        ImageService $imageService
    ) {
        $this->imageService = $imageService;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('images', 'array', '');
    }

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     *
     * @throws Exception
     * @return string Rendered tag
     */
    public function render()
    {
        foreach ($this->arguments['images'] as $image) {
            $imagePath = $image->getForLocalProcessing(false);
            //$image = $this->imageService->getImage('', $imageArgument, true);
            //$image = $this->imageService->getImageUri($image);

            $imageMagickFile = ImageMagickFile::fromFilePath($imagePath, 0);

            \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
                $imageMagickFile, null, 3
            );



        }

    }

}
