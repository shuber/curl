<?php

/**
 * A basic CURL wrapper
 *
 * See the README for documentation/examples or http://php.net/curl for more information about the libcurl extension for PHP
 *
 * @package curl
 * @author Sean Huber <shuber@huberry.com>
 * @author Fabian Grassl
**/
class Curl
{

  /**
   * The file to read and write cookies to for requests
   *
   * @var string
  **/
  protected $cookie_file = null;

  /**
   * Determines whether or not requests should follow redirects
   *
   * @var boolean
  **/
  protected $follow_redirects = true;

  /**
   * An associative array of headers to send along with requests
   *
   * @var array
  **/
  protected $headers = array();

  /**
   * An associative array of CURLOPT options to send along with requests
   *
   * @var array
  **/
  protected $options = array();

  /**
   * The referer header to send along with requests
   *
   * @var string
  **/
  protected $referer = null;

  /**
   * The user agent to send along with requests
   *
   * @var string
  **/
  protected $user_agent = null;

  /**
   * Whether to validate SSL certificates
   *
   * @var boolean
   * @access protected
  **/
  protected $validate_ssl = false;

  /**
   * Stores resource handle for the current CURL request
   * Stores resource handle for the current CURL request
   * @var resource
   * @access protected
  **/
  protected $request = null;

  /**
   * Stores the HTTP auth credentials
   *
   * @var $userpwd
   * @access protected
  **/
  protected $userpwd;

  /**
   * Initializes a Curl object
   *
   * Sets the $cookie_file to "curl_cookie.txt" in the current directory
   * Also sets the $user_agent to $_SERVER['HTTP_USER_AGENT'] if it exists, 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)' otherwise
  **/
  public function __construct()
  {
    $this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Curl/PHP '.PHP_VERSION.' (http://github.com/shuber/curl)';
  }

  /**
   * Weather to validate ssl certificates
   *
   * @param bool $val whether to validate SSL certificates
   * @return void
  **/
  public function setValidateSsl($val)
  {
    return $this->validate_ssl = $val;
  }


  /**
   * Get the user agent to send along with requests
  **/
  public function getUserAgent()
  {
    return $this->user_agent;
  }

  /**
   * Set the user agent to send along with requests
  **/
  public function setUserAgent($user_agent)
  {
    $this->user_agent = $user_agent;
  }

  /**
   * Get the referer header to send along with requests
  **/
  public function getReferer()
  {
    return $this->referer;
  }

  /**
   * Set the referer header to send along with requests
  **/
  public function setReferer($referer)
  {
    $this->referer = $referer;
  }

  /**
   * Get HTTP-Headers as associative array
  **/
  public function getHeaders()
  {
    return $this->headers;
  }

  /**
   * Set HTTP-Headers as associative array
  **/
  public function setHeaders($headers)
  {
    $this->headers = $headers;
  }

  /**
   * Set HTTP-Header value
  **/
  public function setHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }

  /**
   * Get an associative array of CURLOPT options
  **/
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Set an associative array of CURLOPT options
  **/
  public function setOptions($options)
  {
    foreach ($options as $name => $value)
    {
      $this->setOption($name, $value);
    }
  }

