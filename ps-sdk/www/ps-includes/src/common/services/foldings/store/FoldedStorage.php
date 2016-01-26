<?php

/**
 * Хранилище информации о фолдингах, сущностях фолдингов и т.д.
 *
 * @author azazello
 */
final class FoldedStorage extends AbstractSingleton {

    /**
     * @var PsLoggerInterface 
     */
    private $LOGGER;

    /**
     * @var PsProfilerInterface 
     */
    private $PROFILER;

    /**
     * Карта:
     * тип_фолдинга => array('сущность' => 'абсолютный_путь_к_директории_сущности')
     * 
     * @var array
     */
    private $FOLDING_2_ENTITY_2_ENTABSPATH = array();

    /**
     * Карта:
     * тип_фолдинга => array('сущность' => 'относительный_путь_к_директории_сущности')
     * 
     * @var array
     */
    private $FOLDING_2_ENTITY_2_ENTRELPATH = null;

    /**
     * Карта:
     * префикс_ресурсов => тип_фолдинга
     * 
     * @var array
     */
    private $SOURCE_2_FOLDING = array();

    /**
     * Карта:
     * префикс_классов => тип_фолдинга
     * 
     * @var array
     */
    private $CLASSPREFIX_2_FOLDING = array();

    /** @return FoldedStorage */
    protected static function inst() {
        return parent::inst();
    }

    /**
     * В конструкторе пробежимся по всем хранилищам и соберём все фолдинги
     */
    protected function __construct() {
        $this->LOGGER = PsLogger::inst(__CLASS__);
        $this->PROFILER = PsProfiler::inst(__CLASS__);

        $this->PROFILER->start('Loading folding entities');

        /*
         * Пробегаемся по всему, настроенному в foldings.ini
         */
        foreach (FoldingsIni::foldingsRel() as $foldedUnique => $dirRelPathes) {
            $this->FOLDING_2_ENTITY_2_ENTABSPATH[$foldedUnique] = array();

            /*
             * Загрузим карту сущностей
             */
            foreach (array_unique($dirRelPathes) as $dirRelPath) {
                $dm = DirManager::inst($dirRelPath);
                foreach ($dm->getSubDirNames() as $entity) {
                    //Не будем проверять наличие этой сущности, более поздние смогут её переопределить
                    //array_key_exists($entity, $this->FOLDING_2_ENTITY_2_ENTABSPATH[$foldedUnique])
                    $this->FOLDING_2_ENTITY_2_ENTABSPATH[$foldedUnique][$entity] = $dm->absDirPath($entity);
                }
            }
            ksort($this->FOLDING_2_ENTITY_2_ENTABSPATH[$foldedUnique]);

            /*
             * Построим карты сущностей к типам фолдингов, чтобы мы могли через них выйти на фолдинг
             */
            self::extractFoldedTypeAndSubtype($foldedUnique, $ftype, $fsubtype);

            /*
             * Построим карту отношения идентификатора фолдинга к коду ресурса
             * slib => lib-s
             */
            $this->SOURCE_2_FOLDING[$fsubtype . $ftype] = $foldedUnique;

            /*
             * Построим карту отношения идентификатора фолдинга к префиксу класса
             * SLIB_ => lib-s
             */
            $this->CLASSPREFIX_2_FOLDING[strtoupper($fsubtype . $ftype) . '_'] = $foldedUnique;
        }

        $sec = $this->PROFILER->stop();

        if ($this->LOGGER->isEnabled()) {
            $this->LOGGER->info('FOLDING_2_ENTITY_2_ENTABSPATH: {}', print_r($this->FOLDING_2_ENTITY_2_ENTABSPATH, true));
            $this->LOGGER->info('CLASSPREFIX_2_FOLDING: {}', print_r($this->CLASSPREFIX_2_FOLDING, true));
            $this->LOGGER->info('SOURCE_2_FOLDING: {}', print_r($this->SOURCE_2_FOLDING, true));
            $this->LOGGER->info('BUILDING_TIME: {} sec', $sec->getTotalTime());
        }
    }

    /**
     * Метод возвращает все сущности фолдингов
     */
    public static function listEntities() {
        return self::inst()->FOLDING_2_ENTITY_2_ENTABSPATH;
    }

    /**
     * Проверка существования фолдинга
     * @param string $foldedUnique - код фолдинга [lib-p]
     */
    public static function existsFolding($foldedUnique) {
        return array_key_exists($foldedUnique, self::inst()->FOLDING_2_ENTITY_2_ENTABSPATH);
    }

    /**
     * Метод утверждает, что фолдинг существует
     * @param string $foldedUnique - код фолдинга [lib-p]
     */
    public static function assertExistsFolding($foldedUnique) {
        return check_condition(self::existsFolding($foldedUnique), "Фолдинг с идентификатором [$foldedUnique] не существует");
    }

    /**
     * Проверка существования сущности фолдинга
     * 
     * @param string $foldedUnique - код фолдинга [lib-p]
     * @param string $entity - код сущности
     */
    public static function existsEntity($foldedUnique, $entity) {
        return isset(self::inst()->FOLDING_2_ENTITY_2_ENTABSPATH[$foldedUnique][$entity]);
    }

    /**
     * Метод утверждает, что сущность фолдинга существует
     * @param string $foldedUnique - код фолдинга [lib-p]
     * @param string $entity - код сущности
     */
    public static function assertExistsEntity($foldedUnique, $entity) {
        return check_condition(self::existsEntity($foldedUnique, $entity), "Сущность фолдинга [$foldedUnique-$entity] не существует");
    }

