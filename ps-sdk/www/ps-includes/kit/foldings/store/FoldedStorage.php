<?php

/**
 * Хранилище данных о фолдингах
 *

  $entities = Array (
  [ap] => Array (
  [!PATTERN] => C:/www/ps-sdk-dev/ps-sdk/www/ps-content/folded/apages/!PATTERN
  [APAudit] => C:/www/ps-sdk-dev/ps-sdk/www/ps-content/folded/apages/APAudit
  [APCommon] => C:/www/ps-sdk-dev/ps-sdk/www/ps-content/folded/apages/APCommon
  )

  [dg] => Array (
  [!PATTERN] => C:/www/ps-sdk-dev/ps-sdk/www/ps-content/folded/dialog/!PATTERN
  [misprint] => C:/www/ps-sdk-dev/ps-sdk/www/ps-content/folded/dialog/misprint
  [plugins] => C:/www/ps-sdk-dev/ps-sdk/www/ps-content/folded/dialog/plugins
  )
  )

 * 
 * @author azazello
 */
class FoldedStorage {

    /**
     * Карта:
     * тип_фолдинга => array('сущность' => 'абсолютный_путь_к_директории_сущности')
     * 
     * @var type 
     */
    private static $entities;

    public static function getEntities() {
        if (isset(self::$entities)) {
            return self::$entities;
        }

        self::$entities = array();
        $i = 0;
        foreach (FoldingsIni::foldingsRel() as $foldedUnique => $relPathes) {
            self::$entities[$foldedUnique] = array();
            foreach (array_unique($relPathes) as $relPath) {
                $dm = DirManager::inst($relPath);
                foreach ($dm->getSubDirNames() as $entity) {
                    ++$i;
                    if (array_key_exists($entity, self::$entities[$foldedUnique])) {
                        continue; //---
                    }
                    self::$entities[$foldedUnique][$entity] = $dm->absDirPath($entity);
                }
            }
            ksort(self::$entities[$foldedUnique]);
        }

        return self::$entities; //---
    }

    public static function existsEntity($foldedUnique, $entity) {
        return isset(self::getEntities()[$foldedUnique][$entity]);
    }

    public static function getEntityAbsPath($foldedUnique, $entity) {
        return array_get_value_in(array($foldedUnique, $entity), self::getEntities());
    }

    public static function getEntityAbsPathChild($foldedUnique, $entity, $dirs, $name, $ext = null) {
        $abs = self::getEntityAbsPath($foldedUnique, $entity);
        return $abs ? file_path($dirs ? array($abs, $dirs) : $abs, $name, $ext) : null;
    }

    /**
     * Метод патыется получить путь к сущности фолдинга по названию класса.
     * Все классы для сущностей фолдинга начинаются на префикс с подчёркиванием,
     * например PL_, на этом и основан способ подключени класса.
     * 
     * Метод должен быть статическим, так как если мы попытаемся получить путь к
     * классу фолидна, создаваемому Handlers, то никогда его не загрузим.
     */
    public static function tryGetFoldedEntityClassPath($className) {
        if (!self::extractInfoFromClassName($className, $srcPrefix, $entity)) {
            return null; //---
        }
        $classPath = self::getEntityAbsPathChild($srcPrefix, $entity, null, $entity, PsConst::EXT_PHP);
        return $classPath && is_file($classPath) ? $classPath : null;
    }

    /**
     * Извлекает информацию из названия класса. Пример:
     * PL_advgraph
     * Будет извлечено pl и advgraph.
     * 
     * @param type $className
     * @return null
     */
    private static function extractInfoFromClassName($className, &$srcPrefix, &$entity) {
        $tokens = explode('_', trim($className), 3);
        if (count($tokens) != 2) {
            return false; //--
        }
        if (!FoldedResources::isValidClassPrefix($tokens[0] . '_') || !$tokens[1]) {
            return false; //---
        }
        $srcPrefix = strtolower($tokens[0]);
        $entity = $tokens[1];
        return true; //---
    }

}

?>