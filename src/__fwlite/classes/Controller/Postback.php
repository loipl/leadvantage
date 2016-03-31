<?php

class Controller_Postback extends Controller {

    public function run() {
        App::getFrontController()->setUsePageTemplate(false);
        $helper = new Helper_Postback();
        $helper->run($_SERVER['REQUEST_URI']);
    }
    //--------------------------------------------------------------------------

}

