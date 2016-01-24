<?php

/**
 * Класс хранит в себе все маппинги системы, возвращая объекты типа {@link MappingClient},
 * дающие доступ только к клиентским методам.
 *
 * @author azazello
 */
class Mappings {

    /**
     * Маппинг фолдингов к сущностям БД
     * 
     * @param str $postType - тип поста
     * @return MappingClient
     */
    public static final function FOLDINGS2DB() {
        return Mapping::inst(//
                        MapSrcAllFoldings::inst(__FUNCTION__), //
                        MapSrcDbEntitys::inst(__FUNCTION__), //
                        'Маппинг фолдингов к сущностям БД'//
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
                        MapSrcFoldingDb::inst(__FUNCTION__, array('unique' => $lunique)), //
                        MapSrcAllPosts::inst(__FUNCTION__), //
                        'Рекомендованные посты для ' . ps_strtolower($pp->postTitle(null, 2))
        );
    }

}

?>