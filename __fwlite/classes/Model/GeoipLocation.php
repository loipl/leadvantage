<?php

class Model_GeoipLocation extends CrudModel {
    /**
     * @var DB
     */
    protected $db;

    protected $t_geoip_locations;

    protected $t_geoip_ip_blocks;// = 'geoip_ip_blocks_mem';

    protected $t_geoip_ip_cache;

    protected $t_geoip_fips;

    protected $t_us_states;

    protected $t_cc2_2_cc3;

    protected $locations = array();

    const GEOIP_REGION           = 101;
    const GEOIP_REGION_CODE      = 102;
    const GEOIP_STATE            = 103;
    const GEOIP_STATE_CODE       = 104;
    const GEOIP_COUNTRY          = 105;
    const GEOIP_COUNTRY_CODE     = 106;
    const GEOIP_CITY             = 107;
    const GEOIP_AREA_CODE        = 108;
    const GEOIP_POSTAL_CODE      = 109;
    const GEOIP_SR               = 110;
    const GEOIP_SR_CODE          = 111;

    const GEOIP_COUNTRY_CODE_3   = 112;

    protected static $geoipFields = array (
        //self::GEOIP_REGION           => ':region:',
        //self::GEOIP_REGION_CODE      => ':region_code:',
        //self::GEOIP_STATE            => ':state:',
        //self::GEOIP_STATE_CODE       => ':state_code:',

        self::GEOIP_COUNTRY          => ':country:',
        self::GEOIP_COUNTRY_CODE     => ':country_code:',
        self::GEOIP_COUNTRY_CODE_3   => ':country_code_3chars:',
        self::GEOIP_CITY             => ':city:',
        self::GEOIP_AREA_CODE        => ':area_code:',
        self::GEOIP_POSTAL_CODE      => ':postal_code:',
        self::GEOIP_SR               => ':state_or_region:',
        self::GEOIP_SR_CODE          => ':state_or_region_code:',
    );

