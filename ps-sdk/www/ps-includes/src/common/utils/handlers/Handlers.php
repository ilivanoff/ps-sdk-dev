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
     * Фолдинги
     */

    public function getBubbles() {
        return $this->bubbles;
    }

    public function getPanelProviders() {
        return $this->panels;
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
    public function getFoldingBySmartyPrefix($smartyPrefix, $assert = true) {
        $folding = array_get_value($smartyPrefix, $this->folding2smartyPrefix);
        check_condition(!$assert || $folding, "Не удалось определить фолдинг для smaty-функции с префиксом [$smartyPrefix]");
        return $folding;
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

    public function getTimeLineFolding() {
        $insts = array();
        foreach (FoldedStorageInsts::listFoldings() as $folding) {
            if ($folding instanceof TimeLineFolding) {
                $insts[] = $folding;
            }
        }
        return $insts;
    }

    private static $inst;

    /** @return Handlers */
    public static function getInstance() {
        return self::$inst ? self::$inst : self::$inst = new Handlers();
    }

}

?>
