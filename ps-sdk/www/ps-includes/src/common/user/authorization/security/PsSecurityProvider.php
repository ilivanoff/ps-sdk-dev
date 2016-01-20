<?php

/**
 * Базовый интерфейс для всех провайдеров безопасности
 * Мы постараемся не реализовывать авторизацию средствами SDK, а переложить это на плечи внешнего кода, например wordpress.
 * 
 * @author azaz
 */
interface PsSecurityProvider {

    /**
     * Метод получает код пользователя
     */
    public function getUserId();

    /**
     * Метод проверяет, авторизован ли пользователь
     */
    public function isAuthorized();

    /**
     * Метод проверяет, авторизован ли пользователь как администратор
     */
    public function isAuthorizedAsAdmin();
}

?>