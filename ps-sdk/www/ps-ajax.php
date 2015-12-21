<?php

/**
 * Основной скрипт, вызываемый для выполнения всех ajax действий
 */
require_once __DIR__ . '/ps-includes/ajax/MainImport.php';

execute_ajax_action(AjaxActions::getAction());
?>