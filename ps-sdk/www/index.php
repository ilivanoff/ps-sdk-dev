<?php

header('Content-Type: text/html; charset=utf-8');

//define('LOGGING_ENABLED', false);
//$LOGGING_ENABLED = true;
//$LOGGERS_LIST = array('');

require_once 'ps-includes/MainImportAdmin.php';

//print_r(ConfigIni::smartyPlugins());
//print_r(ConfigIni::smartyTemplates());
//print_r(FoldingsIni::foldingsRel());
//print_r(FoldingsIni::foldingsAbs());
//print_r(ConfigIni::ajaxActionsAbs('admin'));
//echo DirManager::inst('/../ps-uploads')->makePath();
//echo ConfigIni::uploadsDirRel();
//PsMailSender::fastSend('Hello', 'Body', 'azazello85@mail.ru');

var_dump(ConfigIni::userActivityInterval());

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

class X {

    function test(array $a = null) {
        print_r($a);
        br();
        print_r(is_array($a));
    }

}

$x = new X();
$x->test(null);
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