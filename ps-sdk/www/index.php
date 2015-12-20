<?php

header('Content-Type: text/html; charset=utf-8');

require_once 'ps-includes/MainImport.php';

//print_r(ConfigIni::getIni());

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

PsConnectionPool::configure(PsConnectionParams::production());
PsConnectionPool::disconnect();

echo PsConnectionPool::params();

//print_r(PSDB::getRec('select * from blog_post where id_post=1'));
//print_r(InflectsManager::inst()->getInflections('корыто'));

print_r(PsMathRebusSolver::solve('a+df=1aa'));
?>