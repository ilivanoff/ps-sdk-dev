<?php

/**
 * Процесс, который должен периодически запускаться.
 * Пока мы его реализуем, как вызов от имени пользователя при запросе страницы.
 * В будущем можно вынести на cron или на службу проверки доступности сайтов.
 * Но вообще, конечно, нужно иметь ввиду: http://habrahabr.ru/post/179399/
 */
final class ExternalProcess extends AbstractSingleton {

    /** Признак - была ли попытка вызова. Обработку выполняем всего 1 раз за время выполнения скрипта. */
    private $called = false;

    /** Признак - выполнился ли процесс фактически. */
    private $executed = false;

    /**
     * Метод вызывается для выполнения периодических задач cron
     * 
     * @return type
     */
    public function execute() {
        if ($this->called) {
            return $this->executed; //---
        }
        $this->called = true;

        $LOGGER = PsLogger::inst(__CLASS__);

        $LOGGER->info('Executing {}', __CLASS__);

        /*
         * Получим список классов, которые нужно выполнить
         */
        $processes = ConfigIni::cronProcesses();
        $LOGGER->info('Processes: {}', array_to_string($processes));

        if (empty($processes)) {
            $LOGGER->info('No cron processes configured, fast return...');
            return $this->executed; //---
        }

        $processes = array_unique($processes);

        foreach ($processes as $class) {
            if (!PsUtil::isInstanceOf($class, 'PsCronProcess')) {
                PsUtil::raise("Class $class cannot be executed as cron process, it should be instance of PsCronProcess");
            }
        }

        $locked = PsLock::lock(__CLASS__, false);

        $LOGGER->info('Lock accured ? {}', var_export($locked, true));

        if (!$locked) {
            return $this->executed; //---
        }

        $LOCKFILE = DirManager::autoNoDel(DirManager::DIR_SERVICE)->getDirItem(null, __CLASS__, PsConst::EXT_LOCK);
        $LOCKFILE_LIFETIME = $LOCKFILE->getFileLifetime();
        $MAX_LIFETIME = 5 * 60;
        $NED_PROCESS = $LOCKFILE_LIFETIME === null || ($LOCKFILE_LIFETIME > $MAX_LIFETIME);

        $LOGGER->info('Process lock file: {} [{}]. Last modified: {} seconds ago. Max process delay: {} seconds.', //
                $LOCKFILE->getRelPath(), //
                $LOCKFILE_LIFETIME === null ? 'NOT EXISTS' : 'EXISTS', //
                var_export($LOCKFILE_LIFETIME, true), //
                $MAX_LIFETIME//
        );

        $LOGGER->info('Need actual execute ? {}', var_export($NED_PROCESS, true));

        if (!$NED_PROCESS) {
            //Отпустим лок
            PsLock::unlock();

            //Выходим
            return $this->executed; //---
        }

        //Обновим время последнего выполнения
        $LOCKFILE->touch();

        //Отпустим лок, так как внутри он может потребоваться для выполнения других действий, например для перестройки спрайтов
        PsLock::unlock();

        $LOGGER->info('Start external process actual execution...');

        //Запускаем режим неограниченного выполнения
        PsUtil::startUnlimitedMode();

        //Начинаем выполнение
        $this->executed = true;

        //Создаём профайлер
        $PROFILER = PsProfiler::inst(__CLASS__);

        //Создадим конфиг выполнения процесса
        $config = new PsCronProcessConfig($LOCKFILE_LIFETIME);

        //Пробегаемся по процессам и выполняем. При первой ошибке - выходим.
        foreach ($processes as $class) {
            $LOGGER->info('Executing cron process {}', $class);
            $PROFILER->start($class);
            try {
                $inst = new $class();
                $inst->onCron($config);
                $secundomer = $PROFILER->stop();
                $LOGGER->info("Cron process {} executed in {} seconds", $class, $secundomer->getTotalTime());
            } catch (Exception $ex) {
                $PROFILER->stop();
                $LOGGER->info("Cron process {} execution error: {}", $class, $ex->getMessage());
            }
        }

        return $this->executed;
    }

    /** @return ExternalProcess */
    public static function inst() {
        return parent::inst();
    }

}

?>