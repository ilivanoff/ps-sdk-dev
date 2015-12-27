<?php

class PSCache_1 extends AbstractSingleton implements PSCacheEngine {

    /**
     * Список всех зарегистрированных групп кеша
     */
    public static function getCacheGroups() {
        return PsUtil::getClassConstLikeMethods(__CLASS__);
    }

    /**
     * Кеш для навигации - будет сброшен при изменении любого поста или количества опубликованных постов.
     * 
     * @return PSCacheInst
     */
    public static final function POSTS() {
        return PSCacheInst::inst(__FUNCTION__);
    }

    /**
     * Кеш для popup-страниц. Будет сброшен при изменении кол-ва видимых плагинов,
     * которое происходит при изменении поста или кол-ва видимых постов.
     * 
     * @return PSCacheInst
     */
    public static final function POPUPS() {
        return PSCacheInst::inst(__FUNCTION__);
    }

    /**
     * Кеш для временных шкал.
     * 
     * @return PSCacheInst
     */
    public static final function TIMELINES() {
        return PSCacheInst::inst(__FUNCTION__);
    }

    /**
     * Кеш для картинок-мозаек.
     * 
     * @return PSCacheInst
     */
    public static final function MOSAIC() {
        return PSCacheInst::inst(__FUNCTION__);
    }

    /*
     * ПОЛЯ
     */

    /** @var PsLoggerInterface */
    private $LOGGER;

    /** @var SimpleDataCache */
    private $CACHE_LOCAL;

    /** @var Cache_Lite */
    private $CACHE_FILE;

    /*
     * 
     * МЕТОДЫ
     * 
     */

    private function localCacheGroup($group) {
        return PsCheck::notEmptyString($group) . ' ';
    }

    private function localCacheKey($id, $group) {
        return $this->localCacheGroup($group) . '[' . PsCheck::notEmptyString($id) . ']';
    }

    /**
     * Метод валидирует значение кода группы и ключа.
     * 
     * @param string $id - Код значения
     * @param string $group - Группа, в которую входит код
     */
    private function buildCacheKey($id, $group) {
        return PsCheck::notEmptyString($group) . ' [' . PsCheck::notEmptyString($id) . ']';
    }

    /**
     * Метод загружает значение из кеша
     * 
     * @param string $id - Код значения
     * @param string $group - Группа, в которую входит код
     * @param array|null $REQUIRED_KEYS - Ключи, наличие которых должны быть в кеше.
     *                                    Если переданы - будет проверено, что значение является массивом и содержит все необходимые ключи
     * @param mixed $sign - Подпись, которая должна совпасть для кеша.
     */
    public function getFromCache($id, $group, /* array */ $REQUIRED_KEYS = null, $sign = null) {
        $cacheId = $this->localCacheKey($id, $group);

        //Сначала ищем в локальном хранилище
        if ($this->CACHE_LOCAL->has($cacheId)) {
            $CACHED = $this->CACHE_LOCAL->get($cacheId);
            if ($CACHED['sign'] == $sign) {
                $this->LOGGER->info("Информация по ключу '$cacheId' найдена в локальном кеше");
                return $CACHED['data'];
            } else {
                $this->LOGGER->info("Информация по ключу '$cacheId' найдена в локальном кеше, но старая и новая подписи не совпадают: [{}]!=[{}]. Чистим...", $CACHED['sign'], $sign);
                $this->CACHE_LOCAL->remove($cacheId);
                $this->CACHE_FILE->remove($id, $group);
                return null;
            }
        }

        /*
         * Раньше здесь был код проверки валидности группы кешей.
         * В новой реализации мы от этого отказались и будем позиционировать данный класс только как для хранения данных.
         */
        //$this->validateGroup($group);


        PsProfiler::inst(__CLASS__)->start('LOAD from cache');
        $CACHED = $this->CACHE_FILE->get($id, $group);
        PsProfiler::inst(__CLASS__)->stop();

        if (!$CACHED) {
            $this->LOGGER->info("Информация по ключу '$cacheId' не найдена в кеше");
            return null;
        }

        if (!is_array($CACHED)) {
            $this->LOGGER->info("Информация по ключу '$cacheId' найдена в хранилище, но не является массивом. Чистим...");
            $this->CACHE_FILE->remove($id, $group);
            return null;
        }

        if (!array_has_all_keys(array('data', 'sign'), $CACHED)) {
            $this->LOGGER->info("Информация по ключу '$cacheId' найдена в хранилище, но отсутствует параметр sign или data. Чистим...");
            $this->CACHE_FILE->remove($id, $group);
            return null;
        }

        if ($CACHED['sign'] != $sign) {
            $this->LOGGER->info("Информация по ключу '$cacheId' найдена в хранилище, но старая и новая подписи не совпадают: [{}]!=[{}]. Чистим...", $CACHED['sign'], $sign);
            $this->CACHE_FILE->remove($id, $group);
            return null;
        }

        $MUST_BE_ARRAY = is_array($REQUIRED_KEYS);
        $REQUIRED_KEYS = to_array($REQUIRED_KEYS);
        if ($MUST_BE_ARRAY || !empty($REQUIRED_KEYS)) {
            //Если нам переданы ключи для проверки, значит необходимо убедиться, что сами данные являются массивом
            if (!is_array($CACHED['data'])) {
                $this->LOGGER->info("Информация по ключу '$cacheId' найдена в хранилище, но не является массивом. Чистим...");
                $this->CACHE_FILE->remove($id, $group);
                return null;
            }
            foreach ($REQUIRED_KEYS as $key) {
                if (!array_key_exists($key, $CACHED['data'])) {
                    $this->LOGGER->info("Информация по ключу '$cacheId' найдена, но в данных отсутствует обязательный ключ [$key]. Чистим...");
                    $this->CACHE_FILE->remove($id, $group);
                    return null;
                }
            }
        }

        $this->LOGGER->info("Информация по ключу '$cacheId' найдена в хранилище");
        //Перенесём данные в локальный кеш для быстрого доступа
        return array_get_value('data', $this->CACHE_LOCAL->set($cacheId, $CACHED));
    }

