<?php

class AP_APFoldingsInfo extends BaseAdminPage {

    public function title() {
        return 'Информация о фолдингах';
    }

    public function buildContent() {
        $PARAMS['foldings'] = FoldedStorageInsts::listFoldings();
        return $this->foldedEntity->fetchTpl($PARAMS);
    }

}

?>