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

    /**
     * 
     */
    public static function Pear() {
        if (self::isInclude(__FUNCTION__)) {
            require_once PATH_PLUGINS . '/PEAR-1.9.4/PEAR.php';
        }
    }

    /**
     * 
     */
    public static function CacheLite() {
        if (self::isInclude(__FUNCTION__)) {
            require_once PATH_PLUGINS . '/Cache_Lite-1.7.11/Cache_Lite-1.7.11/Lite.php';
            require_once PATH_PLUGINS . '/Cache_Lite-1.7.11/Cache_Lite-1.7.11/Lite/Output.php';
        }
    }

    /**
     * 
     */
    public static function SimpleHtmlDom() {
        if (self::isInclude(__FUNCTION__)) {
            require_once PATH_PLUGINS . '/simplehtmldom_1_5/simple_html_dom.php';
        }
    }

    /**
     * 
     */
    public static function SpriteGenerator() {
        if (self::isInclude(__FUNCTION__)) {
            require_once PATH_PLUGINS . '/css-sprite-generator-v4.1/includes/ps-css-sprite-gen.inc.php';
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