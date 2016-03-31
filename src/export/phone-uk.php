<?php
if(!isset($_GET['pw']) || $_GET['pw'] =! 'V5HTpFe4NR4p') exit;

if(isset($_GET['days'])) $days = $_GET['days'];
else $days = '7';
$days = (int)$days + 5;

mysql_connect("dbserver","pingtree","Qh6EFXFV8XsYRMLS"); 
mysql_select_db("pingtree");
$header = '';
$data = '';

$select = "SELECT a.email, b2.value AS 'phone', b3.value AS 'ipaddress', b4.value AS 'source', DATE(d.request_time) AS 'date' FROM profiles AS a LEFT JOIN profiles_data AS b2 ON a.id = b2.profile_id AND b2.field_type_id = '3' LEFT JOIN log_incoming AS d ON a.email = d.email LEFT JOIN profiles_data AS b3 ON a.id = b3.profile_id AND b3.field_type_id = '24' AND b3.value != '' LEFT JOIN profiles_data AS b4 ON a.id = b4.profile_id AND b4.field_type_id = '10' AND b4.value != '' LEFT JOIN profiles_inferred AS c1 ON a.id = c1.profile_id AND c1.token = 'country' WHERE (d.http_referer = '' OR d.remote_ip = '') AND d.request_time < DATE_ADD(NOW(), INTERVAL -5 DAY) AND d.request_time > DATE_ADD(NOW(), INTERVAL -$days DAY) AND (c1.value LIKE 'United Kingdom');";

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

$select = "SELECT a.email, b.value AS 'phone', d.remote_ip AS 'ipaddress', d.http_referer AS 'source', DATE(d.request_time) AS 'date' FROM profiles AS a LEFT JOIN profiles_data AS b ON a.id = b.profile_id AND b.field_type_id = '3' AND b.value != '' LEFT JOIN log_incoming AS d ON a.email = d.email LEFT JOIN profiles_inferred AS c1 ON a.id = c1.profile_id AND c1.token = 'country' WHERE d.http_referer != '' AND d.remote_ip != '' AND d.request_time < DATE_ADD(NOW(), INTERVAL -5 DAY) AND d.request_time > DATE_ADD(NOW(), INTERVAL -$days DAY) AND (c1.value LIKE 'United Kingdom')";

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
header("Content-Disposition: attachment; filename=gi-all-us.csv");
header("Pragma: no-cache");
header("Expires: 0");
print "$header\n$data";
?>
