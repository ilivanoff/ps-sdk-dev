<?php

require_once 'AjaxTools.php';

/**
 * Определим функцию, которая выполнит все действия - не будем лишними переменными засорять глобальное пространство
 */
function psExecuteAjaxAction() {

    /*
     * Название действия должно быть в переменной запроса. Оно же - название класса, который будет выполнен.
     */
    $actionName = RequestArrayAdapter::inst()->str(AJAX_ACTION_PARAM);

    /*
     * Поищем в проектных действиях, они для нас имеют больший приоритет
     */
    $action = Classes::getClassInstance(next_level_dir(PATH_BASE_DIR, PS_DIR_ADDON, 'ajax'), DirManager::DIR_AJAX_ACTIONS, $actionName, AbstractAjaxAction::getClassName(), false);

    /*
     * Поищем объект ajax действия в SDK
     */
    if (!$action) {
        $action = Classes::getClassInstance(__DIR__, DirManager::DIR_AJAX_ACTIONS, $actionName, AbstractAjaxAction::getClassName());
    }


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