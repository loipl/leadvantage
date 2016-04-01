<?php

class Helper_Tooltip {
    public static $texts   = array();
    public static $classes = array();
    public static $id      = 1;

    public static function toolTipImage($id, $text) {
        echo '<img src="/img/icons/help.png" class="tooltip-img" id="tooltip-' . $id . '">';
        self::$texts[] = array('id' => "tooltip-$id", 'text' => $text);
    }
    //--------------------------------------------------------------------------


    public static function toolTipFromWP($id) {
        query_posts("name=$id&post_type=any");
        while (have_posts()) {
            the_post();
            $text = get_the_content(null, true);
            self::toolTipImage($id, $text);
            break;
        }
    }
    //--------------------------------------------------------------------------


    public static function assignTooltipToId($id, $text) {
        self::$texts[] = array('id' => $id, 'text' => $text);
    }
    //--------------------------------------------------------------------------


    public static function assignTooltipToClass($class, $text) {
        self::$classes[] = array('class' => $class, 'text' => $text);
    }
    //--------------------------------------------------------------------------


    public static function jscript() {
        foreach (self::$texts as $arr) {
            $s = escapeJSVal($arr['text']);
            echo "\t$('#$arr[id]').qtip({
\t\tcontent: '$s',
\t\tshow: 'mouseover',
\t\thide: 'mouseout',
\t\tstyle: {
\t\t\tname: 'cream',
\t\t\ttip: 'bottomLeft',
\t\t\tborder: {
\t\t\t\twidth: 2,
\t\t\t\tradius: 4
\t\t\t},
\t\t},
\t\tposition: {
\t\t\tcorner: {
\t\t\t\ttarget: 'topRight',
\t\t\t\ttooltip: 'bottomLeft'
\t\t\t}
\t\t}
\t});\n";
        }
        foreach (self::$classes as $arr) {
            $s = escapeJSVal($arr['text']);
            echo "\t$('.$arr[class]').qtip({
\t\tcontent: '$s',
\t\tshow: 'mouseover',
\t\thide: 'mouseout',
\t\tstyle: {
\t\t\tname: 'cream',
\t\t\ttip: 'bottomLeft',
\t\t\tborder: {
\t\t\t\twidth: 2,
\t\t\t\tradius: 4
\t\t\t},
\t\t},
\t\tposition: {
\t\t\tcorner: {
\t\t\t\ttarget: 'topRight',
\t\t\t\ttooltip: 'bottomLeft'
\t\t\t}
\t\t}
\t});\n";
        }
    }
    //--------------------------------------------------------------------------
}
