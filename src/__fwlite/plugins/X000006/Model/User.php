<?php

class X000006_Model_User extends CrudModel {

    /**
     * @var DB
     */
    protected $db;

    protected $t_users;

    protected $t_user_email_tokens;


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_users);

        $this->timestampForInsert = array('created_at');
        $this->zeroOneFields      = array('is_admin', 'email_verified', 'enabled');
    }
    //--------------------------------------------------------------------------


    public function getUserByUsernameAndPassSHA1($userName, $passSHA1) {
        $sql = "SELECT * FROM `$this->t_users` WHERE `username` = ? AND `pass_sha1` = ?";
        return $this->db->getTopArray($sql, array($userName, $passSHA1));
    }
    //--------------------------------------------------------------------------


    public function getUserForUsername($userName) {
        return $this->db->getTopArray("SELECT * FROM `$this->tableName` WHERE `username` = ?", array($userName));
    }
    //--------------------------------------------------------------------------


    /**
     * @return array('name' => name, 'token' => token, 'email' => email);
     */
    public function getOrGenerateEmailToken($userId) {
        $this->deleteExpiredSignups();

        $sql = "
        SELECT `u`.`name`, `u`.`email`, `t`.`token` 
        FROM `$this->t_user_email_tokens` AS `t`, `$this->t_users` AS `u` 
        WHERE `t`.`user_id` = `u`.`id` AND `user_id` = ?";

        $row = $this->db->getTopArray($sql, array((int)$userId), MYSQL_ASSOC);
        if (!$row) {
            $token = sha1($userId . __FILE__ . microtime(true));
            $this->db->query("INSERT INTO `$this->t_user_email_tokens` (`user_id`, `token`, `create_time`) VALUES (?, ?, NOW())", array((int)$userId, $token));

            $row = $this->db->getTopArray($sql, array((int)$userId), MYSQL_ASSOC);
        }
        return $row;
    }
    //--------------------------------------------------------------------------


    public function deleteExpiredSignups() {
        $this->db->query("DELETE FROM `$this->t_users` WHERE `is_admin` = '0' AND `email_verified` = '0' AND `created_at` < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $this->db->query("DELETE FROM `$this->t_user_email_tokens` WHERE `create_time` < DATE_SUB(NOW(), INTERVAL 1 DAY)");
    }
    //--------------------------------------------------------------------------


    public function getUserIdByEmail($email) {
        $sql = "SELECT `id` FROM `$this->tableName` WHERE `email` = ?";
        return $this->db->getTopLeftInt($sql, array($email));
    }
    //--------------------------------------------------------------------------


    public function verifyEmail($userId) {
        $sql = "UPDATE `$this->t_users` SET `email_verified` = '1' WHERE `id` = ?";
        $this->db->query($sql, array((int)$userId));

        return true;
    }
    //--------------------------------------------------------------------------


    public function isValidToken($userId, $token) {
        $this->deleteExpiredSignups();

        $sql = "SELECT COUNT(*) FROM `$this->t_user_email_tokens` WHERE `user_id` = ? AND `token` = ?";
        $count = $this->db->getTopLeftInt($sql, array((int)$userId, $token));
        if ($count == 0) {
            return false;
        }

        $sql = "UPDATE `$this->t_user_email_tokens` SET `verify_time` = NOW() WHERE `user_id` = ?";
        $this->db->query($sql, array((int)$userId));

        return true;
    }
    //--------------------------------------------------------------------------
}
