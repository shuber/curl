<?php

namespace Shuber\Curl;

use \InvalidArgumentException;
use \ReflectionObject;

/**
 * A basic CURL wrapper
 *
 * @package curl
 * @author Sean Huber <shuber@huberry.com>
 * @author Fabian Grassl
 * @author Nick Lombard <curling@jigsoft.co.za>
**/
class Curl
{
  private   $reflect = null;

  protected $cookie_file = null,
            $follow_redirects = true,
            $validate_ssl = false,
            $request = null,
            $userpwd = false;

  public    $options = array(),
            $headers = array(
                  'Accept' => null,
                  'Accept-Charset' => null,
                  'Accept-Encoding' => null,
                  'Accept-Language' => null,
                  'Authorization' => null,
                  'Expect' => null,
                  'From' => null,
                  'Host' => null,
                  'If-Match' => null,
                  'If-Modified-Since' => null,
                  'If-None-Match' => null,
                  'If-Range' => null,
                  'If-Unmodified-Since' => null,
                  'Max-Forwards' => null,
                  'Proxy-Authorization' => null,
                  'Range' => null,
                  'Referer' => null,
                  'TE' => null,
                  'User-Agent' => null,
            );

  public static $debug = false,
                $with_headers = false;

  /**
    * Magic methods for object member access,
    */
  public function __get($key)
  {
      if ($this->reflect->hasProperty($key))

            return $this->{$key};
     return null;
  }
  public function __set($key,$value)
  {
      if ($this->reflect->hasProperty($key))
            $this->{$key} = $value;
  }
  public function __isset($key)
  {
      if ($this->reflect->hasProperty($key))

            return isset($this->{$key});
      return null;
  }
  public function __unset($key)
  {
      if ($this->reflect->hasProperty($key))
            unset($this->{$key});
  }

  /**
   * Initializes a Curl object
   *
   * @param debug - turn debug on - will collect a debug log in the response.
   * @param with_headers - switch whether to collect headers or not.
  **/
  public function __construct($debug = false, $with_headers = false)
  {
    $this->reflect = new ReflectionObject($this);
    self::$debug = $debug;
    self::$with_headers = $with_headers;
    $this->composeUserAgent();
  }

  /**
   * Com.osing the User-Agent request header with software version info.
   *
   * We attempt to collect as much informaito as is pertinent but not
   * collecting anfthing usseless. First and foremost we send the Shuber/Curl
   * version info and attempt to locate the libcurl anh PHP versions. If the
   * SERVER_SOFTWARE variable is populated we are likely on CGI if that is
   * empty we will attempt to retrieve CLI Terminal information.
   *
   * HTTP_USER_AGENT is added if available which will give the server ample
   * information to try and resove any issues that might be rolated to the
   * software supporting this library.
   *
   * To overwrite this behaviour simply set the User-Agent environment variable
   * to whatever you'd prefer, even empty string is sufficient.
  **/
  private function composeUserAgent()
  {
    if (empty($this->headers['User-Agent'])) {
      $user_agent = 'Shuber/Curl/1.0 (cURL/';
      $curl = \curl_version();

      if (isset($curl['version']))
           $user_agent .= $curl['version'];

      else
           $user_agent .= '?.?.?';

      $user_agent .= ' PHP/'.PHP_VERSION.' ('.PHP_OS.')';

          if (isset($_SERVER['SERVER_SOFTWARE']))
                  $user_agent .= ' '.\preg_replace('~PHP/[\d\.]+~U',
                          '', $_SERVER['SERVER_SOFTWARE']);
      else {

        if (isset($_SERVER['TERM_PROGRAM']))
                  $user_agent .= " {$_SERVER['TERM_PROGRAM']}";

          if (isset($_SERVER['TERM_PROGRAM_VERSION']))
                  $user_agent .= "/{$_SERVER['TERM_PROGRAM_VERSION']}";
      }

      if (isset($_SERVER['HTTP_USER_AGENT']))
            $user_agent .= " {$_SERVER['HTTP_USER_AGENT']}";

      $user_agent .= ')';
      $headers[] = $user_agent;
    }

  }

  /**
   * Set an associative array of CURLOPT options
  **/
  public function setOptions($options)
  {
    foreach ($options as $name => $value) {
      $this->setOption($name, $value);
    }
  }

