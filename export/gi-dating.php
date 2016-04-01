<?php
if(!isset($_GET['pw']) || $_GET['pw'] =! '1V5HTpFe4NR4p') exit;

if(isset($_GET['days'])) $days = $_GET['days'];
else $days = '7';
$days = (int)$days + 5;

mysql_connect("dbserver","pingtree","Qh6EFXFV8XsYRMLS"); 
mysql_select_db("pingtree");
$header = '';
$data = '';

$select = "SELECT DISTINCT a.email, b3.value AS 'ipaddress', b4.value AS 'source', DATE(d.request_time) AS 'date', b1.value AS 'gender', b2.value AS 'first_name', c1.value AS 'country', c2.value AS 'state' FROM profiles AS a LEFT JOIN log_incoming AS d ON a.email = d.email LEFT JOIN profiles_data AS b1 ON a.id = b1.profile_id AND b1.field_type_id = '18' LEFT JOIN profiles_data AS b2 ON a.id = b2.profile_id AND b2.field_type_id = '7' LEFT JOIN profiles_data AS b3 ON a.id = b3.profile_id AND b3.field_type_id = '24' LEFT JOIN profiles_data AS b4 ON a.id = b4.profile_id AND b4.field_type_id = '10' LEFT JOIN profiles_inferred AS c1 ON a.id = c1.profile_id AND c1.token = 'country' LEFT JOIN profiles_inferred AS c2 ON a.id = c2.profile_id AND c2.token = 'state_or_region' WHERE (d.http_referer = '' OR d.http_referer = ' ' OR d.remote_ip = '' OR d.remote_ip = ' ') AND b3.value != '' AND b3.value != ' ' AND b4.value != '' AND b4.value != ' ' AND d.request_time < DATE_ADD(NOW(), INTERVAL -5 DAY) AND d.request_time > DATE_ADD(NOW(), INTERVAL -$days DAY) AND (a.email NOT LIKE '%@adelphia.net' AND a.email NOT LIKE '%@alltel.net' AND a.email NOT LIKE '%@ameritech.net' AND a.email NOT LIKE '%@aol.com' AND a.email NOT LIKE '%@aim.com' AND a.email NOT LIKE '%@atlanticbb.net' AND a.email NOT LIKE '%@bright.net' AND a.email NOT LIKE '%@cableone.net' AND a.email NOT LIKE '%@charter.net' AND a.email NOT LIKE '%@clearwire.net' AND a.email NOT LIKE '%@cox.com' AND a.email NOT LIKE '%@cox.net' AND a.email NOT LIKE '%@cs.com' AND a.email NOT LIKE '%@earthlink.com' AND a.email NOT LIKE '%@earthlink.net' AND a.email NOT LIKE '%@email.com' AND a.email NOT LIKE '%@Email.com' AND a.email NOT LIKE '%@excite.com' AND a.email NOT LIKE '%@fuze.net' AND a.email NOT LIKE '%@gmail.com' AND a.email NOT LIKE '%@hotmail.com' AND a.email NOT LIKE '%@ihavenet.com' AND a.email NOT LIKE '%@insightbb.com' AND a.email NOT LIKE '%@iwon.com' AND a.email NOT LIKE '%@live.com' AND a.email NOT LIKE '%@live.net' AND a.email NOT LIKE '%@localnet.com' AND a.email NOT LIKE '%@Localnet.com' AND a.email NOT LIKE '%@lycos.com' AND a.email NOT LIKE '%@mac.com' AND a.email NOT LIKE '%@mail.com' AND a.email NOT LIKE '%@Mail.com' AND a.email NOT LIKE '%@mchsi.com' AND a.email NOT LIKE '%@mindspring.com' AND a.email NOT LIKE '%@mindspring.net' AND a.email NOT LIKE '%@msn.com' AND a.email NOT LIKE '%@netscape.net' AND a.email NOT LIKE '%@netzero.com' AND a.email NOT LIKE '%@optonline.net' AND a.email NOT LIKE '%@peoplepc.com' AND a.email NOT LIKE '%@prodigy.net' AND a.email NOT LIKE '%@rocketmail.com' AND a.email NOT LIKE '%@sprintpcs.com' AND a.email NOT LIKE '%@suddenlink.net' AND a.email NOT LIKE '%@verizon.net' AND a.email NOT LIKE '%@yahoo.com' AND a.email NOT LIKE '%@ymail.com' AND a.email NOT LIKE '%@zoominternet.net' AND a.email NOT LIKE '%@outlook.com') AND (d.user_id = '4' OR d.user_id = '8' OR d.user_id = '26' OR d.user_id = '54' OR d.user_id = '52' OR d.user_id = '58' OR d.user_id = '62');";

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

