<?php

/**
 * Класс хранит в себе все маппинги системы, возвращая объекты типа {@link MappingClient},
 * дающие доступ только к клиентским методам.
 *
 * @author azazello
 */
class Mappings {

    /**
     * Маппинг дополнительных плагинов предпросмотра постов на рубрики
     * 
     * @param str $postType - тип поста
     * @return MappingClient
     */
    public static final function RUBRIC_2_SCCONTROLLERS($postType) {
        $rp = Handlers::getInstance()->getRubricsProcessorByPostType($postType);
        $lunique = $rp->getRubricsFolding()->getUnique();
        $runique = ShowcasesCtrlManager::inst()->getUnique();
        return Mapping::inst(//
                        MapSrcFoldingDb::inst(array('unique' => $lunique), __FUNCTION__), //
                        MapSrcFoldingEnts::inst(array('unique' => $runique), __FUNCTION__), //
                        'Привязка вариантов предпросмотра постов к ' . ps_strtolower($rp->rubricTitle(null, 3))
        );
    }

    /**
     * Маппинг дополнительных плагинов предпросмотра постов на рубрики
     * 
     * @param str $postType - тип поста
     * @return MappingClient
     */
    public static final function RECOMMENDED_POSTS($postType) {
        $pp = Handlers::getInstance()->getPostsProcessorByPostType($postType);
        $lunique = $pp->getFolding()->getUnique();
        return Mapping::inst(//
                        MapSrcFoldingDb::inst(array('unique' => $lunique), __FUNCTION__), //
                        MapSrcAllPosts::inst(array(), __FUNCTION__), //
                        'Рекомендованные посты для ' . ps_strtolower($pp->postTitle(null, 2))
        );
    }

    /**
     * Меод должен вернуть все возможные маппинги.
     * Нужно для показа в админке
     */
    protected final function allMappings() {
        $mappings = array();
        foreach (Handlers::getInstance()->getRubricsProcessors() as $postType => $rp) {
            $mappings[] = self::RUBRIC_2_SCCONTROLLERS($postType);
        }
        foreach (Handlers::getInstance()->getPostsProcessors() as $postType => $pp) {
            $mappings[] = self::RECOMMENDED_POSTS($postType);
        }
        return $mappings;
    }

}

?>