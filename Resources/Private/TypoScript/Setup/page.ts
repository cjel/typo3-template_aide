page = PAGE
page {
    10 = FLUIDTEMPLATE
    10 {
        templateName = Base
        layoutRootPaths {
            0 = EXT:site_templates/Resources/Private/Layouts
        }
        templateRootPaths {
            0 = EXT:site_templates/Resources/Private/Templates
        }
        partialRootPaths {
            0 = EXT:site_templates/Resources/Private/Partials
        }
    }
    includeJSFooter {
        file01 = {$asset.js.site}
        file01.external = 1
        file01.forceOnTop = 1
    }
    includeCSS {
        file01 = {$asset.css.site}
        file01.external = 1
    }
}

["{$asset.css.site}" == ""]
    page.includeCSS.file01 >
[global]

pageContentelement = PAGE
pageContentelement {
    typeNum = 5000
    10 = USER_INT
    10 {
        userFunc = Cjel\TemplatesAide\UserFunc\RenderContentelement->render
    }
    config {
        disableAllHeaderCode = 1
        xhtml_cleaning = 0
        admPanel = 0
        debug = 0
        no_cache = 1
    }
}