  /**
   * Set a CURLOPT option
  **/
  public function setOption($name, $value)
  {
    if (is_string($name)) {
      $name = constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($name)));
    }
    $this->options[$name] = $value;
  }

  /**
   * Set the file to read and write cookies to for requests
   *
   * @param string|bool $cookie_file path string or true (default location) | false (no cookie file)
   * @return void
  **/
  public function setCookieFile($cookie_file = null)
  {
    if ($cookie_file === true) {
      $this->cookie_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'curl_cookie.txt';
    } elseif (empty($cookie_file))
    {
      $this->cookie_file = null;
    } else {
      $this->cookie_file = $cookie_file;
    }
  }

  /**
   * Makes an HTTP DELETE request to the specified $url with an optional array or string of $vars
   *
   * Returns a CurlResponse object if the request was successful, false otherwise
   *
   * @param string $url
   * @param array|string $vars
   * @return CurlResponse object
  **/
  public function delete($url, $vars = array())
  {
    return $this->request('DELETE', $this->create_get_url($url, $vars));
  }

  /**
   * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
   *
   * Returns a CurlResponse object if the request was successful, false otherwise
   *
   * @param string $url
   * @param array|string $vars
   * @return CurlResponse
  **/
  public function get($url, $vars = array())
  {
    return $this->request('GET', $this->create_get_url($url, $vars));
  }

  /**
   * Modify the given $url with an optional array or string of $vars
   *
   * Returns the modified $url string
   *
   * @param string $url
   * @param array|string $vars
   * @return string
  **/
  protected function create_get_url($url, $vars = array())
  {
    if (!empty($vars)) {
      $url .= (stripos($url, '?') !== false) ? '&' : '?';
      $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
    }

    return $url;
  }

  /**
   * Makes an HTTP HEAD request to the specified $url with an optional array or string of $vars
   *
   * Returns a CurlResponse object if the request was successful, false otherwise
   *
   * @param string $url
   * @param array|string $vars
   * @return CurlResponse
  **/
  public function head($url, $vars = array())
  {
    return $this->request('HEAD', $this->create_get_url($url, $vars));
  }

  /**
   * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
   *
   * @param string $url
   * @param array|string $vars
   * @return CurlResponse|boolean
  **/
  public function post($url, $vars)
  {
    return $this->request('POST', $url, $vars);
  }

  /**
   * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
   *
   * Returns a CurlResponse object if the request was successful, false otherwise
   *
   * @param string $url
   * @param CurlPutData|string $put_data
   * @param array|string $vars
   * @return CurlResponse|boolean
  **/
  public function put($url, $put_data, $vars = array())
  {
    return $this->request('PUT', $this->create_get_url($url, $vars), array(), $put_data);
  }

  /**
   * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
   *
   * Returns a CurlResponse object if the request was successful, false otherwise
   *
   * @param string $method
   * @param string $url
   * @param array|string $post_vars
   * @param CurlPutData|string $put_data
   * @return CurlResponse|boolean
  **/
  public function request($method, $url, $post_vars = array(), $put_data = null)
  {
    if (null !== $put_data && is_string($put_data)) {
      $put_data = CurlPutData::fromString($put_data);
    }
    $this->request = curl_init();

    if (is_array($post_vars)) {
      $post_vars = http_build_query($post_vars, '', '&');
    }

    if (is_array($put_data)) {
      $put_data = http_build_query($put_data, '', '&');
    }

    if (self::$debug||self::$with_headers) {
      $out = fopen("php://temp", 'rw');
      $this->setOption('CURLOPT_STDERR', $out);
      $this->setOption('CURLOPT_VERBOSE', true);
    }

    $this->setRequestOptions($url, $method, $post_vars, $put_data);
    $this->setRequestHeaders();

    $response = curl_exec($this->request);

    if (!$response) {
      throw new CurlException(curl_error($this->request), curl_errno($this->request));
    }

    if (isset($out)) {
      rewind($out);
      $outstr = stream_get_contents($out);
      fclose($out);
      $response = new CurlResponse($response, $outstr);
      unset($outstr);
    } else {
      $response = new CurlResponse($response);
    }

    curl_close($this->request);

    return $response;
  }

  /**
   * Sets the user and password for HTTP auth basic authentication method.
   *
   * @param string|null $username
   * @param string|null $password
   * @return Curl
  **/
  function setAuth($username, $password=null)
  {
    if (null === $username) {
      $this->userpwd = null;

      return $this;
    }

    $this->userpwd = $username.':'.$password;

    return $this;
  }

  /**
   * Formats and adds custom headers to the current request
   *
   * @return void
   * @access protected
  **/
  protected function setRequestHeaders()
  {
    $headers = array();
    foreach ($this->headers as $key => $value) {

      if (isset($value)
            && !in_array($key, array('User-Agent', 'Referer')))
                $headers[] = $key.': '.$value;

    }
    curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
  }

  /**
    * Set the associated CURL options for a request method
    *
    * @param string $method
    * @return void
    * @access protected
  **/
  protected function setRequestMethod($method)
  {
    switch ($method) {
      case 'HEAD':
      case 'OPTIONS':
        curl_setopt($this->request, CURLOPT_NOBODY, true);
        break;
      case 'GET':
        curl_setopt($this->request, CURLOPT_HTTPGET, true);
        break;
      case 'POST':
        curl_setopt($this->request, CURLOPT_POST, true);
        break;
      case 'PUT':
        curl_setopt($this->request, CURLOPT_PUT, true);
        break;
      default:
        curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
    }
  }

  /**
   * Sets the CURLOPT options for the current request
   *
   * @param string $url
   * @param string $method
   * @param string $vars
   * @param string $put_data
   * @return void
   * @access protected
  **/
  protected function setRequestOptions($url, $method, $vars, $put_data)
  {
    $purl = parse_url($url);

    if (!empty($purl['scheme']) && $purl['scheme'] == 'https') {
      curl_setopt($this->request, CURLOPT_PORT , empty($purl['port'])?443:$purl['port']);
      if ($this->validate_ssl) {
        curl_setopt($this->request,CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->request, CURLOPT_CAINFO, dirname(__FILE__).'/cacert.pem');
      } else {
        curl_setopt($this->request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->request, CURLOPT_SSL_VERIFYHOST, 2);
      }
    }

    $method = strtoupper($method);
    $this->setRequestMethod($method);

    curl_setopt($this->request, CURLOPT_URL, $url);

    if (!empty($vars)) {
        if ('POST' != $method) {
          throw new InvalidArgumentException('POST-vars may only be set for a POST-Request.');
        }
        curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
    } elseif ('POST' == $method)
    {
      throw new InvalidArgumentException('POST-vars must be set for a POST-Request.');
    }


    if (null !== $put_data) {
      if ('PUT' != $method) {
        throw new InvalidArgumentException('PUT-data may only be set for a PUT-Request.');
      }
      curl_setopt($this->request, CURLOPT_INFILE, $put_data->getResource());
      curl_setopt($this->request, CURLOPT_INFILESIZE, $put_data->getResourceSize());
    } elseif ('PUT' == $method)
    {
        throw new InvalidArgumentException('PUT-data must be set for a PUT-Request.');
    }

    # Set some default CURL options
    curl_setopt($this->request, CURLOPT_HEADER, false);
    curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->request, CURLOPT_USERAGENT, 'User-Agent: '. $this->header['User-Agent']);
    curl_setopt($this->request, CURLOPT_TIMEOUT, 30);

    if ($this->cookie_file) {
      curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
      curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
    }

    if ($this->follow_redirects) {
      curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
    }

    if ($this->headers['Referer']) {
      curl_setopt($this->request, CURLOPT_REFERER, 'Referer: '.$this->headers['Referer']);
    }

    if ($this->userpwd) {
      curl_setopt($this->request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($this->request, CURLOPT_USERPWD, $this->userpwd);
    } else {
      curl_setopt($this->request, CURLOPT_HTTPAUTH, false);
    }

    # Set any custom CURL options
    foreach ($this->options as $option => $value) {
      curl_setopt($this->request, $option, $value);
    }
  }

 /**
   * Returns an associative array of curl options
   * currently configured.
   *
   * @return array Associative array of curl options
  **/
  function getRequestOptions()
  {
    return curl_getinfo($this->request);
  }

}
