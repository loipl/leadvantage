<?php

/**
 * @desc Supports multiple accounts, plus each user can have isAdmin flag
 *
 * @author Boban
 */
abstract class Auth_MultiAdmin extends AuthBase {
    protected $isAdmin    = false;
    protected $isLoggedIn = false;
    protected $userId     = 0;
    protected $userName   = '';

    protected $userData   = array();


    public function __construct() {
        $this->initToStartState();
        $arr = Session::getSession()->getAuthData();
        if (isset($arr['username'], $arr['password'])) {
            $this->isLoggedIn = $this->findUser($arr['username'], $arr['password'], $this->isAdmin, $this->userId, true);
            if ($this->isLoggedIn) {
                $this->userName = $arr['username'];
            }
        }
    }
    //--------------------------------------------------------------------------


    public function isAdmin() {
        return $this->isAdmin;
    }
    //--------------------------------------------------------------------------


    public function getUserId() {
        return $this->userId;
    }
    //--------------------------------------------------------------------------


    public function getUserName() {
        return $this->userName;
    }
    //--------------------------------------------------------------------------


    public function isLoggedIn() {
        return $this->isLoggedIn;
    }
    //--------------------------------------------------------------------------


    public function tryToLogIn($userName, $password) {
        if (!$this->findUser($userName, $password, $this->isAdmin, $this->userId, false)) {
            throw new ERequestError("Invalid username or password");
        }
        $this->isLoggedIn = true;
        Session::getSession()->setAuthData(array('username' => $userName, 'password' => $this->encryptPassword($password)));
    }
    //--------------------------------------------------------------------------


    public function logOut() {
        parent::logOut();
        $this->initToStartState();
    }
    //--------------------------------------------------------------------------


    protected function initToStartState() {
        $this->isLoggedIn = false;
        $this->userName   = '';
        $this->userId     = 0;
        $this->isAdmin    = false;
    }
    //--------------------------------------------------------------------------


    protected function encryptPassword($password) {
        return sha1($password);
    }
    //--------------------------------------------------------------------------


    abstract public function findUser($userName, $password, & $isAdmin, & $userId, $passIsEncrypted);
    //--------------------------------------------------------------------------
}