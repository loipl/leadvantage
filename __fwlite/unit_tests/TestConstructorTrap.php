<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestConstructorTrap extends UnitTestCase {

    public function test() {
        $folder = dirname(__FILE__) . '/';
        $folder = substr($folder, strlen(CFG_FWLITE_HOME));
        $classes2Files = Lib::extractClassesAssoc(array($folder), CFG_FWLITE_HOME, array('.php'));
        foreach ($classes2Files as $class => $file) {
            require_once CFG_FWLITE_HOME . $file;
            $refl = new ReflectionClass($class);
            if (!$refl->isSubclassOf('UnitTestCase')) {
                continue;
            }
            $methods = $refl->getMethods();
            foreach ($methods as $m) {
                /* @var $m ReflectionMethod */
                if (($m->getName() != '__construct') && (strtolower($m->getName()) == strtolower($class))) {
                    $this->fail("\n\n\tClass $class in file $file has function " . $m->getName() . "() which could be treated as constructor and not executed in unit test. Use __construct() for constructors.\n\n");
                }
            }
        }
    }
    //--------------------------------------------------------------------------
}
