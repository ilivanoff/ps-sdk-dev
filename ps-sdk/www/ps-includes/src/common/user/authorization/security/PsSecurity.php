<?php

/**
 * Задача класса - определить и создать экземпляр провайдера, занимающегося вопросами авторизации.
 * 
 * @author azaz
 */
final class PsSecurity {

    const BASE_CLASS = 'PsSecurityProvider';
    const WP_CLASS = 'PsWpSecurityProvider';

    /** @var PsSecurityProvider */
    private static $provider;

    /**
     * Метод возвращает экземпляр класса, отвечающего за вопросы авторизации.
     * Для переопределения этого класса, на уровне проектного config.ini
     * должен быть задан другой класс.
     * 
     * Это позволит:
     * 1. Использовать сторонний механизм авторизации и регистрации пользователей
     * 
     * @return PsSecurityProvider
     */
    public static final function provider() {
        if (isset(self::$provider)) {
            return self::$provider; //----
        }

        /*
         * Получим название класса
         */
        $class = ConfigIni::authEngine();

        /*
         * Класс провайдера может быть не задан. В таком случае определим его автоматически.
         */
        /*
          if (!$class && PsUtil::isWordPress()) {
          if (class_exists(self::WP_CLASS)) {
          $class = self::WP_CLASS;
          } else {
          return PsUtil::raise('Cannot find {} class', self::WP_CLASS);
          }
          }
         */

        /*
         * До сих пор не нашли класс? Ошибка!
         */
        if (!$class) {
            return PsUtil::raise('Cannot define ps security provider');
        }

        /*
         * Поищем класс
         */
        $classPath = Autoload::inst()->getClassPath($class);
        if (!PsCheck::isNotEmptyString($classPath)) {
            return PsUtil::raise('Не удалось найти класс провайдера безопасности [{}]', $class);
        }

        /*
         * Указанный класс должен быть базового
         */
        if (!PsUtil::isInstanceOf($class, self::BASE_CLASS)) {
            return PsUtil::raise('Указанный провайдер безопасности [{}] не является наследником класса [{}]', $class, self::BASE_CLASS);
        }

        self::$provider = new $class();

        $LOGGER = PsLogger::inst($class);
        if ($LOGGER->isEnabled()) {
            $LOGGER->info('Using provider: {}', $class);
            $LOGGER->info('Is authorized: ? {}', var_export(self::$provider->isAuthorized(), true));
            $LOGGER->info('Is authorized as admin: ? {}', var_export(self::$provider->isAuthorizedAsAdmin(), true));
            $LOGGER->info('User ID: {}', self::$provider->getUserId());
        }

        return self::$provider; //---
    }

    /**
     * Конструктор может быть переопределён и в нём должна быть выполнена вся работа
     */
    private function __construct() {
        
    }

}
