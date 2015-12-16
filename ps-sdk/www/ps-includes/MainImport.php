<?php

//Засекаем время начала выполнения скрипта. Далее будет использовано в профайлере.
define('SCRIPT_EXECUTION_START', microtime(true));

//Корневая папка (DocumentRoot) - C:\WEB\postupayu.ru\www
define('PATH_BASE_DIR', dirname(__DIR__));

//Название папки с включениями sdk
define('PS_SDK_DIR_INCLUDES', 'ps-includes');

//Проверим, что данный файл лежит в папке с включениями
if (PS_SDK_DIR_INCLUDES != basename(__DIR__)) {
    die('Invalid ps-sdk includes dir: ' . basename(__DIR__));
}

//Стартуем сессию
if (!isset($_SESSION)) {
    session_start();
}

//Подключим загрузчик базовых классов
include_once __DIR__ . '/kitcore/PsCoreIncluder.php';
PsCoreIncluder::inst()->includeCore();

//Зарегистрируем наш обработчие для php ошибок
//ExceptionHandler::register4errors();
/*
 * 
 */
//Подключим обработчик эксепшенов. Позднее мы подключим "красивый" обработчик ошибок.
//ExceptionHandler::register();
/*
 * 
 */
//Подключим загрузчик служебных классов
Autoload::inst()->register();

PSForm::inst();

/*
  //Подключаемся к продуктиву, если автоконнект разрещён
  if (!isset($PS_NO_AUTO_CONNECT) || !$PS_NO_AUTO_CONNECT) {
  PsConnectionPool::configure(PsConnectionParams::production());
  }

  //Зарегистрируем функцию, подключающую админские ресурсы
  function ps_admin_on($force = false) {
  if ($force || AuthManager::isAuthorizedAsAdmin()) {
  Autoload::inst()->registerAdminBaseDir();
  }
  }

  //Ну и сразу попытаемся подключить админские ресурсы
  ps_admin_on();

  //Получим экземпляр профайлера, чтобы подписаться на PsShotdown, если профилирование включено
  PsProfiler::inst()->add('ScriptInit', Secundomer::inst()->add(1, microtime(true) - SCRIPT_EXECUTION_START));
 */
?>