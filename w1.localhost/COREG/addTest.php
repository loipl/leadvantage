<?php

//var_export($_GET); die;

// $_GET = array (
//   'docket' => 'NzgxKy1MOWt2dnZ2OUAyQD0yUW00TCcnUj0nT3ZfX19fX3Z1NXgjOTRqNXYsY3NjJ0NjdiE1OTJANHZ2X3kiX3kidnZ2WXg1NC01aD1MNGMjNWhPNSp2WXg1NC01aD1MNGMjNWhPNSp2Nzkna0A9aDFMNGsiN080QHgiWUwnYzVodzRjazQtQDUtaWh5bCdjbHloLTVIaWhCQC09OTU1a2hYNXg0a0AnYy1MQEd2dk0vUiAgIFJ5VzZSeTYgdg==',
//   'ip_address' => '85.222.134.142',
// );

if (empty($_GET['docket'])) {
    die('FAIL');
}
$letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*:?_+-=|\'";.,/ ';
$affKey  = '42=j5r#L@FsxOc\'GP9-kZaCHmEgu1B7w:UqoDVb8.Y&X!f,I3T?p_y W6/J$Mn*Q^%Ktdz+;NSlev0|ARi"h';
$s = ''; $d = base64_decode($_GET['docket']);

for ($i = 0; $i < strlen($d); $i++) {
    $c = $d[$i];
    $pos = strpos($affKey, $c);
    if ($pos !== false) {
        $s .= $letters[$pos];
    }
}
$s = substr($s, 4);
$arr = explode('|', $s);

$username = $arr[0];
if ($username == 'konoko') {
    echo "FAIL: taken
SUGGESTIONS: konoko1234, konoko1234, konoko1234";
    die;
}
die('OK');