  /**
   * Set an associative array of CURLOPT options
  **/
  public function setOption($name, $value)
  {
    if (is_string($name))
    {
      $name = constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($name)));
    }
    $this->options[$name] = $options;
  }

  /**
   * Set whether or not requests should follow redirects
   *
   * @param bool $follow_redirects
   * @return void
  **/
  public function setFollowRedirects($follow_redirects)
  {
    $this->follow_redirects = $follow_redirects;
  }

  /**
   * Get whether or not requests should follow redirects
   *
   * @return bool
  **/
  public function getFollowRedirects()
  {
    return $this->follow_redirects;
  }

  /**
   * Set the file to read and write cookies to for requests
   *
   * @param string|bool $cookie_file path string or true (default location) | false (no cookie file)
   * @return void
  **/
  public function setCookieFile($cookie_file = null)
  {
    if ($cookie_file === true)
    {
      $this->cookie_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'curl_cookie.txt';
    }
    elseif (empty($cookie_file))
    {
      $this->cookie_file = null;
    }
    else
    {
      $this->cookie_file = $cookie_file;
    }
  }

  /**
   * Get the file to read and write cookies to for requests
   *
   * @return string file-location
  **/
  public function getCookieFile()
  {
    return $this->cookie_file;
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
   * Returns the error string of the current request if one occurred
   *
   * @return string
  **/
  public function error()
  {
    return $this->error;
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
    if (!empty($vars))
    {
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
    if (null !== $put_data && is_string($put_data))
    {
      $put_data = CurlPutData::fromString($put_data);
    }
    $this->error = '';
    $this->request = curl_init();

    if (is_array($post_vars))
    {
      $post_vars = http_build_query($post_vars, '', '&');
    }

    if (is_array($put_data))
    {
      $put_data = http_build_query($put_data, '', '&');
    }

    $this->setRequestOptions($url, $method, $post_vars, $put_data);
    $this->setRequestHeaders();

    $response = curl_exec($this->request);

    if (!$response)
    {
      throw new CurlException(curl_error($this->request), curl_errno($this->request));
    }

    $response = new CurlResponse($response);

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
    if (null === $username)
    {
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
    foreach ($this->headers as $key => $value)
    {
      $headers[] = $key.': '.$value;
    }
    curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
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

    if ($purl['scheme'] == 'https')
    {
      curl_setopt($this->request, CURLOPT_PORT , empty($purl['port'])?443:$purl['port']);
      if ($this->validate_ssl)
      {
        curl_setopt($this->request,CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->request, CURLOPT_CAINFO, dirname(__FILE__).'/cacert.pem');
      }
      else
      {
        curl_setopt($this->request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->request, CURLOPT_SSL_VERIFYHOST, 2);
      }
    }

    $method = strtoupper($method);
    switch ($method)
    {
      case 'HEAD':
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

    curl_setopt($this->request, CURLOPT_URL, $url);

    if (!empty($vars))
    {
        if ('POST' != $method)
        {
          throw new InvalidArgumentException('POST-vars may only be set for a POST-Request.');
        }
        curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
    }
    elseif ('POST' == $method)
    {
      throw new InvalidArgumentException('POST-vars must be set for a POST-Request.');
    }


    if (null !== $put_data)
    {
      if ('PUT' != $method)
      {
        throw new InvalidArgumentException('PUT-data may only be set for a PUT-Request.');
      }
      curl_setopt($this->request, CURLOPT_INFILE, $put_data->getResource());
      curl_setopt($this->request, CURLOPT_INFILESIZE, $put_data->getResourceSize());
    }
    elseif ('PUT' == $method)
    {
        throw new InvalidArgumentException('PUT-data must be set for a PUT-Request.');
    }

    # Set some default CURL options
    curl_setopt($this->request, CURLOPT_HEADER, true);
    curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($this->request, CURLOPT_TIMEOUT, 30);

    if ($this->cookie_file)
    {
      curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
      curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
    }

    if ($this->follow_redirects)
    {
      curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
    }

    if ($this->referer)
    {
      curl_setopt($this->request, CURLOPT_REFERER, $this->referer);
    }

    if ($this->userpwd)
    {
      curl_setopt($this->request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($this->request, CURLOPT_USERPWD, $this->userpwd);
    }
    else
    {
      curl_setopt($this->request, CURLOPT_HTTPAUTH, false);
    }

	# Set any custom CURL options
    foreach ($this->options as $option => $value)
    {
      curl_setopt($this->request, $option, $value);
    }
  }

 /**
   * Returns an associative array of curl options
   * currently configured.
   *
   * @return array Associative array of curl options
  **/
  function get_request_options() {
    return curl_getinfo($this->request);
  }

}