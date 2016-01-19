<?php

/**
 * Класс отвечает за окружение, в котором выполняется ps-sdk
 *
 * @author azaz
 */
class PsEnvironment {

    /**
     * Базовое окружение - то есть мы не работаем как часть другой CMS
     */
    const ENV_NONE = null;

    /**
     * Мы работаем как плагин wordpress
     */
    const ENV_WP = 'wordpress';

    /**
     * Метод возвращает идентификатор работчего окружения
     */
    public static function env() {
        return PsUtil::assertClassHasConstVithValue(__CLASS__, 'ENV_', ConfigIni::environment());
    }

    /**
     * Метод проверяет, соответствует ли окружение переданному
     */
    public static function isEnv($env) {
        return self::env() == PsUtil::assertClassHasConstVithValue(__CLASS__, 'ENV_', $env);
    }

    /**
     * Метод вызывается для инициализации окружения
     */
    public static function init() {
        
    }

}
