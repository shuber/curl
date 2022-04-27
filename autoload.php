<?php

function curl_autoload($className)
{
  $prefix = dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
  switch ($className)
  {
    case 'Curl':
      require_once $prefix.'curl.php';
      break;
    case 'CurlResponse':
      require_once $prefix.'curl_response.php';
      break;
    case 'CurlPutData':
      require_once $prefix.'curl_put_data.php';
      break;
  }
}

spl_autoload_register('curl_autoload');