    protected static $countryCodes = array (
      'AC' => 'Ascension Island',
      'AD' => 'Andorra',
      'AE' => 'United Arab Emirates',
      'AF' => 'Afghanistan',
      'AG' => 'Antigua And Barbuda',
      'AI' => 'Anguilla',
      'AL' => 'Albania',
      'AM' => 'Armenia',
      'AN' => 'Netherlands Antilles',
      'AO' => 'Angola',
      'AQ' => 'Antarctica',
      'AR' => 'Argentina',
      'AS' => 'American Samoa',
      'AT' => 'Austria',
      'AU' => 'Australia',
      'AW' => 'Aruba',
      'AX' => 'Åland',
      'AZ' => 'Azerbaijan',
      'BA' => 'Bosnia And Herzegovina',
      'BB' => 'Barbados',
      'BE' => 'Belgium',
      'BD' => 'Bangladesh',
      'BF' => 'Burkina Faso',
      'BG' => 'Bulgaria',
      'BH' => 'Bahrain',
      'BI' => 'Burundi',
      'BJ' => 'Benin',
      'BM' => 'Bermuda',
      'BN' => 'Brunei Darussalam',
      'BO' => 'Bolivia',
      'BR' => 'Brazil',
      'BS' => 'Bahamas',
      'BT' => 'Bhutan',
      'BV' => 'Bouvet Island',
      'BW' => 'Botswana',
      'BY' => 'Belarus',
      'BZ' => 'Belize',
      'CA' => 'Canada',
      'CC' => 'Cocos (Keeling) Islands',
      'CD' => 'Congo (Democratic Republic)',
      'CF' => 'Central African Republic',
      'CG' => 'Congo (Republic)',
      'CH' => 'Switzerland',
      'CI' => 'Cote D’Ivoire',
      'CK' => 'Cook Islands',
      'CL' => 'Chile',
      'CM' => 'Cameroon',
      'CN' => 'People’s Republic of China',
      'CO' => 'Colombia',
      'CR' => 'Costa Rica',
      'CU' => 'Cuba',
      'CV' => 'Cape Verde',
      'CX' => 'Christmas Island',
      'CY' => 'Cyprus',
      'CZ' => 'Czech Republic',
      'DE' => 'Germany',
      'DJ' => 'Djibouti',
      'DK' => 'Denmark',
      'DM' => 'Dominica',
      'DO' => 'Dominican Republic',
      'DZ' => 'Algeria',
      'EC' => 'Ecuador',
      'EE' => 'Estonia',
      'EG' => 'Egypt',
      'ER' => 'Eritrea',
      'ES' => 'Spain',
      'ET' => 'Ethiopia',
      'EU' => 'European Union',
      'FI' => 'Finland',
      'FJ' => 'Fiji',
      'FK' => 'Falkland Islands (Malvinas)',
      'FM' => 'Micronesia, Federated States Of',
      'FO' => 'Faroe Islands',
      'FR' => 'France',
      'GA' => 'Gabon',
      'GB' => 'United Kingdom',
      'GD' => 'Grenada',
      'GE' => 'Georgia',
      'GF' => 'French Guiana',
      'GG' => 'Guernsey',
      'GH' => 'Ghana',
      'GI' => 'Gibraltar',
      'GL' => 'Greenland',
      'GM' => 'Gambia',
      'GN' => 'Guinea',
      'GP' => 'Guadeloupe',
      'GQ' => 'Equatorial Guinea',
      'GR' => 'Greece',
      'GS' => 'South Georgia And The South Sandwich Islands',
      'GT' => 'Guatemala',
      'GU' => 'Guam',
      'GW' => 'Guinea-Bissau',
      'GY' => 'Guyana',
      'HK' => 'Hong Kong',
      'HM' => 'Heard And Mc Donald Islands',
      'HN' => 'Honduras',
      'HR' => 'Croatia (local name: Hrvatska)',
      'HT' => 'Haiti',
      'HU' => 'Hungary',
      'ID' => 'Indonesia',
      'IE' => 'Ireland',
      'IL' => 'Israel',
      'IM' => 'Isle of Man',
      'IN' => 'India',
      'IO' => 'British Indian Ocean Territory',
      'IQ' => 'Iraq',
      'IR' => 'Iran (Islamic Republic Of)',
      'IS' => 'Iceland',
      'IT' => 'Italy',
      'JE' => 'Jersey',
      'JM' => 'Jamaica',
      'JO' => 'Jordan',
      'JP' => 'Japan',
      'KE' => 'Kenya',
      'KG' => 'Kyrgyzstan',
      'KH' => 'Cambodia',
      'KI' => 'Kiribati',
      'KM' => 'Comoros',
      'KN' => 'Saint Kitts And Nevis',
      'KR' => 'Korea, Republic Of',
      'KW' => 'Kuwait',
      'KY' => 'Cayman Islands',
      'KZ' => 'Kazakhstan',
      'LA' => 'Lao People’s Democratic Republic',
      'LB' => 'Lebanon',
      'LC' => 'Saint Lucia',
      'LI' => 'Liechtenstein',
      'LK' => 'Sri Lanka',
      'LR' => 'Liberia',
      'LS' => 'Lesotho',
      'LT' => 'Lithuania',
      'LU' => 'Luxembourg',
      'LV' => 'Latvia',
      'LY' => 'Libyan Arab Jamahiriya',
      'MA' => 'Morocco',
      'MC' => 'Monaco',
      'MD' => 'Moldova, Republic Of',
      'ME' => 'Montenegro',
      'MG' => 'Madagascar',
      'MH' => 'Marshall Islands',
      'MK' => 'Macedonia, The Former Yugoslav Republic Of',
      'ML' => 'Mali',
      'MM' => 'Myanmar',
      'MN' => 'Mongolia',
      'MO' => 'Macau',
      'MP' => 'Northern Mariana Islands',
      'MQ' => 'Martinique',
      'MR' => 'Mauritania',
      'MS' => 'Montserrat',
      'MT' => 'Malta',
      'MU' => 'Mauritius',
      'MV' => 'Maldives',
      'MW' => 'Malawi',
      'MX' => 'Mexico',
      'MY' => 'Malaysia',
      'MZ' => 'Mozambique',
      'NA' => 'Namibia',
      'NC' => 'New Caledonia',
      'NE' => 'Niger',
      'NF' => 'Norfolk Island',
      'NG' => 'Nigeria',
      'NI' => 'Nicaragua',
      'NL' => 'Netherlands',
      'NO' => 'Norway',
      'NP' => 'Nepal',
      'NR' => 'Nauru',
      'NU' => 'Niue',
      'NZ' => 'New Zealand',
      'OM' => 'Oman',
      'PA' => 'Panama',
      'PE' => 'Peru',
      'PF' => 'French Polynesia',
      'PG' => 'Papua New Guinea',
      'PH' => 'Philippines, Republic of the',
      'PK' => 'Pakistan',
      'PL' => 'Poland',
      'PM' => 'St. Pierre And Miquelon',
      'PN' => 'Pitcairn',
      'PR' => 'Puerto Rico',
      'PS' => 'Palestine',
      'PT' => 'Portugal',
      'PW' => 'Palau',
      'PY' => 'Paraguay',
      'QA' => 'Qatar',
      'RE' => 'Reunion',
      'RO' => 'Romania',
      'RS' => 'Serbia',
      'RU' => 'Russian Federation',
      'RW' => 'Rwanda',
      'SA' => 'Saudi Arabia',
      'UK' => 'United Kingdom',
      'SB' => 'Solomon Islands',
      'SC' => 'Seychelles',
      'SD' => 'Sudan',
      'SE' => 'Sweden',
      'SG' => 'Singapore',
      'SH' => 'St. Helena',
      'SI' => 'Slovenia',
      'SJ' => 'Svalbard And Jan Mayen Islands',
      'SK' => 'Slovakia (Slovak Republic)',
      'SL' => 'Sierra Leone',
      'SM' => 'San Marino',
      'SN' => 'Senegal',
      'SO' => 'Somalia',
      'SR' => 'Suriname',
      'ST' => 'Sao Tome And Principe',
      'SU' => 'Soviet Union',
      'SV' => 'El Salvador',
      'SY' => 'Syrian Arab Republic',
      'SZ' => 'Swaziland',
      'TC' => 'Turks And Caicos Islands',
      'TD' => 'Chad',
      'TF' => 'French Southern Territories',
      'TG' => 'Togo',
      'TH' => 'Thailand',
      'TJ' => 'Tajikistan',
      'TK' => 'Tokelau',
      'TI' => 'East Timor (new code)',
      'TM' => 'Turkmenistan',
      'TN' => 'Tunisia',
      'TO' => 'Tonga',
      'TP' => 'East Timor (old code)',
      'TR' => 'Turkey',
      'TT' => 'Trinidad And Tobago',
      'TV' => 'Tuvalu',
      'TW' => 'Taiwan',
      'TZ' => 'Tanzania, United Republic Of',
      'UA' => 'Ukraine',
      'UG' => 'Uganda',
      'UM' => 'United States Minor Outlying Islands',
      'US' => 'United States',
      'UY' => 'Uruguay',
      'UZ' => 'Uzbekistan',
      'VA' => 'Vatican City State (Holy See)',
      'VC' => 'Saint Vincent And The Grenadines',
      'VE' => 'Venezuela',
      'VG' => 'Virgin Islands (British)',
      'VI' => 'Virgin Islands (U.S.)',
      'VN' => 'Viet Nam',
      'VU' => 'Vanuatu',
      'WF' => 'Wallis And Futuna Islands',
      'WS' => 'Samoa',
      'YE' => 'Yemen',
      'YT' => 'Mayotte',
      'ZA' => 'South Africa',
      'ZM' => 'Zambia',
      'ZW' => 'Zimbabwe',
    );

