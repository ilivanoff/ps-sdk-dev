<?php

/**
 * Класс занимается парсингом конфигов sdk и их переопределением.
 *
 * @author azazello
 */
final class ConfigIni extends AbstractIni {

    const GROUP_CORE = 'core';
    const CRON_PROCESS = 'cron-pocess';
    const GROUP_CONNECTIONS = 'connection-pool';
    const GROUP_FOLDINGS = 'foldings';
    const GROUP_SMARTY = 'smarty';
    const GROUP_CACHE = 'cache';

    public static function projectName() {
        return self::getProp(self::GROUP_CORE, 'project');
    }

    public static function libsIncluder() {
        return self::getPropCheckType(self::GROUP_CORE, 'libs', array(PsConst::PHP_TYPE_STRING));
    }

    public static function webPagesStore() {
        return self::getPropCheckType(self::GROUP_CORE, 'web-pages', array(PsConst::PHP_TYPE_STRING));
    }

    public static function cacheEngine() {
        return self::getPropCheckType(self::GROUP_CACHE, 'engine', array(PsConst::PHP_TYPE_STRING));
    }

    public static function cacheFileLifetime() {
        return self::getPropCheckType(self::GROUP_CACHE, 'cache-file-lifetime', array(PsConst::PHP_TYPE_STRING));
    }

    public static function cronProcesses() {
        return self::getPropCheckType(self::CRON_PROCESS, 'cron', array(PsConst::PHP_TYPE_ARRAY, PsConst::PHP_TYPE_NULL));
    }

    public static function smartyFilter() {
        return self::getPropCheckType(self::GROUP_SMARTY, 'filter', array(PsConst::PHP_TYPE_STRING));
    }

    public static function smartyPlugins() {
        return DirManager::relToAbs(self::getPropCheckType(self::GROUP_SMARTY, 'plugins', array(PsConst::PHP_TYPE_ARRAY, PsConst::PHP_TYPE_NULL)));
    }

    public static function smartyTemplates() {
        return DirManager::relToAbs(self::getPropCheckType(self::GROUP_SMARTY, 'templates', array(PsConst::PHP_TYPE_ARRAY, PsConst::PHP_TYPE_NULL)));
    }

    public static function isSdk() {
        return self::projectName() == 'sdk';
    }

    public static function isProject() {
        return !self::isSdk();
    }

    /**
     * Возвращает доступные скоупы. Всегда доступен SDK и,
     * если мы работаем из проекта - доступен PROJ.
     */
    public static function getAllowedScopes() {
        $scopes = array(ENTITY_SCOPE_SDK);
        if (self::isProject()) {
            $scopes[] = ENTITY_SCOPE_PROJ;
        }
        return $scopes;
    }

}

?>