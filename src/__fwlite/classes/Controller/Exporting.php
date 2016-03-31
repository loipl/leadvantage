<?php

class Controller_Exporting extends Controller {


    public function indexAction() {
        App::getFrontController()->setUsePageTemplate(false);

        // Only log SQL errors
        DB::clearGlobalListeners(true, false);
        DB::$db->clearInstanceListeners(true, false);

        // All exporting functionality is in class Helper_Export
        $hx = new Helper_Export();
        $hx->doExport($_GET);
    }
    //--------------------------------------------------------------------------
}
