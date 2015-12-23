<?php

/**
 * Процесс строит скрипты разворачивания БД
 * 
 * @param array $argv
 */
function executeProcess(array $argv) {
    PsConnectionPool::assertDisconnectied();

    /*
     * СОЗДАЁМ SQL
     * 
     * Нам нужны настройки таблиц, которые неоткуда взять, кроме как из базы, поэтому для экспорта данных нужна БД.
     */
    $DB = DirManager::inst(__DIR__ . '/temp');
    $SDK = DirManager::inst(__DIR__ . '/temp/ps-sdk');

    //Почистим файлы для удаления
    dolog('Clearing not .sql/.txt from temp dir');
    /* @var $item DirItem */
    foreach ($DB->getDirContentFull(null, DirItemFilter::FILES) as $item) {
        if (!$item->checkExtension(array(PsConst::EXT_TXT, PsConst::EXT_SQL))) {
            dolog('[-] {}', $item->remove()->getRelPath());
        }
    }
    dolog();

    /*
     * SDK
     */
    //dolog('Processing objects.sql');

    /* @var $DIR DirManager */
    foreach (array(ENTITY_SCOPE_SDK => $SDK, ENTITY_SCOPE_PROJ => $DB) as $scope => $DM) {
        dolog();
        dolog('***************************************************************');
        dolog('>>> Processing scope [{}], dir: [{}]', $scope, $DM->absDirPath());

        $SCHEMA = $DM->getDirItem(null, 'schema.sql');
        if (!$SCHEMA->isFile()) {
            dolog('schema.sql is not exists, skipping');
            continue; //---
        }

        //Директория с системными объектами
        $DM_SYSOBJECTS = DirManager::inst($DM->absDirPath(), 'sysobjects');

        //Директория, в которой будет содержимое для автосгенерированных файлов
        $DM_BUILD = DirManager::inst($DM->absDirPath(), 'build')->clearDir();

        //Создадим ссылку на файл с объектами
        $DM_BUILD_OBJECTS_SQL = $DM_BUILD->getDirItem(null, 'objects', PsConst::EXT_SQL)->getSqlFileBuilder();

        //Заново инициализируем блоки логов
        LOGBOX_INIT();

        //Строим objects.sql
        LOGBOX('Processing objects.sql');

        /*
         * Получаем строки с включениями в objects.sql
         */
        $ALL_LINES = $DM_SYSOBJECTS->getDirItem(null, 'all', PsConst::EXT_TXT)->getFileLines(false);
        if (empty($ALL_LINES)) {
            dolog('No includes');
        } else {
            dolog('Adding {} includes from all.txt', count($ALL_LINES));

            $DM_BUILD_OBJECTS_SQL->appendMlComment('INCLUDES SECTION');
            foreach ($ALL_LINES as $include) {
                dolog('+ {}', $include);
                $DM_BUILD_OBJECTS_SQL->appendFile($DM_SYSOBJECTS->getDirItem($include));
            }
        }

        // << Сохраняем objects.sql
        $DM_BUILD_OBJECTS_SQL->save();

        /*
         * Создаём скрипты инициализации для схем
         */
        foreach (PsConnectionParams::getDefaultConnectionNames() as $connection) {
            //Для данного скоупа не задан коннект? Пропускаем...
            if (!PsConnectionParams::has($connection, $scope)) {
                continue; //---
            }

            //Поработаем с настройками
            $props = PsConnectionParams::get($connection, $scope);
            $database = $props->database();

            if (empty($database)) {
                continue; //Не задана БД - пропускаем (для root)
            }

            LOGBOX('Making schema script for {}', $props);

            $SCHEMA_DI = $DM_BUILD->getDirItem('schemas', $database, PsConst::EXT_SQL)->makePath();
            check_condition(!$SCHEMA_DI->isFile(), 'Schema file for database "{}" is already exists. Dublicate database names?', $database);
            $SCHEMA_SQL = $SCHEMA_DI->getSqlFileBuilder();

            //DROP+USE
            $SCHEMA_SQL->clean();
            $SCHEMA_SQL->appendLine("DROP DATABASE IF EXISTS $database;");
            $SCHEMA_SQL->appendLine("CREATE DATABASE $database CHARACTER SET utf8 COLLATE utf8_general_ci;");
            $SCHEMA_SQL->appendLine("USE $database;");

            if ($scope == ENTITY_SCOPE_PROJ) {
                dolog('+ SDK PART');

                //Добавим секцию в лог
                $SCHEMA_SQL->appendMlComment('>>> SDK');

                //CREATE CHEMA SCRIPT
                $SCHEMA_SQL->appendFile($SDK->getDirItem(null, 'schema', PsConst::EXT_SQL));

                //OBJECTS SCRIPT
                $SCHEMA_SQL->appendFile($SDK->getDirItem('build', 'objects', PsConst::EXT_SQL));

                //Добавим секцию в лог
                $SCHEMA_SQL->appendMlComment('<<< SDK');
            }

            //CREATE CHEMA SCRIPT
            $SCHEMA_SQL->appendFile($SCHEMA);

            //OBJECTS SCRIPT
            $SCHEMA_SQL->appendFile($DM_BUILD_OBJECTS_SQL->getDi());

            //CREATE USER
            $grant = "grant all on {}.* to '{}'@'{}' identified by '{}';";
            $SCHEMA_SQL->appendMlComment('Create user with grants');
            $SCHEMA_SQL->appendLine(PsStrings::replaceWithBraced($grant, $database, $props->user(), $props->host(), $props->password()));

            /*
             * Мы должны создать тестовую схему, чтобы убедиться, что всё хорошо и сконфигурировать db.ini
             */
            if ($connection == PsConnectionParams::CONN_TEST) {
                /*
                 * На тестовой схеме прогоняем скрипт
                 */
                dolog('Making physical schema {}', $props);

                $rootProps = PsConnectionParams::get(PsConnectionParams::CONN_ROOT);
                dolog('Root connection props: {}', $rootProps);

                $rootProps->execureShell($SCHEMA_SQL->getDi());

                dolog('Connecting to [{}]', $props);
                PsConnectionPool::configure($props);

                $tables = PsTable::all();

                /*
                 * Нам нужно определить новый список таблиц SDK, чтобы по ним 
                 * провести валидацию новых db.ini.
                 * 
                 * Если мы обрабатываем проект, то SDK-шный db.ini уже готов и 
                 * можем положиться на него. Если мы подготавливаем SDK-шный db.ini,
                 * но новый список таблиц возмём из развёрнутой тестовой БД.
                 */
                $sdkTableNames = $scope == ENTITY_SCOPE_SDK ? array_keys($tables) : $SDK->getDirItem('build', 'tables', PsConst::EXT_TXT)->getFileLines();

                if ($scope == ENTITY_SCOPE_PROJ) {
                    //Уберём из всех таблиц - SDK`шные
                    array_remove_keys($tables, $sdkTableNames);
                }

                $scopeTableNames = array_keys($tables);
                sort($scopeTableNames);

                /*
                 * Составим список таблиц.
                 * Он нам особенно не нужен, но всёже будем его формировать для наглядности - какие таблицы добавились.
                 */
                $tablesDi = $DM_BUILD->getDirItem(null, 'tables', PsConst::EXT_TXT)->touch()->putToFile(implode("\n", $scopeTableNames));
                dolog('Tables: {} saved to {}', print_r($scopeTableNames, true), $tablesDi->getAbsPath());

                /*
                 * Для проекта выгружаем данные, хранящиеся в файлах
                 * TODO - возможно имеет смысл выгружать данные в файл
                 */
                /*
                  if ($scope == ENTITY_SCOPE_PROJ) {
                  dolog('Exporting tables data from files');

                  $DM_BUILD->getDirItem('data')->makePath();
                  $AUTO_DATA_SQL = $DM_BUILD_DATA->touch()->getSqlFileBuilder();

                  //Пробегаемся по таблицам
                  foreach (DbIni::getTables() as $tableName) {
                  $table = PsTable::inst($tableName);
                  if ($table->isFilesync()) {
                  $fileData = $table->exportFileAsInsertsSql();
                  if ($fileData) {
                  dolog(' + {}', $tableName);
                  $AUTO_DATA_SQL->appendFile($DM_BUILD->getDirItem('data', $tableName, 'sql')->putToFile($fileData));
                  } else {
                  dolog(' - {}', $tableName);
                  }
                  }
                  }

                  $AUTO_DATA_SQL->save();

                  //Вставим данные в тестовую схему
                  dolog('Inserting data to test schema.');
                  $props->execureShell($DM_BUILD_DATA);
                  }
                 */

                /*
                 * Теперь ещё создадим тестовые объекты.
                 * Мы уверены, что для SDK тестовая часть есть всегда.
                 */

                dolog('Add test part');
                $SCHEMA_SQL->appendMlComment('Test part');

                if ($scope == ENTITY_SCOPE_PROJ) {
                    dolog('+ SDK TEST PART');

                    //Добавим секцию в лог
                    $SCHEMA_SQL->appendMlComment('>>> SDK TEST PART');

                    //CREATE CHEMA SCRIPT
                    $SCHEMA_SQL->appendFile($SDK->getDirItem('sysobjects/test', 'schema.sql'));

                    //ADD TEST DATA
                    $SCHEMA_SQL->appendFile($SDK->getDirItem('sysobjects/test', 'data.sql'));

                    //Добавим секцию в лог
                    $SCHEMA_SQL->appendMlComment('<<< SDK TEST PART');
                }
                $SCHEMA_SQL->appendFile($DM_SYSOBJECTS->getDirItem('test', 'schema.sql'), false);
                $SCHEMA_SQL->appendFile($DM_SYSOBJECTS->getDirItem('test', 'data.sql'), false);
                $SCHEMA_SQL->save();
            }
            #end conn== TEST

            /*
             * Всё, сохраняем финальный скрипт
             */
            //SAVE .sql
            $SCHEMA_SQL->save();
        }
    }

    dolog('Database schemas successfully exported');
}

//Отключаем автоматический коннект на базу, чтоыб наш генератор ничего ненабедокурил на продуктиве
$PS_NO_AUTO_CONNECT = true;
$CALLED_FILE = __FILE__;
$LOGGERS_LIST[] = 'PsConnectionParams';
require_once dirname(dirname(__DIR__)) . '/MainImportProcess.php';
?>