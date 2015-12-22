<?php

//Подключаем ресурсы проекта
require_once dirname(__DIR__) . '/MainImport.php';

//Поставим признак ajax-запроса
PageContext::inst()->setAjaxContext();

/**
 * Метод вызывается для завершения успешного выполнения Ajax-запроса
 * 
 * @param mixed $data - данные, которые будут возвращены на клиента
 */
function json_success($data) {
    exit(json_encode(array('res' => $data)));
}

/**
 * Метод вызывается для завершения выполнения Ajax-запроса с ошибкой
 * 
 * @param mixed $error - данные, которые будут возвращены на клиента
 */
function json_error($error) {
    exit(json_encode(array('err' => $error)));
}

/**
 * Метод проверяет маркер сессии пользователя
 * 
 * @param string $marker
 */
function check_user_session_marker($marker) {
    if (!AuthManager::checkUserSessionMarker($marker)) {
        json_error('Передан некорректный маркер сессии');
    }
}

/**
 * Выполнение ajax действия
 * 
 * @param AjaxClassProvider $provider
 */
function execute_ajax_action(AbstractAjaxAction $action = null) {
    /* Для безопасности не будем писать детали обработки */
    if (!$action) {
        json_error('Действие не опеределено');
    }

    $result = $action->execute();
    $result = $result ? $result : 'Ошибка выполнения действия';

    if ($result instanceof AjaxSuccess) {
        json_success($result->getJsParams());
    }
    json_error($result);
}

?>