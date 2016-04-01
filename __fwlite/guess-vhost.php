<?php

// Guess what machine we're and put it in HTTP_HOST, on so the right config file will be used
if (getenv('BOBAN_DEV_MACHINE')) {
    isset($_SERVER["HTTP_HOST"]) or ($_SERVER["HTTP_HOST"] = 'www.w5.localhost');
} elseif (getenv('DOM_DEV_MACHINE')) {
    isset($_SERVER["HTTP_HOST"]) or ($_SERVER["HTTP_HOST"] = 'www.w5.localhost');
} elseif (!empty($_ENV['staging_environment']) || (isset($_SERVER['HOSTNAME']) && ($_SERVER['HOSTNAME'] == 'cs901.mojohost.com'))) {
    isset($_SERVER["HTTP_HOST"]) or ($_SERVER["HTTP_HOST"] = 'www.staging.leadwrench.com');
} else {
    isset($_SERVER["HTTP_HOST"]) or ($_SERVER["HTTP_HOST"] = 'www.leadwrench.com');
}
