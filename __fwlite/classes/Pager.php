<?php

class Pager extends PageFragment_PagerX {
    protected $lastClass = 'last';


    public static function showPager($total, $perPage = false, $getName = false, $viewFile = '') {
        $me = new self();
        $me->init($total, $perPage, $getName);
        $me->setViewFile($viewFile ? $viewFile : Controller::getViewFileFor('PageFragment_PagerX'));
        $me->output();

        $start = (1 + ($me->pageNr - 1) * $me->perPage);
        if ($start > $total) {
            return;
        }

        $end = min($me->total, $me->pageNr * $me->perPage);
        echo "<div style=\"display: inline;\">Showing $start &ndash; $end of $me->total total.</div>";
    }
    //--------------------------------------------------------------------------
}
