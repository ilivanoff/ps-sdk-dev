<?php

require_once 'AjaxTools.php';

/**
 * Определим функцию, которая выполнит все действия - не будем лишними переменными засорять глобальное пространство
 */
function psExecuteAjaxAction() {

    /*
     * Получаем объект действия
     */
    $action = Classes::getClassInstance(__DIR__, 'actions', RequestArrayAdapter::inst()->str(AJAX_ACTION_PARAM), AbstractAjaxAction::getClassName());

    /*
     * Проверим, существует ли действие.
     * Для безопасности не будем писать детали обработки.
     */
    if (!$action || !($action instanceof AbstractAjaxAction)) {
        json_error('Действие не опеределено');
    }

    /*
     * Выполняем
     */
    $result = null;

    try {
        $result = $action->execute();
    } catch (Exception $e) {
        $result = $e->getMessage();
    }

    /*
     * Проверим результат
     */
    if ($result instanceof AjaxSuccess) {
        json_success($result->getJsParams());
    } else {
        json_error($result ? $result : 'Ошибка выполнения действия');
    }
}

/**
 * Вызываем
 */
psExecuteAjaxAction();
?>