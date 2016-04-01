<?php

class X000004_Util_Mailer {

    /**
     * @desc $data needs to have these fields: [email, name, id, token]
     */
    public static function sendEmailVerification(array $data) {
        $mailer = self::makeNewMailer();
        $mailer->IsHTML(true);
        $mailer->AddAddress($data['email'], $data['name']);
        $mailer->Subject = 'Please verify your email address';

        $params = array('t' => $data['id'] . 'e' . $data['token']);
        $link = "http://{$_SERVER['HTTP_HOST']}" . App::getFrontController()->urlFor('a', array('action' => 'verify'), $params);
        $mailer->Body = "Please click on this link to verify your email address:<br /><br />\n" .
        "<a href=\"$link\">$link</a>";

        $mailer->Send();
    }
    //--------------------------------------------------------------------------


    public static function sendHtmlMail($subject, $body, $toEmail,  $toName = '') {
        $mailer = self::makeNewMailer();
        $mailer->IsHTML(true);

        $mailer->AddAddress($toEmail, $toName);
        $mailer->Subject = $subject;
        $mailer->Body    = $body;
        return $mailer->Send();
    }
    //--------------------------------------------------------------------------


    public static function sendPlaintextMail($subject, $body, $toEmail,  $toName = '') {
        $mailer = self::makeNewMailer();
        $mailer->IsHTML(false);

        $mailer->AddAddress($toEmail, $toName);
        $mailer->Subject = $subject;
        $mailer->body    = $body;
        return $mailer->Send();
    }
    //--------------------------------------------------------------------------


    /**
     * @return X000004_PHPMailer
     */
    public static function makeNewMailer() {
        $mailer = new X000004_PHPMailer();

        if (empty(X000004_MailConfig::$smtpHost)) {
            $mailer->IsMail();
        } else {
            $mailer->IsSMTP();
            $mailer->Host       = X000004_MailConfig::$smtpHost;
            $mailer->SMTPSecure = X000004_MailConfig::$smtpSecure;
            $mailer->SMTPDebug  = X000004_MailConfig::$smtpDebug;

            $mailer->SMTPAuth   = X000004_MailConfig::$smtpAuth;
            if (X000004_MailConfig::$smtpAuth) {
                $mailer->Username  = X000004_MailConfig::$smtpUsername;
                $mailer->Password  = X000004_MailConfig::$smtpPassword;
            }

            $mailer->From       = X000004_MailConfig::$smtpFromEmail;
            $mailer->FromName   = X000004_MailConfig::$smtpFromName;
            $mailer->Sender     = X000004_MailConfig::$smtpFromEmail;

            $mailer->AddReplyTo(X000004_MailConfig::$smtpFromEmail, "Replies for my site");
        }

        return $mailer;
    }
    //--------------------------------------------------------------------------
}
