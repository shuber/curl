<?php

require 'curl.php';
$curl = new Curl;

print_r($curl->get('google.com')->headers);

?>