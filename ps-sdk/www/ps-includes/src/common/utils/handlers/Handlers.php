<?php

final class Handlers {

    private $postsProcessors = array();
    private $foldings = array();
    private $libs = array();
    private $bubbles = array();
    private $panels = array();
    private $folding2unique = array();
    private $folding2smartyPrefix = array();
    private $folding2classPrefix = array();
    private $postProcessorFoldings = array();
    private $pageFinaliseFoldings = array();

    private function __construct() {
        PsProfiler::inst(__CLASS__)->start(__FUNCTION__);

        //Фолдинги
        $this->foldings[] = PopupPagesManager::inst();
        $this->foldings[] = PluginsManager::inst();
        $this->foldings[] = TimeLineManager::inst();
        $this->foldings[] = UserPointsManager::inst();
        $this->foldings[] = StockManager::inst();
        $this->foldings[] = HelpManager::inst();
        $this->foldings[] = EmailManager::inst();
        $this->foldings[] = PSForm::inst();
        $this->foldings[] = DialogManager::inst();
        //Библиотеки
        $this->foldings[] = PoetsManager::inst();
        $this->foldings[] = ScientistsManager::inst();
        //Админские страницы
        $this->foldings[] = APagesResources::inst();

        /*
         * Выделим различные подклассы фолдингов
         */
        foreach ($this->foldings as $folding) {
            //Фолдинги библиотек
            if ($folding instanceof LibResources) {
                $this->libs[] = $folding;
            }
            //Фолдинги обработчиков постов
            if ($folding instanceof PostFoldedResources) {
                $this->postProcessorFoldings[] = $folding;
            }
            //Фолдинги для баблов
            if ($folding instanceof BubbledFolding) {
                $this->bubbles[] = $folding;
            }
            //Фолдинги, предоставляющие панели
            if ($folding instanceof PanelFolding) {
                $this->panels[] = $folding;
            }
            //Фолдинги, финализирующие контент страницы
            if ($folding instanceof PageFinalizerFolding) {
                $this->pageFinaliseFoldings[] = $folding;
            }
            //Индексированный список фолдингов
            $this->folding2unique[$folding->getUnique()] = $folding;
            //Префиксы smarty к фолдингам
            $this->folding2smartyPrefix[$folding->getSmartyPrefix()] = $folding;
            //Префиксы классов к фолдингам
            if ($folding->getClassPrefix()) {
                $this->folding2classPrefix[$folding->getClassPrefix()] = $folding;
            }
        }

        PsProfiler::inst(__CLASS__)->stop();
    }

    /*
     * Обход всех классов
     */

    private function walk(array $what, $callback) {
        foreach ($what as $ob) {
            call_user_func($callback, $ob);
        }
    }

    /*
     * Фолдинги
     */

    public function getFoldings() {
        return $this->foldings;
    }

    public function getBubbles() {
        return $this->bubbles;
    }

    public function getPanelProviders() {
        return $this->panels;
    }

    public function getFoldingsIndexed() {
        return $this->folding2unique;
    }

    /** @return FoldedResources */
    public function getFoldingByUnique($unique, $assert = true) {
        $folding = array_get_value($unique, $this->folding2unique);
        check_condition(!$assert || $folding, "Фолдинг [$unique] не существует.");
        return $folding;
    }

    /** @return FoldedResources */
    public function getFolding($type, $subtype = null, $assert = true) {
        return $this->getFoldingByUnique(FoldedResources::unique($type, $subtype), $assert);
    }

    /** @return FoldedResources */
    public function getFoldingByClassPrefix($prefix, $assert = true) {
        $folding = array_get_value($prefix, $this->folding2classPrefix);
        check_condition(!$assert || !!$folding, "Фолдинг с префиксом классов [$prefix] не существует.");
        return $folding;
    }

    /** @return FoldedEntity */
    public function getFoldedEntityByUnique($unique, $assert = true) {
        $parts = explode('-', trim($unique));
        $count = count($parts);
        if ($count < 2) {
            check_condition(!$assert, "Некорректный идентификатор сущности фолдинга: [$unique].");
            return null;
        }

        $type = $parts[0];
        $hasSubType = $this->isFoldingHasSubtype($type, false);
        if ($hasSubType === null) {
            //Фолдинга с таким типом вообще не существует
            check_condition(!$assert, "Сущность фолдинга [$unique] не существует.");
            return null;
        }

        if ($hasSubType && ($count == 2)) {
            check_condition(!$assert, "Некорректный идентификатор сущности фолдинга: [$unique].");
            return null;
        }

        $subtype = $hasSubType ? $parts[1] : null;
        $folding = $this->getFolding($type, $subtype, $assert);

        if (!$folding) {
            return null;
        }

        array_shift($parts);
        if ($hasSubType) {
            array_shift($parts);
        }

        //TODO '-' вынести на константы
        $ident = implode('-', $parts);

        return $folding->getFoldedEntity($ident, $assert);
    }

    /** @return FoldedResources */
    public function getFoldingBySmartyPrefix($smartyPrefix, $assert = true) {
        $folding = array_get_value($smartyPrefix, $this->folding2smartyPrefix);
        check_condition(!$assert || $folding, "Не удалось определить фолдинг для smaty-функции с префиксом [$smartyPrefix]");
        return $folding;
    }

    /**
     * Метод проверяет, имеет ли фолдинг с данным типом - подтип.
     * Например, все фолдинги для постов объединены в один фолдинг с типом post и разными подтипами [is, bp, tr].
     */
    public function isFoldingHasSubtype($type, $errIfNotFound = true) {
        /** @var FoldedResources */
        foreach ($this->foldings as $folding) {
            if ($folding->isItByType($type)) {
                return $folding->hasSubType();
            }
        }
        check_condition($errIfNotFound, "Не удалось найти folding с типом [$type]");
        return null;
    }

    /*
     * Библиотеки
     */

    public function getLibManagers() {
        return $this->libs;
    }

    /** @return LibResources */
    public function getLibManager($libType, $assert = true) {
        return $this->getFolding(LibResources::LIB_FOLDING_TYPE, $libType, $assert);
    }

    /**
     * Получение обработчика
     */
    private function getHandlerImpl(array $handlers, $postType, $isEnsure) {
        if (array_key_exists($postType, $handlers)) {
            return $handlers[$postType];
        } else {
            check_condition(!$isEnsure, "Неизвестный тип поста: [$postType]");
        }
        return null;
    }

    /** @return PostsProcessor */
    public function getPostsProcessorByPostType($postType, $isEnsure = true) {
        return $this->getHandlerImpl($this->postsProcessors, $postType, $isEnsure);
    }

    private static $inst;

    /** @return Handlers */
    public static function getInstance() {
        return self::$inst ? self::$inst : self::$inst = new Handlers();
    }

}

?>
