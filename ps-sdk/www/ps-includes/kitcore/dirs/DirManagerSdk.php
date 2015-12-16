<?php

/**
 * Расширение DirManager с методами для быстрого допуска к предопределённым директориям
 *
 * @author azazello
 */
class DirManagerSdk extends DirManager {

    /**
     * Путь к базовой папке с файлами SDK
     * 
     * @return DirManager
     */
    public static final function sdk($notCkeckDirs = null) {
        return self::inst(array(PATH_SDK_DIR, $notCkeckDirs));
    }

}

?>