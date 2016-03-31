<?php

abstract class AuthBase {

    /**
     * @desc Method should throw an exception in case user cannot log in
     */
    abstract public function tryToLogIn($userName, $password);
    //--------------------------------------------------------------------------


    public function logOut() {
        Session::getSession()->setAuthData(array());
    }
    //--------------------------------------------------------------------------


    abstract public function isLoggedIn();
    //--------------------------------------------------------------------------
}
