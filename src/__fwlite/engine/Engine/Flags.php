<?php

/**
 * @desc Settings that alter behavior of Engine class.
 */
class Engine_Flags {

    /**
     * @desc if supplied then this data will be used as incoming data, not $_POST, $_GET etc.
     * Used for unit testing
     */
    public $testIncomingData = false;

    /**
     * @desc array of url_prefix => response , used for unit testing
     */
    public $fakeUrlResponses = array();

    /**
     * @desc For use by unit tests, to make sure campaign will be skimmed
     */
    public $alwaysSkim = false;

    /**
     * @desc For use with unit tests, to make sure that skimming doesn't happen when we don't want to.
     * Overrides $alwaysSkim
     */
    public $neverSkim = false;

    /**
     * @desc Whether we should check for duplicate submission.
     * In case we're running unit tests, this should be turned off.
     * When duplicate is detected we simply do whatever we did the first time,
     * either redirect user or show an error message
     */
    public $checkForDuplicates = true;

    /**
     * @desc Meant for unit tests. If it has int value, it will be used
     * as limit for current user when checking for too many submissions.
     */
    public $test_MaxSubmissions = false;

    /**
     * @desc Meant for unit tests. If it has a value it will be used for
     * ownerUserCaps
     */
    public $test_OwnerUserCaps = false;

    /**
     * @desc If false, the code will only look at incoming data and not also at profiles.
     * It will be set to false for unit tests by default.
     */
    public $useProfilesForMissingData = true;
}
