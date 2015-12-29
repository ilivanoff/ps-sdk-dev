<?php

/**
 * Хранилище всех WebPage, которые только есть в системе.
 * Перед началом работы мы пробегаемся по всем "построителям страниц" и просим их зарегистрировать
 * страницы, которые они могут строить, в хранилище.
 */
class WebPages {

    /**
     * Возвращает текущую страницу.
     * 
     * @return WebPage
     */
    public static function getCurPage() {
        return WebPagesStorage::inst()->getCurPage();
    }

    public final static function hasCurrentPage() {
        return WebPagesStorage::inst()->hasCurPage();
    }

    /**
     * Проверяет, является ли переданная страница - текущей
     * 
     * @param type $page
     */
    public static function isCurPage($page) {
        return WebPagesStorage::inst()->isCurPage($page);
    }

    public static function reloadCurPage() {
        self::getCurPage()->redirectHere();
    }

    /**
     * Метод определяет и строит текущую Web страницу.
     * Если у пользователя нет к ней доступа, то он будет перенаправлен.
     */
    public static function buildCurrent() {
        if (self::hasCurrentPage()) {
            self::getCurPage()->buildPage();
        } else {
            self::getPage(BASE_PAGE_INDEX)->redirectHere();
        }
    }

    /**
     * Метод получения зарегистрированной страницы
     * 
     * @return WebPage
     */
    public final static function getPage($page, $ensure = true) {
        return WebPagesStorage::inst()->getPage($page, $ensure);
    }

}

?>