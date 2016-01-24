<?php

class AP_APTables extends BaseAdminPage {

    const MODE_TABLES_LIST = 'list';
    const MODE_TABLE_VIEW = 'view';

    /*
      private $INI_ERRORS;

      protected function _construct() {
      parent::_construct();
      $this->INI_ERRORS = PsDbIniHelper::validateAll();
      }

      public function title() {
      return 'Настройка редактирования таблиц ' . ($this->INI_ERRORS ? PsHtml::spanErr('ОШИБКИ') : '');
      }
     */

    public function title() {
        return 'Редактирование конфигов';
    }

    public static function urlTables() {
        return self::pageUrl();
    }

    public static function urlTableRows($table) {
        $params['table'] = $table instanceof PsTable ? $table->getName() : $table;
        return self::pageUrl($params);
    }

    public function buildContent() {
        $navigation = AdminPageNavigation::inst();

        $PARAMS['mode'] = self::MODE_TABLES_LIST;

        foreach (ConfigIni::getAllowedScopes() as $scope) {
            $PARAMS['data']["$scope.ini"] = ConfigIni::getIniContent($scope);
        }

        $navigation->setCurrent('Настройки');

        echo $this->getFoldedEntity()->fetchTpl($PARAMS);
    }

    public function getSmartyParams4Resources() {
        return array('MATHJAX_DISABLE' => true);
    }

}

?>