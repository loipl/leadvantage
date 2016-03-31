<?php

class PageFragment_Pager extends PageFragment {
    protected $pageNr  = 1;
    protected $perPage = 25;
    protected $total   = 0;
    protected $getName = 'page';

    protected $overridePageNr = false;

    protected $spanClass     = '';
    protected $linkClass     = '';
    protected $lastClass     = '';
    protected $skipGet       = array();
    protected $overrideGet   = array();

    protected $around        = 3;
    protected $showFirstLast = true;
    protected $showPrevNext  = true;

    protected $urlStart      = '';


    /**
     * @return PageFragment_Pager
     */
    public function init($total, $perPage = false, $getName = false) {
        $this->total = $total;
        if ($perPage !== false) {
            $this->perPage = $perPage;
        }
        if ($getName !== false) {
            $this->getName = $getName;
        }
        $this->pageNr = isset($_GET[$this->getName]) ? max(1, (int)$_GET[$this->getName]) : 1;
        return $this;
    }
    //--------------------------------------------------------------------------


    /**
     * @param Controller $controller
     *
     * @return PageFragment_Box
     */
    public static function create(Controller $controller = null) {
        die;
    }
    //--------------------------------------------------------------------------


    /**
     * @return PageFragment_Pager
     */
    public function setCssClasses($current = '', $link = '', $last = '') {
        $this->spanClass = $current;
        $this->linkClass = $link;
        $this->lastClass = $last;
        return $this;
    }
    //--------------------------------------------------------------------------


    protected function urlForPage($page) {
        $arr = $_GET;
        foreach ($this->skipGet as $k) {
            unset($arr[$k]);
        }
        foreach ($this->overrideGet as $k => $v) {
            $arr[$k] = $v;
        }
        $arr[$this->getName] = $page;
        return $this->urlStart . '?' . http_build_query($arr);
    }
    //--------------------------------------------------------------------------


    protected function linkForPage($page, $text, $isLast = false) {
        $onPage = $page == $this->pageNr;
        $html = $onPage ? '<span' : '<a';
        $class = (($isLast && $this->lastClass) ? "$this->lastClass" : '');
        if ($this->spanClass && $onPage) {
            $class .= " $this->spanClass";
        } elseif($this->linkClass) {
            $class .= " $this->linkClass";
        }
        if ($class) {
            $html .= " class=\"$class\"";
        }
        if (!$onPage) {
            $html .= ' href="' . $this->urlForPage($page) . '"';
        }
        $html .= '>' . $text . ($onPage ? '</span>' : '</a>');

        return $html;
    }
    //--------------------------------------------------------------------------


    public function getLinks() {

        if ($this->total <= $this->perPage) {
            return array();
        }

        $out = array();

        if ($this->showFirstLast) {
            $out[] = array(1, 'First');
        }
        if ($this->showPrevNext) {
            $out[] = array(max(1, $this->pageNr - 1), 'Prev');
        }

        $first = max($this->pageNr - $this->around, 1);
        if (($first > 1) && ($this->showFirstLast || $this->showPrevNext)) {
            $out[] = array('...');
        }
        $lastPage = (int)(($this->total + $this->perPage - 1) / $this->perPage);
        $last = (min($this->pageNr + $this->around, $lastPage));
        for ($i = $first; $i <= $last; $i++) {
            $out[] = array($i, $i);
        }

        if ((($last < $lastPage - 1) && $this->showPrevNext) || (($last < $lastPage) && $this->showFirstLast)) {
            $out[] = array('...');
        }

        if ($this->showPrevNext) {
            $out[] = array(min($lastPage, ($this->pageNr + 1)), 'Next');
        }
        if ($this->showFirstLast) {
            $out[] = array($lastPage, 'Last');
        }

        return $out;
    }
    //--------------------------------------------------------------------------
}