    /**
     * Метод возвращает сущности для указанного типа фолдинга
     * @param string $foldedUnique - код фолдинга [lib-p]
     */
    public static function getEntities($foldedUnique) {
        return self::assertExistsFolding($foldedUnique) ? self::inst()->FOLDING_2_ENTITY_2_ENTABSPATH[$foldedUnique] : null;
    }

    /**
     * Метод возвращает сущность указанного типа фолдинга
     * @param string $foldedUnique - код фолдинга [lib-p]
     * @param string $entity - код сущности
     */
    public static function getEntity($foldedUnique, $entity) {
        return self::assertExistsEntity($foldedUnique, $entity) ? self::inst()->FOLDING_2_ENTITY_2_ENTABSPATH[$foldedUnique][$entity] : null;
    }

    /**
     * Метод возвращает элемент в директории указанной сущности
     * 
     * @param string $foldedUnique - код фолдинга [lib-p]
     * @param string $entity - код сущности
     * @param mixed $dirs - поддиректории
     * @param string $name - название файла
     * @param string $ext - расширение файла
     */
    public static function getEntityChild($foldedUnique, $entity, $dirs, $name = null, $ext = null) {
        return file_path(array(self::getEntity($foldedUnique, $entity), $dirs), $name, $ext);
    }

    /**
     * Получение префикса класса для фолдинга: lib-p => PLIB_
     * @param string $foldedUnique - код фолдинга [lib-p]
     */
    public static function getFoldingClassPrefix($foldedUnique) {
        return self::assertExistsFolding($foldedUnique) ? array_search($foldedUnique, self::inst()->CLASSPREFIX_2_FOLDING) : null;
    }

    /**
     * Получение префикса ресурсов для фолдинга: lib-p => plib
     * @param string $foldedUnique - код фолдинга [lib-p]
     */
    public static function getFoldingSourcePrefix($foldedUnique) {
        return self::assertExistsFolding($foldedUnique) ? array_search($foldedUnique, self::inst()->SOURCE_2_FOLDING) : null;
    }

    /**
     * Метод возвращает все сущности фолдингов и относительные пути к ним
     */
    public static function listEntitiesRel() {
        if (is_null(self::inst()->FOLDING_2_ENTITY_2_ENTRELPATH)) {
            self::inst()->FOLDING_2_ENTITY_2_ENTRELPATH = array();

            foreach (self::listEntities() as $unique => $foldings) {
                foreach ($foldings as $ident => $absPath) {
                    self::inst()->FOLDING_2_ENTITY_2_ENTRELPATH[$unique][$ident] = DIR_SEPARATOR . cut_string_start($absPath, PATH_BASE_DIR);
                }
            }

            self::inst()->LOGGER->info('FOLDING_2_ENTITY_2_ENTRELPATH: {}', print_r(self::inst()->FOLDING_2_ENTITY_2_ENTRELPATH, true));
        }
        return self::inst()->FOLDING_2_ENTITY_2_ENTRELPATH;
    }

    /**
     * Метод патыется получить путь к сущности фолдинга по названию класса.
     * Все классы для сущностей фолдинга начинаются на префикс с подчёркиванием,
     * например PL_, на этом и основан способ подключени класса.
     * 
     * Метод должен быть статическим, так как если мы попытаемся получить путь к
     * классу фолидна, создаваемому Handlers, то никогда его не загрузим.
     */
    public static function tryGetEntityClassPath($className) {
        if (!self::extractInfoFromClassName($className, $classPrefix, $entity)) {
            return null; //---
        }
        $foldedUnique = self::inst()->CLASSPREFIX_2_FOLDING[$classPrefix];
        if (!$foldedUnique || !self::existsEntity($foldedUnique, $entity)) {
            return null; //---
        }
        $classPath = self::getEntityChild($foldedUnique, $entity, null, $entity, PsConst::EXT_PHP);
        return is_file($classPath) ? $classPath : null;
    }

    /**
     * Извлекает информацию из названия класса. Пример:
     * PL_advgraph
     * Будет извлечено PL_ и advgraph.
     * 
     * @param type $className
     * @return null
     */
    public static function extractInfoFromClassName($className, &$classPrefix, &$entity) {
        if (1 !== preg_match('/^[A-Z]+\_/', $className, $matches)) {
            return false; //---
        }
        $ident = cut_string_start($className, $matches[0]);
        if (1 !== preg_match('/^[A-Za-z0-9]+$/', $ident, $imatches)) {
            return false; //---
        }
        $classPrefix = $matches[0];
        $entity = $imatches[0];
        return true; //---
    }

    /**
     * Извлекает тип и подтип фолдинга из его идентификатора:
     * [lib-p] => [lib, p]
     */
    public static function extractFoldedTypeAndSubtype($foldedUnique, &$type, &$subtype) {
        $tokens = explode('-', PsCheck::notEmptyString($foldedUnique), 3);
        $tokensCnt = count($tokens);
        switch ($tokensCnt) {
            case 1:
                $type = PsCheck::notEmptyString($tokens[0]);
                $subtype = '';
                break;
            case 2:
                $type = PsCheck::notEmptyString($tokens[0]);
                $subtype = PsCheck::notEmptyString($tokens[1]);
                break;
            default:
                PsUtil::raise('Invalid folded resource ident: [{}]', $foldedUnique);
        }
    }

}

?>