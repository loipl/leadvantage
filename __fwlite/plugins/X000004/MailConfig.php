<?php

class X000004_MailConfig {

    public static $smtpHost      = '';
    public static $smtpAuth      = false;
    public static $smtpPassword  = '';
    public static $smtpSecure    = '';
    public static $smtpUsername  = '';
    public static $smtpFromName  = '';
    public static $smtpFromEmail = '';
    
    public static $smtpDebug     = false;

}

Config::initExternalConfig('X000004_MailConfig');
