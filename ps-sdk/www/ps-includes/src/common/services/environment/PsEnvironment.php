<?php

/**
 * Класс отвечает за окружение, в котором выполняется ps-sdk
 *
 * @author azaz
 */
class PsEnvironment {

    private static $inited = false;

    /**
     * Метод возвращает идентификатор работчего окружения
     */
    public static function env() {
        return ConfigIni::environment();
    }

    /**
     * Метод проверяет, соответствует ли окружение переданному
     */
    public static function isEnv($env) {
        return self::env() == $env;
    }

    /**
     * Метод вызывается для инициализации окружения:
     * 1. Директория ресурсов окружения будет подключена в Autoload
     * 2. Файл, включающий окружение, будет выполнен
     */
    public static function init() {
        if (self::$inited) {
            return; //---
        }

        self::$inited = true; //---

        $env = self::env();

        if (!$env) {
            return; //---
        }

        $envDir = array_get_value($env, ConfigIni::environments());

        if (!$envDir) {
            return PsUtil::raise('Environment [{}] not found', $env);
        }

        if (!is_dir($envDir)) {
            return PsUtil::raise('Environment dir for [{}] not found', $env);
        }

        $envSrcDir = next_level_dir($envDir, DirManager::DIR_SRC);
        $envIncFile = file_path($envDir, $env, PsConst::EXT_PHP);

        if (!is_file($envIncFile)) {
            return PsUtil::raise('Environment include file for [{}] not found', $env);
        }

        $LOGGER = PsLogger::inst(__CLASS__);
        if ($LOGGER->isEnabled()) {
            $LOGGER->info('Using [{}]', $env);
            $LOGGER->info('Env dir:  {}', $envDir);
            $LOGGER->info('Src dir:  {}', $envSrcDir);
            $LOGGER->info('Inc file: {}', $envIncFile);
        }

        //Регистрируем директорию с классами, специфичными только для данного окружения
        Autoload::inst()->registerBaseDir($envSrcDir, false);

        //Выполним необходимое действие
        $PROFILER = PsProfiler::inst(__CLASS__);
        try {
            $PROFILER->start($env);
            self::initImpl($envIncFile);
            $secundomer = $PROFILER->stop();
            $LOGGER->info('Inc file included for {} sec', $secundomer->getTime());
        } catch (Exception $ex) {
            $PROFILER->stop(false);
            $LOGGER->info('Inc file execution error: [{}]', $ex->getMessage());
            throw $ex; //---
        }
    }

    public static function initImpl($_envIncFile_) {
        require_once $_envIncFile_;
    }

}
