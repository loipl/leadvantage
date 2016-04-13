<?php
$pingToSend = array(
	'key' => '2c4ea2307eb218e299058d74324a360ad2948d85',
	'zip_code' => '19428',
	'need_id' => '2243',
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_URL, 'http://184.107.228.107/api/82ef0ede6471e0f29c848b14939386445fd/');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pingToSend));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
curl_close($ch);
echo $result;
?>