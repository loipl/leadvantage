<?php

class Helper_MailWarning {

    const EMAIL_TYPE_QUOTA_WARNING = 1;
    const EMAIL_TYPE_TRAFFIC_DROP  = 2;

    /**
     * @var Model_EmailSendLog
     */
    private $model;

    /**
     * @var Model_Campaign
     */
    private $modelCampaign;

    public $fakeEmail = false;

    public $allEmailsTo = false;


    public function __construct() {
        $this->model = SingletonRegistry::getSingleInstance('Model_EmailSendLog');
    }
    //--------------------------------------------------------------------------


    public function sendWarningsToUsersOver($percentage = 90) {
        $list = $this->listUsersWithQuotasOver($percentage);
        $this->sendQuotaWarningEmail($list);
    }
    //--------------------------------------------------------------------------


    public function getQuotasForUserAssoc() {
        /* @var $modelUserLevels Model_ConfigPostsPerLevel */
        $modelUserLevels = SingletonRegistry::getSingleInstance('Model_ConfigPostsPerLevel');
        $levelsAssoc = $modelUserLevels->listLevelsAssoc();

        $result = array();
        foreach (SingletonRegistry::getModelUser()->listUserCapsAssoc() as $userId => $userCaps) {
            $result[$userId] = Engine_IncomingData::getMaxDeliveriesForUserLevel($levelsAssoc, $userCaps);
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    public function sendQuotaWarningEmail(array $users) {
        if (!$users) {
            return array();
        }
        /* @var $modelEmailSendLog Model_EmailSendLog */
        $modelEmailSendLog = SingletonRegistry::getSingleInstance('Model_EmailSendLog');
        
        /* @var $subaccountModel Model_SubAccount */
        $subaccountModel = SingletonRegistry::getSingleInstance('Model_SubAccount');
        
        $emails = array();
        foreach ($users as $row) {
            if ($modelEmailSendLog->userHasMailTypeThisMonth($row['user_id'], self::EMAIL_TYPE_QUOTA_WARNING)) {
                continue;
            }
            
            $subAccountInfo = $subaccountModel->checkIfUserIsSubAccount($row['user_id']);
            if (!empty($subAccountInfo)) {
                continue;
            }
            
            $user = get_userdata($row['user_id']);
            if ($user) {
                $email = $user->data->user_email;
                $username = $user->data->display_name;

                if ($row['quota'] > $row['count']) {
                    $subject = 'WARNING: You are at ' . $row['percentage'] . '% of your monthly lead quota';
                } else {
                    $subject = 'WARNING: You\'ve exceeded your monthly lead quota';
                }
                
                $body = "Hi, " . $username . "! Your lead quota for the month is $row[quota] and you have already sent $row[count] leads. Your account will stop accepting leads when you have reached your quota.  Please contact your account manager to upgrade.";
                if ($this->fakeEmail) {
                    $emails[] = array($email);
                } else {
                    if ($this->allEmailsTo) {
                        wp_mail($this->allEmailsTo, $subject, "email:$email\n\n\n$body");
                    } else {
                        wp_mail($email, $subject, $body);
                    }
                }
                $data = array('user_id' => $row['user_id'], 'type' => self::EMAIL_TYPE_QUOTA_WARNING,
                        'rec_email' => $email, 'subject' => $subject, 'body' => $body);
                $modelEmailSendLog->insert($data);
            }
        }
        return $emails;
    }
    //--------------------------------------------------------------------------


    public function listUsersWithQuotasOver($percentage = 90) {
        if ($percentage < 0) {
            throw new EServerError("Percentage must be more than 0");
        } elseif ($percentage > 100) {
            throw new EServerError("Percentage must be <= 100");
        }
        $percentage = (int)$percentage;
        $logIncoming = SingletonRegistry::getModelLogIncoming();
        $quotas = $this->getQuotasForUserAssoc();

        $result = array();
        foreach ($quotas as $userId => $quota) {
            if ($quota > 0) {
                $count = $logIncoming->getTotalSuccessfulCount($userId);
                if (($quota * $percentage / 100) <= $count) {
                    $result[] = array('user_id' => $userId, 'quota' => $quota, 'percentage' => $percentage, 'count' => $count);
                }
            }
        }
        return $result;
    }
    //--------------------------------------------------------------------------


    public function sendTrafficDropWarning($trafficFromLastHours = 1, $trafficCheckAgainstHours = 24, $percentage = 20) {
        if ($trafficFromLastHours <= 0) {
            return;
        }
        if ($trafficCheckAgainstHours <= 0) {
            return;
        }
        if ($trafficFromLastHours >= $trafficCheckAgainstHours) {
            return;
        }
        $this->modelCampaign or $this->modelCampaign = SingletonRegistry::getModelCampaign();

        $relevantCampaigns = $this->modelCampaign->listCampaignsWithDropTrafficWarning();
        if (!$relevantCampaigns) {
            return;
        }
        $logIncoming = SingletonRegistry::getModelLogIncoming();
        $found = array();

        foreach ($relevantCampaigns as $campaign) {
            $smallPeriod = $logIncoming->getCountInLastHours($campaign['id'], $trafficFromLastHours);
            $bigPeriod   = $logIncoming->getCountInLastHours($campaign['id'], $trafficCheckAgainstHours);

            $smallCount = $smallPeriod / $trafficFromLastHours;
            $bigCount   = $bigPeriod   / $trafficCheckAgainstHours;
            if ($smallCount < $bigCount * ((100 - $percentage) / 100)) {
                $found[] = $campaign;
            }
            $content = "Your traffic on campaign $campaign[name] in last $trafficFromLastHours hour";
            if ($trafficFromLastHours > 1) {
                $content .= 's';
            }
            $smallCount = number_format($smallCount, 2);
            $bigCount   = number_format($bigCount, 2);

            $content .= " has fallen to $smallCount per hour, which is more than $percentage% less than your average of $bigCount per hour from last $trafficCheckAgainstHours hours.";
            $this->sendTrafficDropEmail($campaign, $content);
        }
    }
    //--------------------------------------------------------------------------


    private function sendTrafficDropEmail(array $campaign, $content) {
        /* @var $modelEmailSendLog Model_EmailSendLog */
        $modelEmailSendLog = SingletonRegistry::getSingleInstance('Model_EmailSendLog');

        $emails = array();
        $user = get_userdata($campaign['user_id']);
        if ($user) {
            $email = $user->data->user_email;
            $subject = 'WARNING: Your traffic dropped';
            if ($this->fakeEmail) {
                $emails[] = array($email);
            } else {
                if ($this->allEmailsTo) {
                    wp_mail($this->allEmailsTo, $subject, "email:$email\n\n\n$content");
                } else {
                    wp_mail($email, $subject, $content);
                }
            }
            $data = array('user_id' => $campaign['user_id'], 'type' => self::EMAIL_TYPE_TRAFFIC_DROP,
                    'rec_email' => $email, 'subject' => $subject, 'body' => $content);
            $modelEmailSendLog->insert($data);
        }
    }
    //--------------------------------------------------------------------------
}
