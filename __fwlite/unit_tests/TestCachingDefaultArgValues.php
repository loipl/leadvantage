<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../runAllTests.php';
}

class TestCachingDefaultArgValues extends UnitTestCase {
    private $cachedFunctions  = array();
    private $defaultArguments = array();


    public function testDefaultArgValuesInDbCacheWrapper() {
        foreach (App::getClassMap() as $className => $path) {
            $defaultParams = array();
            if (strpos($path, 'classes/Model/') !== 0) {
                continue;
            }
            $obj = new $className;
            if ($obj instanceof CrudModelCaching) {
                $this->readValuesForModel($obj);
            }
        }

        $this->removeDefaultArgumentsForClassesThatHaveNoCachedFunctions();
        ksort($this->defaultArguments);
        ksort($this->cachedFunctions);

        $errorMessage = '';
        if (!$this->arraysIdentical($this->cachedFunctions, DbCache_Wrapper::$cachedFunctions)) {
            $errorMessage .= "\n\npublic static \$cachedFunctions = " . var_export($this->cachedFunctions, true) . ";";
        }
        if (!$this->arraysIdentical($this->defaultArguments, DbCache_Wrapper::$defaultArguments)) {
            $errorMessage .= "\n\npublic static \$defaultArguments = " . var_export($this->defaultArguments, true) . ";";
        }

        while (strpos($errorMessage, "=>  ") !== false) {
            $errorMessage = str_replace("=>  " , "=> ", $errorMessage);
        }
        $errorMessage = str_replace("=>\n" , "=> ", $errorMessage);
        $errorMessage = str_replace("=> \n" , "=> ", $errorMessage);
        while (strpos($errorMessage, "=>  ") !== false) {
            $errorMessage = str_replace("=>  " , "=> ", $errorMessage);
        }

        $errorMessage = preg_replace("/array \\(\n[\\s]{0,}\\)/", ' array()', $errorMessage);
        if ($errorMessage) {
            $this->fail("DbCache_Wrapper should have this:\n\n" . $this->correctIndentation($errorMessage) . "\n\n\n");
        }
    }
    //--------------------------------------------------------------------------


    private function readValuesForModel(CrudModelCaching $model) {
        $refl = new ReflectionClass($model);
        $methods = $refl->getMethods();
        foreach ($methods as $m) {
            if (($m->getName() == '__construct') || ($m->isPrivate())) {
                continue;
                $m = new ReflectionMethod;
            }

            if (($m->getDeclaringClass()->getName() != $refl->getName()) && ($m->getDeclaringClass()->getName() != 'CrudModelCaching')) {
                continue;
            }

            $s = $m->getDocComment();
            if (strpos($s, '@Cached') !== false) {
                $this->cachedFunctions[$refl->getName()][$m->getName()] = 1;
            }

            $params = $m->getParameters();
            $defArgs = array();
            foreach ($params as $nr => /* @var $p ReflectionParameter */$p) {
                if ($p->isOptional()) {
                    $defArgs[$nr] = $p->getDefaultValue();
                }
                if ($p->isPassedByReference() && $m->getName() != 'attachSettingsForGroup') {
                    $this->fail("Do not use by-reference parameter in model classes, caching cannot pass by reference: " . get_class($model) . '::' . $m->getName() . '()');
                }
            }
            if ($defArgs) {
                $this->defaultArguments[$refl->getName()][$m->getName()] = $defArgs;
            }
        }

        if (!empty($this->defaultArguments[$refl->getName()])) {
            ksort($this->defaultArguments[$refl->getName()]);
        }
        if (!empty($this->cachedFunctions[$refl->getName()])) {
            ksort($this->cachedFunctions[$refl->getName()]);
        }
    }
    //--------------------------------------------------------------------------


    private function removeDefaultArgumentsForClassesThatHaveNoCachedFunctions() {
        foreach (array_keys($this->defaultArguments) as $k) {
            if (empty($this->cachedFunctions[$k])) {
                unset($this->defaultArguments[$k]);
            }
        }
    }
    //--------------------------------------------------------------------------


    private function correctIndentation($errorMessage) {
        $lines = explode("\n", $errorMessage);
        foreach ($lines as & $s) {
            $len = 0;
            for($i = 0; $i < strlen($s); $i++) {
                if ($s[$i] == ' ') {
                    $len++;
                } else {
                    break;
                }
            }
            $s = str_repeat(' ', 4 + $len * 2) . trim($s);
        }

        return implode("\n", $lines);
    }
    //--------------------------------------------------------------------------


    private function arraysIdentical(array $one, array $two) {
        return serialize($one) == serialize($two);
    }
    //--------------------------------------------------------------------------
}
