<?php

class X000006_Auth extends Auth_MultiAdmin {

    public function __construct() {
        parent::__construct();
    }
    //--------------------------------------------------------------------------


    /**
     * @return X000006_Auth
     */
    public static function getInstance() {
        return SingletonRegistry::getSingleInstance(__CLASS__);
    }
    //--------------------------------------------------------------------------


    public function findUser($userName, $password, & $isAdmin, & $userId, $passIsEncrypted) {
        $model = SingletonRegistry::getModelUser();
        $row = $model->getUserByUsernameAndPassSHA1($userName, $passIsEncrypted ? $password : $this->encryptPassword($password));

        if ($row) {
            if (empty($row['enabled'])) {
                throw new ERequestError("Account suspended");
            }
            if (empty($row['email_verified']) && empty($row['is_admin'])) {
                throw new ERequestError("Email not yet verified", 1234);
            }
        }

        $isAdmin = !empty($row['is_admin']);
        $userId  = empty($row['id']) ? 0 : (int)$row['id'];
        $this->userData = $row;
        return !empty($row);
    }
    //--------------------------------------------------------------------------


    protected function encryptPassword($password) {
        return sha1(Config::$sha1Salt . $password);
    }
    //--------------------------------------------------------------------------
}
