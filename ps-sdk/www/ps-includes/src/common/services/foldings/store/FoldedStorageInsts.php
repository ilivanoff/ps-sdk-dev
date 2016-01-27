<?php

/**
 * Хранилище экземпляров фолдингов, с которыми можно работать.
 * Можно переопределить в config.ini и использовать проектный.
 *
 * @author azazello
 */
class FoldedStorageInsts {

    /**
     * @var PsLoggerInterface 
     */
    private $LOGGER;

    /**
     * @var PsProfilerInterface 
     */
    private $PROFILER;

    /** @var arr Список доступных фолдингов */
    private $foldings = array();

    /**
     * Регистрация страниц SDK
     */
    private function registerSdkFoldings() {
        $this->register(PopupPagesManager::inst());
        $this->register(PluginsManager::inst());
        $this->register(TimeLineManager::inst());
        $this->register(UserPointsManager::inst());
        $this->register(StockManager::inst());
        $this->register(HelpManager::inst());
        $this->register(EmailManager::inst());
        $this->register(PSForm::inst());
        $this->register(DialogManager::inst());
        //Библиотеки
        $this->register(PoetsManager::inst());
        $this->register(ScientistsManager::inst());
        //Админские страницы
        $this->register(APagesResources::inst());
    }

    /**
     * Регистрация проектных фолдингов
     */
    protected function registerProjectFoldings() {
        
    }

    /**
     * Метод регистрации экземпляров фолдингов
     * 
     * @param FoldedResources $inst - экземпляр
     */
    protected final function register(FoldedResources $inst) {
        $unique = $inst->getUnique();
        if (array_key_exists($unique, $this->foldings)) {
            PsUtil::raise('Folding \'{}\' is already registered. Cannot register \'{}\' with same unique.', $this->foldings[$unique], $inst);
        } else {
            $this->foldings[$unique] = $inst;

            if ($this->LOGGER->isEnabled()) {
                $this->LOGGER->info('+{}. {}, count: {}.', pad_left(count($this->foldings), 3, ' '), $inst, FoldedStorage::getEntitiesCount($unique));
            }
        }
    }

    /** @var FoldedStorageInsts */
    private static $inst;

    /**
     * Метод возвращает экземпляр класса-хранилища экземпляров фолдинов.
     * Для переопределения этого класса, на уровне проектного config.ini
     * должен быть задан другой класс.
     * 
     * @return FoldedStorageInsts
     */
    public static final function inst() {
        if (isset(self::$inst)) {
            return self::$inst; //----
        }

        /*
         * Получим название класса
         */
        $class = FoldingsIni::foldingsStore();

        /*
         * Класс совпадает с базовым
         */
        if (__CLASS__ == $class) {
            return self::$inst = new FoldedStorageInsts();
        }

        /*
         * Нам передан класс, который отличается от SDK
         */
        $classPath = Autoload::inst()->getClassPath($class);
        if (!PsCheck::isNotEmptyString($classPath)) {
            return PsUtil::raise('Не удалось найти класс регистрации экземпляров фолдингов [{}]', $class);
        }

        /*
         * Указанный класс должен быть наследником данного
         */
        if (!PsUtil::isInstanceOf($class, __CLASS__)) {
            return PsUtil::raise('Указанный класс регистрации экземпляров фолдингов [{}] не является наследником класса [{}]', $class, __CLASS__);
        }

        return self::$inst = new $class();
    }

    /**
     * В конструкторе зарегистрируем все страницы
     */
    protected final function __construct() {
        //Инициализируем хранилище, чтобы честно замерять время создания регистрации самих экземпляров
        FoldedStorage::init();

        $class = get_called_class();
        $basic = __CLASS__ == $class;

        //Логгер
        $this->LOGGER = PsLogger::inst(__CLASS__);
        $this->LOGGER->info('USING {} STORAGE: {}', $basic ? 'SDK' : 'CUSTOM', $class);

        //Стартуем профайлер
        $this->PROFILER = PsProfiler::inst(__CLASS__);
        $this->PROFILER->start('Loading folding insts');

        //Регистрируем фолдинги
        $this->LOGGER->info();
        $this->LOGGER->info('FOLDINGS SDK:');
        $this->registerSdkFoldings();

        if (!$basic) {
            $this->LOGGER->info();
            $this->LOGGER->info('FOLDINGS PROJECT:');
            $this->registerProjectFoldings();
        }

        //Останавливаем профайлер
        $sec = $this->PROFILER->stop();

        //Логируем
        $this->LOGGER->info();
        $this->LOGGER->info('COLLECTING TIME: {} sec', $sec->getTotalTime());
    }

}

?>