$select = "SELECT DISTINCT a.email, d.remote_ip AS 'ipaddress', d.http_referer AS 'source', DATE(d.request_time) AS 'date', b1.value AS 'gender', b2.value AS 'first_name', c1.value AS 'country', c2.value AS 'state' FROM profiles AS a LEFT JOIN log_incoming AS d ON a.email = d.email LEFT JOIN profiles_data AS b1 ON a.id = b1.profile_id AND b1.field_type_id = '18' LEFT JOIN profiles_data AS b2 ON a.id = b2.profile_id AND b2.field_type_id = '7' LEFT JOIN profiles_inferred AS c1 ON a.id = c1.profile_id AND c1.token = 'country' LEFT JOIN profiles_inferred AS c2 ON a.id = c2.profile_id AND c2.token = 'state_or_region' WHERE d.http_referer != '' AND d.http_referer != ' ' AND d.remote_ip != '' AND d.remote_ip != ' ' AND d.request_time < DATE_ADD(NOW(), INTERVAL -5 DAY) AND d.request_time > DATE_ADD(NOW(), INTERVAL -$days DAY) AND (a.email NOT LIKE '%@adelphia.net' AND a.email NOT LIKE '%@alltel.net' AND a.email NOT LIKE '%@ameritech.net' AND a.email NOT LIKE '%@aol.com' AND a.email NOT LIKE '%@aim.com' AND a.email NOT LIKE '%@atlanticbb.net' AND a.email NOT LIKE '%@bright.net' AND a.email NOT LIKE '%@cableone.net' AND a.email NOT LIKE '%@charter.net' AND a.email NOT LIKE '%@clearwire.net' AND a.email NOT LIKE '%@cox.com' AND a.email NOT LIKE '%@cox.net' AND a.email NOT LIKE '%@cs.com' AND a.email NOT LIKE '%@earthlink.com' AND a.email NOT LIKE '%@earthlink.net' AND a.email NOT LIKE '%@email.com' AND a.email NOT LIKE '%@Email.com' AND a.email NOT LIKE '%@excite.com' AND a.email NOT LIKE '%@fuze.net' AND a.email NOT LIKE '%@gmail.com' AND a.email NOT LIKE '%@hotmail.com' AND a.email NOT LIKE '%@ihavenet.com' AND a.email NOT LIKE '%@insightbb.com' AND a.email NOT LIKE '%@iwon.com' AND a.email NOT LIKE '%@live.com' AND a.email NOT LIKE '%@live.net' AND a.email NOT LIKE '%@localnet.com' AND a.email NOT LIKE '%@Localnet.com' AND a.email NOT LIKE '%@lycos.com' AND a.email NOT LIKE '%@mac.com' AND a.email NOT LIKE '%@mail.com' AND a.email NOT LIKE '%@Mail.com' AND a.email NOT LIKE '%@mchsi.com' AND a.email NOT LIKE '%@mindspring.com' AND a.email NOT LIKE '%@mindspring.net' AND a.email NOT LIKE '%@msn.com' AND a.email NOT LIKE '%@netscape.net' AND a.email NOT LIKE '%@netzero.com' AND a.email NOT LIKE '%@optonline.net' AND a.email NOT LIKE '%@peoplepc.com' AND a.email NOT LIKE '%@prodigy.net' AND a.email NOT LIKE '%@rocketmail.com' AND a.email NOT LIKE '%@sprintpcs.com' AND a.email NOT LIKE '%@suddenlink.net' AND a.email NOT LIKE '%@verizon.net' AND a.email NOT LIKE '%@yahoo.com' AND a.email NOT LIKE '%@ymail.com' AND a.email NOT LIKE '%@zoominternet.net' AND a.email NOT LIKE '%@outlook.com') AND (d.user_id = '4' OR d.user_id = '8' OR d.user_id = '26' OR d.user_id = '54' OR d.user_id = '52' OR d.user_id = '58' OR d.user_id = '62');";

$export = mysql_query ( $select ) or die ( "SQL error: " . mysql_error( ) );

$fields = mysql_num_fields ( $export );

for ( $i = 0; $i < $fields; $i++ )
{
//    $header .= mysql_field_name( $export , $i ) . ",";
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

$data = str_replace( "\r" , "" , $data );

if ( $data == "" )
{
    $data = "\n(0) Records Found!\n";                        
}

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=gi-dating.csv");
header("Pragma: no-cache");
header("Expires: 0");
print "$header\n$data";
?>
