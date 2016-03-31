<?php

/**
 * @desc Meant for sites that only need one admin account and it is OK to be hardcoded, or set from Config file
 *
 * @author Boban
 */
abstract class Auth_SingleAdmin extends AuthBase {
    protected $userName = 'admin';

    protected $secretKey = '123!@#qweASD';

    // sha1('123!@#qweASD' . '21wE#$')
    protected $adminPassSha1 = 'df2c92879b9d69b4abee0d84b47f28f917bd1378';


    protected function __construct($userName = false, $secretKey = false, $adminPassSha1 = false) {
        if ($userName !== false) {
            $this->userName = $userName;
        }
        if ($secretKey !== false) {
            $this->secretKey = $secretKey;
        }
        if ($adminPassSha1 !== false) {
            $this->adminPassSha1 = $adminPassSha1;
        }
    }
    //--------------------------------------------------------------------------


    public function tryToLogIn($userName, $password) {
        if (!$this->usernameAndPasswordValid($userName, $password)) {
            throw new ERequestError("Invalid username or password");
        }
        $t = time();
        Session::getSession()->setAuthData(array('username' => $userName, 'adminPassSha1' => $t. '-' . sha1($this->secretKey . $t . $this->adminPassSha1)));
    }
    //--------------------------------------------------------------------------


    protected function usernameAndPasswordValid($userName, $password) {
        return ($userName == $this->userName) && (sha1($this->secretKey . $password) == $this->adminPassSha1);
    }
    //--------------------------------------------------------------------------


    public function isLoggedIn() {
        $arr = Session::getSession()->getAuthData();
        if (isset($arr['username'], $arr['adminPassSha1'])) {
            if ($arr['username'] != $this->userName) {
                Session::getSession()->setAuthData(array());
                return false;
            }
            $d = explode('-', $arr['adminPassSha1'], 2);
            if (sizeof($d) != 2) {
                Session::getSession()->setAuthData(array());
                return false;
            }
            return $d[1] == sha1($this->secretKey . $d[0] . $this->adminPassSha1);
        }
        return false;
    }
    //--------------------------------------------------------------------------

}