    protected static $usStates = array();


    public function __construct(DB $db = null) {
        $db = $db ? $db : DB::$db;
        $this->setTablePrefix($db->getTablePrefix());
        parent::__construct($db, $this->t_geoip_locations);
    }
    //--------------------------------------------------------------------------


    public function listGeoipFields() {
        $arr = self::$geoipFields;
        unset($arr[self::GEOIP_COUNTRY_CODE_3]);
        return $arr;
    }
    //--------------------------------------------------------------------------


    public function listGeoipFieldsFull() {
        return self::$geoipFields;;
    }
    //--------------------------------------------------------------------------


    public static function listCountryCodes() {
        return self::$countryCodes;
    }
    //--------------------------------------------------------------------------


    public function listUsStates() {
        if (empty(self::$usStates)) {
            self::$usStates = $this->db->getArrayAssoc("SELECT `abbr`, `name` FROM `$this->t_us_states` ORDER BY 2");
        }
        return self::$usStates;
    }
    //--------------------------------------------------------------------------


    /**
     * @desc Reads data from geolitecity locations CSV file and writes it into geoip_locations
     * table
     */
    public function readLocations($locationsFileName) {
        set_time_limit(360000);

        $fp = fopen($locationsFileName, 'rb');
        if (!$fp) {
            return;
        }

        $table = $this->t_geoip_locations . '_tmp';
        $this->db->query("DROP TABLE IF EXISTS `$table`");
        $this->db->query("
        CREATE TABLE IF NOT EXISTS `$table` (
          `id` int(11) unsigned NOT NULL auto_increment,
          `country` varchar(2) NOT NULL,
          `region` varchar(64) NOT NULL,
          `city` varchar(64) NOT NULL,
          `zip` varchar(10) NOT NULL,
        PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
        $this->db->query("TRUNCATE `$table`");

        // Skip copyright line and column names
        fgets($fp);
        fgets($fp);

        $data = array();
        for (;;) {
            $arr = fgetcsv($fp);
            if (!$arr) {
                break;
            }
            $data[] = $this->db->processParams('(?, ?, ?, ?, ?)', array((int)$arr[0], $arr[1], $arr[2], $arr[3], $arr[4]));
            if (sizeof($data) > 200) {
                $this->insertLocationsRows($data, $table);
                $data = array();
            }
        }
        if (sizeof($data)) {
            $this->insertLocationsRows($data, $table);
        }
        fclose($fp);

        $this->db->query("DROP TABLE `$this->t_geoip_locations`");
        $this->db->query("RENAME TABLE `$table` TO `$this->t_geoip_locations`");
    }
    //--------------------------------------------------------------------------


    protected function insertLocationsRows(array $data, $table) {
        $sql = "INSERT IGNORE INTO `$table` (`id`, `country`, `region`, `city`, `zip`) VALUES\n" . implode(",\n", $data);
        $this->db->query($sql);
    }
    //--------------------------------------------------------------------------

    /**
     * @desc Reads data from geolitecity ip blocks CSV file and writes it into geoip_ip_blocks
     * table
     */
    public function readBlocks($blocksFileName) {
        set_time_limit(360000);

        $fp = fopen($blocksFileName, 'rb');
        if (!$fp) {
            return;
        }

        $table = $this->t_geoip_ip_blocks . '_tmp';
        $this->db->query("DROP TABLE IF EXISTS `$table`");
        $this->db->query("
        CREATE TABLE IF NOT EXISTS `$table` (
          `start` int(10) unsigned NOT NULL,
          `end` int(10) unsigned NOT NULL,
          `location_id` int(10) unsigned NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("TRUNCATE `$table`");

        // Skip copyright line and column names
        fgets($fp);
        fgets($fp);

        $data = array();
        for (;;) {
            $arr = fgetcsv($fp);
            if (!$arr) {
                break;
            }
            if (sizeof($arr) != 3) {
                continue;
            }
            $data[] = "($arr[0],$arr[1],$arr[2])";
            if (sizeof($data) > 250) {
                $this->insertBlockRows($data, $table);
                $data = array();
            }
        }
        if (sizeof($data)) {
            $this->insertBlockRows($data, $table);
        }

        fclose($fp);
        $this->db->query("ALTER TABLE `$table` ADD INDEX (`start` , `end`);");
        $this->db->query("ALTER TABLE `$table` ADD INDEX (`location_id`);");

        $this->db->query("DROP TABLE `$this->t_geoip_ip_blocks`");
        $this->db->query("RENAME TABLE `$table` TO `$this->t_geoip_ip_blocks`");
    }
    //--------------------------------------------------------------------------


    protected function insertBlockRows(array $data, $table) {
        $sql = "INSERT IGNORE INTO `$table` (`start`, `end`, `location_id`) VALUES\n" . implode(",\n", $data);
        $this->db->query($sql) or die(mysql_error());
    }
    //--------------------------------------------------------------------------


    public function getLocationForIP($ipAddress) {
        $ipAddress = $this->normalizeIpAddressIntoNumericForm($ipAddress);

        if (isset($this->locations[$ipAddress])) {
            return $this->locations[$ipAddress];
        }

        $location = $this->findLocation($ipAddress);

        if ($location) {
            $location['country_name'] = isset(self::$countryCodes[$location['country']]) ? self::$countryCodes[$location['country']] : 'USA';
            $usStates = $this->listUsStates();
            if (isset($usStates[$location['region']])) {
                $location['state_name'] = $usStates[$location['region']];
            } else {
                $location['state_name'] = $this->getRegionFromFips($location['country'], $location['region']);
            }
            $location['city'] = utf8_encode($location['city']);
        }

        $this->locations[$ipAddress] = $location;
        return $location;
    }
    //--------------------------------------------------------------------------


    private function normalizeIpAddressIntoNumericForm($ipAddress) {
        if (substr_count($ipAddress, '.') == 3) {
            $ipAddress = ip2long($ipAddress);
        }
        if (!is_numeric($ipAddress)) {
            $ipAddress = (int)$ipAddress;
        }
        $ipAddress = sprintf("%u\n", $ipAddress);
        return $ipAddress;
    }
    //--------------------------------------------------------------------------


    private function findLocation($ipAddress) {
        $cachedLocationId = $this->db->getTopLeftInt("SELECT `loc_id` FROM `$this->t_geoip_ip_cache` WHERE `id` = $ipAddress");
        if ($cachedLocationId) {
            $location = $this->getCachedLocation($cachedLocationId);

        } else {
            $location = $this->getLocationFromGeoipTables($ipAddress);
            if ($location) {
                $this->db->query("INSERT IGNORE INTO `$this->t_geoip_ip_cache` (`id`, `loc_id`, `create_time`) VALUES ($ipAddress, ?, NOW())", array((int)$location['id']));
            }
        }

        return $location;
    }
    //--------------------------------------------------------------------------


    public function flushCacheEntries() {
        if ($this->db->query("DELETE FROM `$this->t_geoip_ip_cache` WHERE `create_time` < DATE_SUB(NOW(), INTERVAL 1 DAY)")) {
            return true;
        } else {
            return false;
        }
    }
    //--------------------------------------------------------------------------


    private function getCachedLocation($cachedLocationId) {
        $location = $this->db->getTopArray("SELECT `l`.*, `c`.`code3` AS `country_code_3chars` FROM `$this->t_geoip_locations` AS `l`
        LEFT JOIN `$this->t_cc2_2_cc3` AS `c` ON `c`.`code2` = `l`.`country`
        WHERE `id` = ?", array((int)$cachedLocationId));

        return $location;
    }
    //--------------------------------------------------------------------------


    private function getLocationFromGeoipTables($ipAddress) {
        $sql = "
        SELECT `l`.*, `c`.`code3` AS `country_code_3chars` FROM `$this->t_geoip_locations` AS `l`
        LEFT JOIN `$this->t_cc2_2_cc3` AS `c` ON `c`.`code2` = `l`.`country`
        WHERE `id` = (SELECT `location_id` FROM `$this->t_geoip_ip_blocks` WHERE `start` = (SELECT MAX(`start`) FROM `geoip_ip_blocks` WHERE (`start` <= $ipAddress)))
        ";
        $location = $this->db->getTopArray($sql);
        return $location;
    }
    //--------------------------------------------------------------------------


    public function getRegionFromFips($countryCode, $regionCode) {
        $sql = "SELECT `region_name` FROM `$this->t_geoip_fips` WHERE `country_code` = ? AND `region_code` = ?";
        return $this->db->getTopLeft($sql, array($countryCode, $regionCode));
    }
    //--------------------------------------------------------------------------
}