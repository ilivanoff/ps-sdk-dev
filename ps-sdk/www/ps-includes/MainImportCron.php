<?php

/*
 * Данный файл подключается для выполнения cron процессов. 
 */

include_once 'MainImportAdmin.php';

PsCron::inst()->execute();
?>