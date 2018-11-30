
plugin.tx_templatesaide_dummy {
    view {
        # cat=plugin.tx_templatesaide_dummy/file; type=string; label=Path to template root (FE)
        templateRootPath = EXT:templates_aide/Resources/Private/Templates/
        # cat=plugin.tx_templatesaide_dummy/file; type=string; label=Path to template partials (FE)
        partialRootPath = EXT:templates_aide/Resources/Private/Partials/
        # cat=plugin.tx_templatesaide_dummy/file; type=string; label=Path to template layouts (FE)
        layoutRootPath = EXT:templates_aide/Resources/Private/Layouts/
    }
    persistence {
        # cat=plugin.tx_templatesaide_dummy//a; type=string; label=Default storage PID
        storagePid =
    }
}

## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder