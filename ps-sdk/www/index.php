<?php

header('Content-Type: text/html; charset=utf-8');

require_once 'ps-includes/MainImport.php';

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

//print_r(B::$a);

PsLibs::inst();

PsConnectionPool::configure(PsConnectionParams::sdkTest());

ps_admin_on(true);

$a = array('a' => array('x' => 1, 'y' => 2));

print_r($a);
br();
unset($a['c']['x']);
print_r($a);
br();

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


PageBuilder::inst()->buildpage(PB_admin::getIdent());
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