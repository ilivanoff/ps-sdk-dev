<?php

define('PATH_PLUGINS', PATH_BASE_DIR . '/plugins');

/**
 * Класс для подключения внешних плагинов.
 * @author azazello
 */
class ExternalPluginsManager {

    /**
     * Метод проверит - относится ли файл к файлам внешних плагинов
     */
    public static function isExternalFile($fileAbsPath) {
        return starts_with(normalize_path($fileAbsPath), normalize_path(PATH_PLUGINS . '/'));
    }

    /**
     * 
     */
    public static function PhpMailer() {
        if (self::isInclude(__FUNCTION__)) {
            require_once PATH_PLUGINS . '/PHPMailer_5.2.4/class.phpmailer.php';
        }
    }

    /*
     * 
     * УТИЛИТЫ
     * 
     */

    private static $included = array();

    protected static final function isInclude($key) {
        if (array_key_exists($key, self::$included)) {
            return false;
        }
        self::$included[$key] = true;
        PsLogger::inst(__CLASS__)->info('+ {}', $key);
        return true;
    }

}

?>