<?php

/**
 * Класс - движок для хранения данных в постоянном хранилище на основе файла
 *
 * @author azazello
 */
class PSCacheEngineFile implements PSCacheEngine {

    /** @var Cache_Lite */
    private $CACHE_FILE;

    public function __construct() {
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

    public function cleanCache($group = null) {
        
    }

    public function getFromCache($id, $group) {
        
    }

    public function removeFromCache($id, $group) {
        
    }

    public function saveToCache($object, $id, $group) {
        
    }

}

?>
