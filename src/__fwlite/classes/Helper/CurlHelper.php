<?php

class CurlHelper
{

    public static function request($url, $method = 'GET', $params = array())
    {
        $options = array(
            CURLOPT_URL			=> $url,
            CURLOPT_CUSTOMREQUEST       => $method,
            CURLOPT_FOLLOWLOCATION	=> true,
            CURLOPT_AUTOREFERER		=> true,
            CURLOPT_RETURNTRANSFER	=> true,
            CURLOPT_TIMEOUT             => 60  
        );
        
        if ($method === 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = is_array($params) ? http_build_query($params) : $params;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $content    = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err        = curl_error($ch);
        $errno      = curl_errno($ch);
        curl_close($ch);

        return array(
            'httpCode'  => $httpCode,
            'httpErr'   => $err,
            'httpErrno' => $errno,
            'content'   => $content
        );
    }
}
