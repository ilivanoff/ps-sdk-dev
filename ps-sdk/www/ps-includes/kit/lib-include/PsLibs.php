<?php

/**
 * Класс для подключения внешних библиотек
 *
 * @author azazello
 */
class PsLibs {

    /** @var PsLoggerInterface */
    protected $LOGGER;

    /**
     * Путь к директории библиотек SDK
     * 
     * @var DirItem
     */
    protected $SDK_LIB_DIR;

    /**
     * Путь к директории проектных библиотек.
     * Устанавливается только если указанный в config.ini загрузчик библиотек отличается от базового.
     * 
     * @var DirItem
     */
    protected $PROJ_LIB_DIR = null;

    /**
     * Список подключённых библиотек, а именно - списко вызванных методов
     * 
     * @var array 
     */
    private $INCLUDED = array();

    /**
     * Библиотека для работы с базой
     * 
     * @link http://adodb.sourceforge.net
     */
    public function AdoDb() {
        if ($this->isAlreadyIncluded(__FUNCTION__)) {
            return; //---
        }

        require_once $this->SDK_LIB_DIR . 'Adodb/adodb5/adodb.inc.php';
        require_once $this->SDK_LIB_DIR . 'Adodb/adodb5/drivers/adodb-mysql.inc.php';

        GLOBAL $ADODB_FETCH_MODE, $ADODB_COUNTRECS;

        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $ADODB_COUNTRECS = false;
    }

    /**
     * Шаблонизатор Смарти
     * 
     * @link http://www.smarty.net/
     */
    public function Smarty() {
        if ($this->isAlreadyIncluded(__FUNCTION__)) {
            return; //---
        }
        require_once $this->SDK_LIB_DIR . 'Smarty/Smarty-3.1.21/libs/Smarty.class.php';
    }

    /**
     * Плагин для проверки цензуры в сообщении
     */
    public function Censure() {
        if ($this->isAlreadyIncluded(__FUNCTION__)) {
            return; //---
        }
        require_once $this->SDK_LIB_DIR . 'Censure/Censure-3.2.7/UTF8.php';
        require_once $this->SDK_LIB_DIR . 'Censure/Censure-3.2.7/ReflectionTypehint.php';
        require_once $this->SDK_LIB_DIR . 'Censure/Censure-3.2.7/Text/Censure.php';
    }

    /**
     * Метод должен быть вызван перед подключением библиотеки для предотвращения повторного подключения
     * Пример использования:
     * 
     * if ($this->isAlreadyIncluded(__FUNCTION__)) {
     *     return;//---
     * }
     * 
     * @param string $libName - название подключаемой библиотеки
     * @return boolean - признак, нужно ли подключать данную библиотеку
     */
    private final function isAlreadyIncluded($libName) {
        if (in_array($libName, $this->INCLUDED)) {
            return true;
        }

        $this->INCLUDED[] = $libName;
        $this->LOGGER->info('+ {}', $libName);

        return false;
    }

    /** @var PsLibs */
    private static $inst;

    /**
     * Метод возвращает экземпляр класса, подключающего библиотеки.
     * Для переопределения этого класса, на уровне проектного config.ini
     * должен быть задан другой класс, отвечающий за подключение библиотек.
     * 
     * Это позволит:
     * 1. Использовать стандартизованный метод подключения внешних библиотек
     * 2. Переопределить подключение библиотек из SDK
     */
    public static final function inst() {
        if (isset(self::$inst)) {
            return self::$inst; //----
        }

        /*
         * Получим название класса, отвечающего за подключение библиотек
         */
        $class = ConfigIni::libsIncluder();

        /*
         * Подготовим директории
         */
        $SDK_LIB_DIR = DirItem::inst(PS_DIR_INCLUDES, DirManager::DIR_LIB . DIRECTORY_SEPARATOR)->getAbsPath();
        $PROJ_LIB_DIR = DirItem::inst(PS_DIR_ADDON, DirManager::DIR_LIB . DIRECTORY_SEPARATOR)->getAbsPath();

        /*
         * Класс подключения библиотек совпадает с базовым
         */
        if (__CLASS__ == $class) {
            self::$inst = new PsLibs($class);
            self::$inst->SDK_LIB_DIR = $SDK_LIB_DIR;

            self::$inst->LOGGER = PsLogger::inst($class);
            self::$inst->LOGGER->info('Libs includer  SDK: [{}]', __FILE__);
            self::$inst->LOGGER->info('Libs directory SDK: [{}]', $SDK_LIB_DIR);

            return self::$inst; //---
        }

        /*
         * Нам передан класс, который отличается от SDK
         */
        $classPath = Autoload::inst()->getClassPath($class);
        if (!PsCheck::isNotEmptyString($classPath)) {
            return PsUtil::raise('Не удалось найти класс загрузчика библиотек [{}]', $class);
        }

        /*
         * Указанный класс должен быть наследником данного
         */
        if (!PsUtil::isInstanceOf($class, __CLASS__)) {
            return PsUtil::raise('Указанный загрузчик библиотек [{}] не является наследником класса [{}]', $class, __CLASS__);
        }

        self::$inst = new $class($class);
        self::$inst->SDK_LIB_DIR = $SDK_LIB_DIR;
        self::$inst->PROJ_LIB_DIR = $PROJ_LIB_DIR;

        self::$inst->LOGGER = PsLogger::inst($class);
        self::$inst->LOGGER->info('Libs includer  CUSTOM: [{}]', $classPath);
        self::$inst->LOGGER->info('Libs directory    SDK: [{}]', $SDK_LIB_DIR);
        self::$inst->LOGGER->info('Libs directory CUSTOM: [{}]', $PROJ_LIB_DIR);

        return self::$inst; //---
    }

    /**
     * Конструктор
     */
    protected final function __construct() {
        
    }

}

?>