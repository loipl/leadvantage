<?php
if (!isset($_GET['pw']) || $_GET['pw'] =! 'V5HTpFe4NR4p') exit;

if(isset($_GET['days'])) $days = $_GET['days'];
else $days = '1';
$days = (int)$days + 7;

mysql_connect("dbserver","export","ppKm5Rb6StFUQtaR", true);
$lwDb = 'leadwrench';

$userIDs = listEligibleUserIDs($lwDb, array('FIN', 'INS'));
if (!$userIDs) {
    // There are no users with ID 0 - this means we will output an empty query
    $userIDs = array(0);
}
//die;
mysql_query("USE `pingtree`");

$select = "SELECT
b7.value AS `first`,
b8.value AS `last`,
b1.value AS `address1`, b2.value AS `address2`, b3.value AS `city`,
IF(TRIM(b4.value) = '' || b4.value IS NULL, b5.value, b4.value) AS `state`,
b6.value AS `zip`,
DATE(d.request_time) AS `date`,
IF(TRIM(b9.value) != '' && b9.value IS NOT NULL, b9.value,
   IF(TRIM(b10.value) != '' && b10.value IS NOT NULL, b10.value, d.http_referer)) AS `source`
FROM profiles AS a
LEFT JOIN log_incoming AS d ON a.email = d.email
LEFT JOIN $lwDb.wp_users AS e ON d.user_id = e.id
LEFT JOIN profiles_data AS b1 ON a.id = b1.profile_id AND b1.field_type_id = 33 
LEFT JOIN profiles_data AS b2 ON a.id = b2.profile_id AND b2.field_type_id = 34 
LEFT JOIN profiles_data AS b3 ON a.id = b3.profile_id AND b3.field_type_id = 27 
LEFT JOIN profiles_data AS b4 ON a.id = b4.profile_id AND b4.field_type_id = 25 
LEFT JOIN profiles_data AS b5 ON a.id = b5.profile_id AND b5.field_type_id = 28 
LEFT JOIN profiles_data AS b6 ON a.id = b6.profile_id AND b6.field_type_id = 22 
LEFT JOIN profiles_data AS b7 ON a.id = b7.profile_id AND b7.field_type_id = 7 
LEFT JOIN profiles_data AS b8 ON a.id = b8.profile_id AND b8.field_type_id = 8 
LEFT JOIN profiles_data AS b9 ON a.id = b9.profile_id AND b9.field_type_id = 10
LEFT JOIN profiles_data AS b10 ON a.id = b10.profile_id AND b10.field_type_id = 9
WHERE
b1.value IS NOT NULL
AND b1.value NOT LIKE '% box %'
AND b3.value IS NOT NULL
AND d.request_time < DATE_ADD(NOW(), INTERVAL -7 DAY) AND d.request_time > DATE_ADD(NOW(), INTERVAL -$days DAY)
AND d.user_id IN (" . implode(',', $userIDs) . ")
GROUP BY a.email";
$export = mysql_query ($select) or die ( "SQL error: " . mysql_error( ) );

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=mail.csv");
header("Pragma: no-cache");
header("Expires: 0");

$fieldsCount = mysql_num_fields($export);
for ($i = 0; $i < $fieldsCount; $i++) {
    echo ($i ? ',' : '') . mysql_field_name($export , $i);
}


$found = false;
while ($row = mysql_fetch_row($export)) {
    $found = true;
    echo "\n";
    for($i = 0, $l = sizeof($row); $i < $l; $i++) {
        echo $i ? ',' : '';
        echo (!isset($row[$i]) || ($row[$i] == "")) ? '' : '"' . str_replace( '"' , '""' , $row[$i] ) . '"';
    }
}

if (!$found)
{
    echo "\n(0) Records Found!\n";
}

/**
 * @desc industries in $industries parameter will be used as "OR" - user will be selected if he has any of the industries
 */
function listEligibleUserIDs($db, array $industries) {
    $userData = array();

    $query = "SELECT `user_id`, `meta_key`, `meta_value` FROM `$db`.`wp_usermeta` WHERE `meta_key` = 'wp_capabilities' OR `meta_key` = 'wp_s2member_custom_fields' ORDER BY 1, 2";
    $res = mysql_query($query) or die("SQL error: " . mysql_error());
    while ($row = mysql_fetch_row($res)) {
        $arr = @unserialize($row[2]);
        if ($arr === false) {
            continue;
        }
        $userId = (int)$row[0];
        if ($row[1] == 'wp_capabilities') {
            $keys = array_keys($arr);
            if (sizeof($keys) != 1) {
                continue;
            }
            $role = trim(strtolower($keys[0]));
            if (($role == 's2member_level3') || ($role == 's2member_level4') || ($role == 's2member_level6') || ($role == 'administrator')) {
                continue;
            }
            $userData[$userId]['cap'] = 1;
        } elseif ($row[1] == 'wp_s2member_custom_fields') {
            $usersIndustries = isset($arr['industry']) ? $arr['industry'] : array();
            $intersect = array_intersect($industries, $usersIndustries);
            if ($intersect) {
                $userData[$userId]['ind'] = 1;
            }
        }
    }

    $userIDs = array();
    foreach ($userData as $userId => $data) {
        if (!empty($data['cap']) && !empty($data['ind'])) {
            $userIDs[] = $userId;
        }
    }
    return $userIDs;
}
