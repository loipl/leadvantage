<?php
if (!isset($_GET['pw']) || $_GET['pw'] =! 'V5HTpFe4NR4p') exit;

if(isset($_GET['days'])) $days = $_GET['days'];
else $days = '1';
$days = (int)$days + 7;

mysql_connect("dbserver","export","ppKm5Rb6StFUQtaR", true);
$lwDb = 'leadwrench';

$userIDs = listEligibleUserIDs($lwDb, array('DAT'));
if (!$userIDs) {
    // There are no users with ID 0 - this means we will output an empty query
    $userIDs = array(0);
}
//die;
mysql_query("USE `pingtree`");

$select = "SELECT
DISTINCT a.email,
IF(TRIM(b1.value) = '' || b1.value IS NULL, d.remote_ip, b1.value) AS `ipaddress`,
IF(TRIM(b2.value) != '' && b2.value IS NOT NULL, b2.value,
   IF(TRIM(b3.value) != '' && b3.value IS NOT NULL, b3.value, d.http_referer)) AS `source`
FROM profiles AS a
LEFT JOIN log_incoming AS d ON a.email = d.email
LEFT JOIN $lwDb.wp_users AS e ON d.user_id = e.id
LEFT JOIN profiles_data AS b1 ON a.id = b1.profile_id AND b1.field_type_id = 24
LEFT JOIN profiles_data AS b2 ON a.id = b2.profile_id AND b2.field_type_id = 10
LEFT JOIN profiles_data AS b3 ON a.id = b3.profile_id AND b3.field_type_id = 9
LEFT JOIN profiles_inferred AS c1 ON a.id = c1.profile_id AND c1.token = 'country' 
WHERE
a.email IS NOT NULL 
AND d.request_time < DATE_ADD(NOW(), INTERVAL -45 DAY) AND d.request_time > DATE_ADD(NOW(), INTERVAL -90 DAY)
AND d.user_id IN (" . implode(',', $userIDs) . ")
AND (c1.value = 'United States')
AND (a.email LIKE '%@yahoo.%' OR a.email LIKE '%@ymail.%' OR a.email LIKE '%@gmail.com' OR a.email LIKE '%@googlemail.com' OR a.email LIKE '%@msn.com' OR a.email LIKE '%@email.msn.com' OR a.email LIKE '%@live.%' OR a.email LIKE '%@hotmail.%' OR a.email LIKE '%@outlook.com' )
GROUP BY a.email
LIMIT 500000;";
$export = mysql_query ($select) or die ( "SQL error: " . mysql_error( ) );

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=ricardo-dating.csv");
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
            if ($role != 's2member_level5') {
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
