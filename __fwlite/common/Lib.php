<?php

class Lib {

    public static function var_export($var, $return = false) {
        $s = "<?php\n\n" . var_export($var, true) . ";\n\n?>";
        return highlight_string($s, $return);
    }
    //--------------------------------------------------------------------------


    public static function var_dump($var) {
        echo "<pre>";
        foreach (func_get_args() as $r) {
            var_dump($r);
        }
        echo "</pre>";
    }
    //--------------------------------------------------------------------------


    public static function toSlug($text) {
        $s = str_replace("'", '', $text);
        $s = preg_replace('/[^\\w]{1,}/', '-', strtolower($s));
        if (substr($s, 0, 1) == '-') {
            $s = substr($s, 1);
        }
        if (substr($s, -1) == '-') {
            $s = substr($s, 0, -1);
        }
        return $s;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Looks at all incoming headers and figures out user's real IP address,
     * and puts it in $_SERVER['REMOTE_ADDR']
     */
    public static function getRealIP() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0)) {
            if (strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.') && '.' . strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') == strrchr($_SERVER['HTTP_CLIENT_IP'], '.') && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0)) {
                $_SERVER['REMOTE_ADDR'] = implode('.', array_reverse(explode('.', $_SERVER['HTTP_CLIENT_IP'])));
            } else {
                $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
        {
            if (strtok($_SERVER['REMOTE_ADDR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.')) {
                $_SERVER['REMOTE_ADDR'] = implode('.', array_reverse(explode('.', $_SERVER['HTTP_CLIENT_IP'])));
            } else {
                $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
            }
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $ips = array_reverse(explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']));
                foreach ($ips as $i => $ip)  {
                    if (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $ip) != 0 && preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) == 0) {
                        continue;
                    }
                    $_SERVER['REMOTE_ADDR'] = trim($ip);
                    break;
                }
            } elseif (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } elseif (!isset($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = '';
        }
    }
    //--------------------------------------------------------------------------


    public static function removeIntKeys(array & $arr) {
        foreach (array_keys($arr) as $k) {
            if (is_int($k)) {
                unset($arr[$k]);
            }
        }
    }
    //--------------------------------------------------------------------------


    public static function convertUSDateToSQLDate($usDate, $separator = '/') {
        $arr = explode($separator, $usDate);
        if (sizeof($arr) != 3) {
            throw new EExplainableError("Invalid date");
        }
        if (!checkdate((int)$arr[0], (int)$arr[1], (int)$arr[2])) {
            throw new EExplainableError("Invalid date");
        }
        return sprintf("%04d-%02d-%02d", $arr[2], $arr[0], $arr[1]);
    }
    //--------------------------------------------------------------------------


    public static function listFiles($folder, $recursive = true) {
        $list = array();
        $dirs = array();

        $d    = dir($folder);
        while (false !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $filename = $folder . $entry;
            if (is_dir($filename)) {
                if ($recursive) {
                    $dirs[] = $filename . '/';
                }
                continue;
            }
            $list[] = $filename;
        }
        $d->close();

        foreach ($dirs as $dir) {
            $list = array_merge($list, self::listFiles($dir, $recursive));
        }
        return $list;
    }
    //--------------------------------------------------------------------------


    public static function extractClassesAssoc(array $folders, $root, array $extensions = array('.php', '.inc'), $excludeFiles = array()) {
        $files = array();
        $root = ensureTrailingSlash($root);
        foreach ($folders as $oneFolder) {
            foreach (self::listFiles(ensureTrailingSlash($root . $oneFolder)) as $fn) {
                foreach ($extensions as $ext) {
                    if (substr($fn, -strlen($ext)) == $ext) {
                        $files[] = $fn;
                    }
                }
            }
        }
        $classes = array();
        foreach ($files as $fn) {
            $fileName = $fn;
            if (strpos($fileName, $root) === 0) {
                $fileName = substr($fileName, strlen($root));
            }
            if (in_array($fileName, $excludeFiles)) {
                continue;
            }
            $arr = file($fn);
            foreach ($arr as $line) {
                preg_match_all('/^(?:abstract){0,1}[\\s]{0,}class[\\s]{1,}([\\w]{1,})[\\s]{0,}/', $line, $matches);
                if (!empty($matches[1][0])) {
                    $classes[$matches[1][0]] = $fileName;
                }
            }
        }
        ksort($classes);
        return $classes;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc PHP equivalent of MySQL TO_DAYS() function
     */
    public static function TO_DAYS($date) {
        if (is_numeric($date)) {
            $res = 719528 + (int) ($date / 86400);
        } else {
            $TZ = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $res = 719528 + (int) (strtotime($date) / 86400);
            date_default_timezone_set($TZ);
        }
        return $res;
    }
    //--------------------------------------------------------------------------
}
