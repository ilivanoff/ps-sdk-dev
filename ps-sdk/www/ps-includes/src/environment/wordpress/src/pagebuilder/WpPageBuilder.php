<?php

/**
 * Класс занимается построением страцины wordpress - вызывается до построения и после него.
 */
final class WpPageBuilder extends EnvPageBuilder {

    protected function preProcessImpl(\PageBuilderContext $builderCtxt, \RequestArrayAdapter $requestParams, \ArrayAdapter $buildParams) {
        $output = "<style> .wp_head_example_xxx { background-color : #f1f1f1; } </style>";
        echo $output;
    }

    protected function postProcessImpl(PageParams $pageParams, \RequestArrayAdapter $requestParams) {
        $SMARTY_PARAMS['JS_DEFS'] = PageBuilder::inst()->buildJsDefs($pageParams);

        $SMARTY_PARAMS_PAGE = $pageParams->getSmartyParams4Resources();

        $SMARTY_PARAMS = array_merge($SMARTY_PARAMS, $SMARTY_PARAMS_PAGE);

        $resources = PSSmarty::template('page/environment/wordpress/wp_page_resources.tpl', $SMARTY_PARAMS)->fetch();
        $resources = trim($resources);

        $this->LOGGER->infoBox('PAGE_RESOURCES', $resources);

        echo PsHtml::div(array(), $resources);
    }

}

?>