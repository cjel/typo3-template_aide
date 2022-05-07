page.10.stdWrap.parseFunc {
    htmlSanitize = 0
    externalBlocks = a
    externalBlocks {
        a.stdWrap.postUserFunc = Cjel\TemplatesAide\UserFunc\ParseExternalLinks->render
        a.stdWrap.postUserFunc {
            iconFile      = EXT:site_templates/Resources/Private/Partials/Atoms/IconExternalLinkFill.html
            iconPosistion = end
            linkText      = Externer Link zu
        }
    }
}
