<?php

class AP_APGlobals extends BaseAdminPage {

    public function title() {
        return 'Глобальные настройки';
    }

    public function buildContent() {
        if (PsGlobals::inst()->exists()) {
            $PARAMS['exists'] = true;
            $PARAMS['props'] = PsGlobals::inst()->getProps();
        } else {
            $PARAMS['exists'] = false;
            $PARAMS['path'] = ConfigIni::projectGlobalsFilePath();
        }
        echo $this->getFoldedEntity()->fetchTpl($PARAMS);
    }

    public function getSmartyParams4Resources() {
        return array('MATHJAX_DISABLE' => true);
    }

}

?>