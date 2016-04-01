<?php


/**
 * @desc Escapes text for embedding as attribute value
 * 
 * <pre>return htmlspecialchars($str, ENT_QUOTES,<br />    $encoding ? $encoding : Config::$encoding);</pre>
 */
function escapeAttrVal($str, $encoding = '') {
    return htmlspecialchars($str, ENT_QUOTES, $encoding ? $encoding : Config::$encoding);
}

/**
 * @desc Escapes text for showing in html document
 * 
 * <pre>return htmlentities($str, ENT_QUOTES,<br />    $encoding ? $encoding : Config::$encoding);</pre>
 */
function escapeHtml($str, $encoding = '') {
    return htmlentities($str, ENT_QUOTES, $encoding ? $encoding : Config::$encoding);
}

function escapeJSVal($str) {
    $str = str_replace("\n", "\\n", addslashes($str));
    $str = str_replace("\r", "\\r", $str);
    return $str;
}

function ellipsify($str, $maxLen = 100, $html = true) {
    if (strlen($str) >= $maxLen) {
        $str = substr($str, 0, $maxLen) . '...';
    }
    return $html ? escapeHtml($str) : $str;
}
