<?php

/**
 * Контекст выполнения страницы
 */
class PageContext extends AbstractSingleton {

    /**
     * Признак - является ли выполнение ajax-запросом
     */
    private $isAjax;

    /**
     * Ajax?
     * 
     * @return type
     */
    public function isAjax() {
        return $this->isAjax || ServerArrayAdapter::IS_AJAX();
    }

    /**
     * Метод позволяет установить признак выполнения Ajax контекста
     */
    public function setAjaxContext() {
        $this->isAjax = true;
    }

    /** @return PageContext */
    public static function inst() {
        return parent::inst();
    }

}

?>