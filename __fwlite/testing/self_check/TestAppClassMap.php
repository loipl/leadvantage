<?php

$skipOtherTests = 1;

if (!defined('RUN_ALL_TESTS')) {
    require_once '../../runAllTests.php';
}

/**
 * @desc Makes sure that App::$classMap is consistent with PHP files on disk.
 * Should be part of regular unit test suite.
 */
class TestAppClassMap extends UnitTestCase {

    private $classes2Files = array();


    public function testClassPaths() {
        $this->readClassesFromProjectPHPFiles();

        if ($this->isDifferentFromAppClassMap()) {
            $this->failTestWithCorrectClassPathsAsErrorMessage();
        }
    }
    //--------------------------------------------------------------------------


    private function readClassesFromProjectPHPFiles() {
        $ignoredFiles = array(
            'startup.php',
            'common/_core.php',
        );
        $relativePaths = array();
        foreach (App::$classPaths as $absolutePath) {
            $relativePaths[] = substr($absolutePath, strlen(CFG_FWLITE_HOME));
        }

        $this->classes2Files = Lib::extractClassesAssoc($relativePaths, CFG_FWLITE_HOME, array('.php', '.inc'), $ignoredFiles);
    }
    //--------------------------------------------------------------------------


    private function isDifferentFromAppClassMap() {
        $missingClasses = array_diff_assoc($this->classes2Files, App::$classMap);
        if ($missingClasses) {
            return true;
        }
        $surplusClasses = array_diff_assoc(App::$classMap, $this->classes2Files);
        if ($surplusClasses) {
            return true;
        }
        return false;
    }
    //--------------------------------------------------------------------------


    private function failTestWithCorrectClassPathsAsErrorMessage() {
        $errorMessage = "App::\$classMap should look like this:\n\n" . $this->generateClassFilesMap($this->classes2Files) . "\n";
        $this->fail($errorMessage);
    }
    //--------------------------------------------------------------------------


    private function generateClassFilesMap() {
        $maxClassnameLength = $this->findLongestClassName();

        $result = "    public static \$classMap = array(\n";
        foreach ($this->classes2Files as $className => $fileName) {
            $result .= "        '$className'";
            $result .= str_repeat(' ', $maxClassnameLength - strlen($className));
            $result .= " => '$fileName',\n";
        }
        $result .= "    );\n";

        return $result;
    }
    //--------------------------------------------------------------------------


    private function findLongestClassName() {
        $maxLength = 1;
        foreach (array_keys($this->classes2Files) as $className) {
            $maxLength = max($maxLength, strlen($className));
        }
        return $maxLength;
    }
    //--------------------------------------------------------------------------
}
