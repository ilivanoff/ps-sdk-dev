<?php

class AP_APAudit extends BaseAdminPage {

    public function title() {
        return 'Аудит';
    }

    public function buildContent() {
        return $this->getFoldedEntity()->fetchTpl();
    }

    /** @return AP_APAudit */
    public static function getInstance() {
        return parent::inst();
    }

}

?>