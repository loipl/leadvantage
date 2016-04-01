<?php

class PageFragment_PagerX extends PageFragment_Pager {

    protected $wrapperClass     = 'pager-wrapper';

    /**
     * @return PageFragment_PagerX
     */
    public function setCssClasses($current = '', $link = '', $last = '', $wrapper = '') {
        $this->spanClass    = $current;
        $this->linkClass    = $link;
        $this->lastClass    = $last;
        return $this;
    }
    //--------------------------------------------------------------------------


    protected function linkForPage($page, $text, $isLast = false) {
        $onPage = $page == $this->pageNr;
        $html = $onPage ? '<span' : '<a';
        $class = (($isLast && $this->lastClass) ? "$this->lastClass" : '');
        $c = is_numeric($text) ? '' : ' ' . $this->wrapperClass;
        if ($this->spanClass && $onPage) {
            $class .= " $this->spanClass$c";
        } elseif($this->linkClass) {
            $class .= " $this->linkClass$c";
        } else {
            $class .= $c;
        }
        if ($class) {
            $html .= " class=\"" . trim($class) . "\"";
        }
        if (!$onPage) {
            $html .= ' href="' . $this->urlForPage($page) . '"';
        }
        $html .= '>' . $text . ($onPage ? '</span>' : '</a>');

        return $html;
    }
    //--------------------------------------------------------------------------
}