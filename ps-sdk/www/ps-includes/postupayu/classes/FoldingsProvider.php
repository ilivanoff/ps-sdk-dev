<?php

/**
 * Хранилище фолдингов SDK.
 * Если этот файл будет переименован, нужно его записать в config-sdk.ini
 *
 * @author azazello
 */
final class FoldingsProvider extends FoldingsProviderAbstract {

    /**
     * Список фолдингов
     */
    public static function listFoldings() {
        //Фолдинги
        $foldings[] = PopupPagesManager::inst();
        $foldings[] = PluginsManager::inst();
        $foldings[] = TimeLineManager::inst();
        $foldings[] = TemplateMessages::inst();
        $foldings[] = UserPointsManager::inst();
        $foldings[] = StockManager::inst();
        $foldings[] = HelpManager::inst();
        $foldings[] = EmailManager::inst();
        $foldings[] = PSForm::inst();
        $foldings[] = DialogManager::inst();
        //Библиотеки
        $foldings[] = PoetsManager::inst();
        $foldings[] = ScientistsManager::inst();
        //Админские страницы
        $foldings[] = APagesResources::inst();
        //Построитель страниц
        $foldings[] = PageBuilder::inst();

        //Все фолдинги системы
        return $foldings;
    }

}

?>