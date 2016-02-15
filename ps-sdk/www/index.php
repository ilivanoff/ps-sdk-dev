<?php

header('Content-Type: text/html; charset=utf-8');

//define('LOGGING_ENABLED', false);
//$LOGGING_ENABLED = true;
//$LOGGERS_LIST = array('');

require_once 'ps-includes/MainImportAdmin.php';

die();

ExceptionHandler::registerPretty();

//print_r(ConfigIni::smartyPlugins());
//print_r(ConfigIni::smartyTemplates());
//print_r(FoldingsIni::foldingsRel());
//print_r(FoldingsIni::foldingsAbs());
//print_r(ConfigIni::ajaxActionsAbs('admin'));
//echo DirManager::inst('/../ps-uploads')->makePath();
//echo ConfigIni::uploadsDirRel();
//PsMailSender::fastSend('Hello', 'Body', 'azazello85@mail.ru');
//var_dump(DirItem::inst(ConfigIni::globalsFilePath())->getModificationTime());


PSDB::getArray('select * from crop_cell');

die;

$data = "data:image/png;base64,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";
$data = explode(',', $data, 2)[1];
$unencoded = base64_decode($data);

$im = imagecreatefromstring($unencoded);
if ($im !== false) {
    header('Content-Type: image/png');
    imagepng($im);
    imagedestroy($im);
}

die;

$client_id = '3485070'; // ID приложения
$client_secret = 'lYjfUZwZmlJJlFIqQFAj'; // Защищённый ключ
$redirect_uri = 'http://localhost/vk-auth'; // Адрес сайта

$url = 'http://oauth.vk.com/authorize';

$params = array(
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code'
);


echo urlencode(http_build_query($params));

//echo PsEnvironment::isIncluded();
//print_r(PsMathRebusSolver::solve('драма+драма=театр'));

die;

PSSmarty::smarty();

echo (new SmartyFunctions())->psctrl(array());


die;

print_r(FoldedStorage::listFoldingUniques());

print_r(FoldedStorageInsts::listFoldingUniques());

die;

var_dump(ConfigIni::projectSrcAdminDir());
br();
var_dump(ConfigIni::projectSrcCommonDir());
br();
var_dump(ConfigIni::projectGlobalsFilePath());
br();

new YouTubePluginAdmin();


die;

ExceptionHandler::dumpError(new Exception('XXXX'), 'Additional info');

die;

class X {

    protected function __construct() {
        echo 'X';
    }

}

class Y extends X {

    function __construct() {
        //parent::__construct();
    }

}

$y = new Y();


echo PsSecurity::isBasic();
die;

ExceptionHandler::registerPretty();

print_r(PopupPagesManager::inst()->getPagesList());

die('');

echo PluginsManager::inst()->getAutogenDi('advgraph', array('x', 'y', 'z'), null, 'temp', 'php')->touch();
die;

echo TestUtils::testProductivity(function() {
    FoldedStorage::getEntities('lib-s');
}, 200);

br();
echo FoldedStorage::extractInfoFromClassName('PL_slib', $classPrefix, $entity);
br();
echo $classPrefix;
br();
echo $entity;

die;

$prefix = 'PL_math';

echo preg_match('/^[A-Z]+\_/', $prefix, $matches);
br();
print_r($matches);

die;

FoldedStorage::extractFoldedTypeAndSubtype('lib-xxxx-', $type, $subtype);

echo "$type, $subtype";

die;

class A {

    public static $a = array();

    public static function test() {
        self::$a[] = '1';
    }

    final function __construct($a) {
        
    }

}

class B extends A {
    
}

A::test();
A::test();
B::test();

ExceptionHandler::registerPretty();

//print_r(B::$a);

PsLibs::inst();

PsConnectionPool::configure(PsConnectionParams::sdkTest());

ps_admin_on(true);

$a = array('a' => array('x' => 1, 'y' => 2));
$key = 'M';
$group = 'default';
$group2 = 'default2';

PSCache::inst()->saveToCache($a, $key, $group, 'xxx');
PSCache::inst()->saveToCache(array('a' => 1), '$key', '$group', 'xxx1');

die;

echo TestUtils::testProductivity(function() {
    PSCache::inst()->getFromCache('$key', '$group', null, 'xxx1');
});

print_r(PSCache::inst()->getFromCache($key, $group, array('a'), 'xxx1'));

die;

print_r(PSCache::inst()->saveToCache($a, $key, $group));
print_r(PSCache::inst()->getFromCache($key, $group));

PSCache::inst()->removeFromCache($key, $group);
print_r(PSCache::inst()->getFromCache($key, $group));


die;

/*

  echo PsConnectionPool::params();
 */
//print_r(PSDB::getRec('select * from blog_post where id_post=1'));
//print_r(InflectsManager::inst()->getInflections('корыто'));
//print_r(PsMathRebusSolver::solve('a+df=1aa'));
//print_r(PsTable::inst('users')->getColumns());
//echo TexImager::inst()->getImgDi('\alpha');
//echo TexImager::inst()->getImgDi('\sqrt{4}=2');
//$sprite = CssSprite::inst(DirItem::inst('ps-content/sprites/ico'));
//echo $sprite->getSpriteSpan('calendar');
//print_r(ConfigIni::cronProcesses());
//$tpl = PSSmarty::template(DirItem::inst(__DIR__, 'mytpl', PsConst::EXT_TPL));
$tpl = PSSmarty::template('common/citatas.tpl', array('c_body' => 'My body'));
$tpl->display();
$tpl = PSSmarty::template('myhelp/bubble.tpl', array('c_body' => 'My body'));
$tpl->display();
?>