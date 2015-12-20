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

echo PsConnectionPool::params();

print_r(PSDB::getArray('select * from blog_post'));

?>