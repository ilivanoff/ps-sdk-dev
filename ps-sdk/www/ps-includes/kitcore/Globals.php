<?php

/*
 * Время жизни кешей (в минутах)
 */
define('CACHE_LITE_LIFE_TIME', 720);

/*
 * Включено ли логирование
 */
define('LOGGING_ENABLED', true);

/*
 * Включено ли профилирование
 */
define('PROFILING_ENABLED', true);

/*
 * Максимальный размер файла аудита (в мегабайтах)
 */
define('PROFILING_MAX_FILE_SIZE', 1);

/*
 * Максимальное кол-во дампов файлов последних эксепшенов
 */
define('EXCEPTION_MAX_FILES_COUNT', 10);

/*
 * Максимальное кол-во хранимых последних отправленных email
 */
define('EMAILS_MAX_FILES_COUNT', 10);

/*
 * Интервал между действиями пользователя (в секундах)
 */
define('ACTIVITY_INTERVAL', 0);

/*
 * Заменяем ли формулы на картинки
 */
define('REPLACE_FORMULES_WITH_IMG', true);

/*
 * Заменять ли формулы на спрайты
 */
define('REPLACE_FORMULES_WITH_SPRITES', true);

/*
 * Нормализация страницы (удаление двойных пробелов)
 */
define('NORMALIZE_PAGE', false);

/*
 * Включение режима Production
 */
define('PS_PRODUCTION', false);

/*
 * Адрес SMTP сервера
 */
define('SMTP_HOST', 'smtp.yandex.ru');

/*
 * Имя для доступа к SMTP
 */
define('SMTP_USERNAME', 'postupayu@yandex.ru');

/*
 * Пароль для доступа к SMTP
 */
define('SMTP_PASSWORD', 'Anastasiya!1997');
?>