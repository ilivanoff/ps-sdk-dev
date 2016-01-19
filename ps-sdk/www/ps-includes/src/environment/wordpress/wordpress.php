<?php

/*
 * wordpress - Классы окружения, когда ps-sdk работает, как wordpress plugin.
 * Нужно учитывать, что ps-sdk может быть вызван после подключения классов 
 * wordpress (например при построении страницы, когда подключаются плагины) или до этого
 */

if (PsUtil::isWordPress()) {
    /*
     * Мы уже работаем как часть WordPress, ничего делать не нужно.
     * Классы src подключатся автоматически.
     */
    $LOGGER->info('WordPress is already loaded, skip including...');
} else {
    /*
     * Нас вызвали раньше wordpress. Это может быть процесс, или ajax, или ещё что-либо.
     * Подключим ядро WordPress.
     */

    if (PsContext::isAjax()) {
        $wpInc = PATH_BASE_DIR . 'wp-load.php';
        $LOGGER->info('WordPress is not loaded yet, include wp core [{}]', $wpInc);

        /*
         * Если файл есть - подключаем, нет - ругаемся.
         */
        if (is_file($wpInc)) {
            require_once $wpInc;
        } else {
            PsUtil::raise('WordPress core file not found, environment cannot be loaded.');
        }
    } else {
        $LOGGER->info('WordPress core will not be included for context [{}]', PsContext::describe());
    }
}
?>