    public function saveToCache($object, $id, $group, $sign = null) {
        $cacheId = $this->localCacheKey($id, $group);
        $this->LOGGER->info("Информация по ключу '$cacheId' сохранена в кеш");

        $CACHED['sign'] = $sign;
        $CACHED['data'] = $object;

        //Нужно быть аккуратным - в cacheLite мы храним данные и подпись, а в local CACHE только данные
        PsProfiler::inst(__CLASS__)->start('SAVE to cache');
        $this->CACHE_FILE->save($CACHED, $id, $group);
        PsProfiler::inst(__CLASS__)->stop();
        return array_get_value('data', $this->CACHE_LOCAL->set($cacheId, $CACHED));
    }

    public function cleanCache($group = null) {
        $this->clean($group);
    }

    public function clean($group = null) {
        $this->LOGGER->info($group ? "Очистка кеша по группе [$group]" : 'Полная очистка кеша');
        $this->CACHE_FILE->clean($group);
        if ($group) {
            //Очистим ключи локального хранилища
            $keys = $this->CACHE_LOCAL->keys();
            $removed = array();
            $prefix = $this->localCacheGroup($group);
            foreach ($keys as $key) {
                if (starts_with($key, $prefix)) {
                    $removed[] = $key;
                    $this->CACHE_LOCAL->remove($key);
                }
            }
            if ($removed) {
                $this->LOGGER->info('В локальном кеше были удалены следующие ключи: {}.', concat($removed));
            }
        } else {
            $this->CACHE_LOCAL->clear();
        }
    }

    /** @return PSCache */
    public static function inst() {
        return parent::inst();
    }

    protected function __construct() {
        $this->CACHE_LOCAL = new SimpleDataCache();
        $this->LOGGER = PsLogger::inst(__CLASS__);

        /**
         * Подключаем cache lite
         */
        ExternalPluginsManager::CacheLite();

        $liteOptions = array(
            'readControl' => true,
            'writeControl' => true,
            'readControlType' => 'md5',
            'automaticSerialization' => true, //Чтобы можно было писать объекты
            //
            'cacheDir' => DirManager::autogen('cache')->absDirPath(),
            'lifeTime' => CACHE_LITE_LIFE_TIME * 60,
            'caching' => true //Кеширование включено всегда
        );

        $this->CACHE_FILE = new Cache_Lite($liteOptions);
    }

}

?>