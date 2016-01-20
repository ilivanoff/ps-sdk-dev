<?php

/**
 * Класс отвечает за определение контекста, в котором выполняется скрипт
 *
 * @author azazello
 */
final class PsContext {

    /**
     * Метод проверяет, работаем ли мы в контексте ajax
     */
    public static function isAjax() {
        /*
         * Определена ли специальная константа, которая определяется в AjaxTools
         * Парамер HTTP_X_REQUESTED_WITH может быть не установлен, например, при загрузке файла с помощью flash.
         */
        if (defined('PS_AJAX_CONTEXT') && !!PS_AJAX_CONTEXT) {
            return true; //---
        }
        /*
         * Проверим наличие переменной HTTP_X_REQUESTED_WITH в глобальном массиве $_SERVER
         */
        return 'xmlhttprequest' == lowertrim(array_get_value('HTTP_X_REQUESTED_WITH', $_SERVER, ''));
    }

    /**
     * Метод проверяет, работаем ли мы из командной строки
     */
    public static function isCmd() {
        return php_sapi_name() === 'cli';
    }

    /**
     * Метод проверяет, выполняем ли мы php процесс
     */
    public static function isCmdPsProcess() {
        return self::isCmd() && defined('PS_PROCESS_FUNCTION_NAME') && is_string(PS_PROCESS_FUNCTION_NAME) && is_callable(PS_PROCESS_FUNCTION_NAME);
    }

    /**
     * Метод описывает контекст выполнения скрипта
     */
    public static function describe() {
        $decr = self::describeImpl();
        return php_sapi_name() . ($decr ? "[$decr]" : '');
    }

    /**
     * Метод описывает детали контекста выполнения скрипта
     */
    private static function describeImpl() {
        if (self::isAjax()) {
            return 'ajax'; //---
        }

        if (self::isCmdPsProcess()) {
            return 'cmd-ps-process';
        }

        if (self::isCmd()) {
            return 'cmd-process'; //---
        }

        return '';
    }

}

?>