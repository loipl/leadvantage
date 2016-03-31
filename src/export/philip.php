<?php
if(!isset($_GET['pw']) || $_GET['pw'] =! 'V5HTpFe4NR4p') exit;

if(isset($_GET['days'])) $days = $_GET['days'];
else $days = '7';
$days = (int)$days + 5;

mysql_connect("dbserver","export","ppKm5Rb6StFUQtaR");
mysql_select_db("pingtree");
$header = '';
$data = '';

$select = "SELECT
DISTINCT a.email,
IF(TRIM(b3.value) = '' || b3.value IS NULL, d.remote_ip, b3.value) AS `ipaddress`,
DATE(d.request_time) AS `date`, b1.value AS `gender`,
c1.value AS `country`
FROM profiles AS a
LEFT JOIN log_incoming AS d ON a.email = d.email
LEFT JOIN profiles_data AS b1 ON a.id = b1.profile_id AND b1.field_type_id = 18
LEFT JOIN profiles_data AS b3 ON a.id = b3.profile_id AND b3.field_type_id = 24
LEFT JOIN profiles_inferred AS c1 ON a.id = c1.profile_id AND c1.token = 'country'
WHERE d.request_time >= '2014-01-01'
AND d.request_time <= '2014-04-15'
AND a.email IS NOT NULL
AND d.user_id = 64
AND d.campaign_id = 86
GROUP BY a.email;";

$export = mysql_query ( $select ) or die ( "SQL error: " . mysql_error( ) );

$fields = mysql_num_fields ( $export );

for ( $i = 0; $i < $fields; $i++ )
{
    $header .= mysql_field_name( $export , $i ) . ",";
}

while( $row = mysql_fetch_row( $export ) )
{
    $line = '';
    foreach( $row as $value )
    {
        if ( ( !isset( $value ) ) || ( $value == "" ) )
        {
            $value = ",";
        }
        else
        {
            $value = str_replace( '"' , '""' , $value );
            $value = '"' . $value . '"' . ",";
        }
        $line .= $value;
    }
    $data .= trim( $line ) . "\n";
}

if ( $data == "" )
{
    $data = "\n(0) Records Found!\n";
}

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=dating.csv");
header("Pragma: no-cache");
header("Expires: 0");
print "$header\n$data";
