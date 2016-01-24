<?php

/**
 * Класс занимается парсингом конфигов sdk и их переопределением.
 *
 * @author azazello
 */
final class ConfigIni extends AbstractIni {

    const GROUP_CORE = 'core';
    const GROUP_LOGGING = 'logging';
    const GROUP_PROFILING = 'profiling';
    const GROUP_ENV = 'environment';
    const CRON_PROCESS = 'cron-pocess';
    const GROUP_CONNECTIONS = 'connection-pool';
    const GROUP_FOLDINGS = 'foldings';
    const GROUP_SMARTY = 'smarty';
    const GROUP_MAPPINGS = 'mappings';
    const GROUP_CACHE = 'cache';
    const GROUP_UPLOADS = 'uploads';
    const GROUP_WEB_PAGES = 'web-pages';
    const GROUP_EXTERNAL_LIBS = 'external-libs';
    const GROUP_AJAX_ACTIONS = 'ajax-actions';

    /*
     * CORE
     */

    public static function projectName() {
        return self::getProp(self::GROUP_CORE, 'project');
    }

    public static function isProduction() {
        return 1 == self::getProp(self::GROUP_CORE, 'production');
    }

    /*
     * LOGGING
     */

    public static function isLoggingEnabled() {
        return 1 == self::getProp(self::GROUP_LOGGING, 'enabled');
    }

    /*
     * PROFILING
     */

    public static function isProfilingEnabled() {
        return 1 == self::getProp(self::GROUP_PROFILING, 'enabled');
    }

    public static function profilingMaxFileSize() {
        return PsCheck::int(self::getPropCheckType(self::GROUP_PROFILING, 'max-file-size', array(PsConst::PHP_TYPE_STRING)));
    }

    /*
     * ENVIROMENTS
     */

    public static function environment() {
        return self::getPropCheckType(self::GROUP_ENV, 'environment', array(PsConst::PHP_TYPE_STRING, PsConst::PHP_TYPE_NULL));
    }

    public static function environments() {
        return DirManager::relToAbs(self::getPropCheckType(self::GROUP_ENV, 'environments', array(PsConst::PHP_TYPE_ARRAY)));
    }

    /*
     * CACHE
     */

    public static function cacheEngine() {
        return self::getPropCheckType(self::GROUP_CACHE, 'engine', array(PsConst::PHP_TYPE_STRING));
    }

    public static function cacheFileLifetime() {
        return self::getPropCheckType(self::GROUP_CACHE, 'cache-file-lifetime', array(PsConst::PHP_TYPE_STRING));
    }

    /*
     * CRON
     */

    public static function cronProcesses() {
        return self::getPropCheckType(self::CRON_PROCESS, 'cron', array(PsConst::PHP_TYPE_ARRAY, PsConst::PHP_TYPE_NULL));
    }

    /*
     * SMARTY
     */

    public static function smartyFilter() {
        return self::getPropCheckType(self::GROUP_SMARTY, 'filter', array(PsConst::PHP_TYPE_STRING));
    }

    public static function smartyPlugins() {
        return DirManager::relToAbs(self::getPropCheckType(self::GROUP_SMARTY, 'plugins', array(PsConst::PHP_TYPE_ARRAY, PsConst::PHP_TYPE_NULL)));
    }

    public static function smartyTemplates() {
        return DirManager::relToAbs(self::getPropCheckType(self::GROUP_SMARTY, 'templates', array(PsConst::PHP_TYPE_ARRAY, PsConst::PHP_TYPE_NULL)));
    }

    /*
     * UPLOADS
     */

    public static function uploadsDirRel() {
        return self::getPropCheckType(self::GROUP_UPLOADS, 'dir', array(PsConst::PHP_TYPE_STRING));
    }

    /*
     * MAPPINGS
     */

    public static function mappingStorage() {
        return self::getPropCheckType(self::GROUP_MAPPINGS, 'storage', array(PsConst::PHP_TYPE_STRING));
    }

    /*
     * WEB-PAGES
     */

    public static function webPagesStore() {
        return self::getPropCheckType(self::GROUP_WEB_PAGES, 'storage', array(PsConst::PHP_TYPE_STRING));
    }

    /*
     * EXTERNAL LIBS
     */

    public static function libsIncluder() {
        return self::getPropCheckType(self::GROUP_EXTERNAL_LIBS, 'libs', array(PsConst::PHP_TYPE_STRING));
    }

    /*
     * AJAX ACTIONS
     */

    private static $ajax = array();

    public static function ajaxActionsAbs($group) {
        return array_key_exists($group, self::$ajax) ? self::$ajax[$group] : self::$ajax[$group] = DirManager::relToAbs(self::getPropCheckType(self::GROUP_AJAX_ACTIONS, $group, array(PsConst::PHP_TYPE_ARRAY)));
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