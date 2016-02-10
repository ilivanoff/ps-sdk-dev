<?php

header('Content-Type: text/html; charset=utf-8');

require_once 'ps-includes/MainImport.php';

header('Content-Type: text/html; charset=utf-8');
ExceptionHandler::registerPretty();

$SMARTY_PARAMS['JS_DEFS'] = PageBuilder::inst()->buildJsDefs();
$PARAMS['RESOURCES'] = PSSmarty::template('crop/page_resources.tpl', $SMARTY_PARAMS)->fetch();
$PARAMS['CONTENT'] = PSSmarty::template('crop/page.tpl')->fetch();
$PARAMS['TITLE'] = 'Мои мысли';
PSSmarty::template('crop/page_pattern.tpl', $PARAMS)->display();
?>