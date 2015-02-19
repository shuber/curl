<?php
namespace Shuber\Curl;

/**
 * Parses the response from a Curl request into an object containing
 * the response body and an associative array of headers
 *
 * @package Shuber/Curl
 * @author Sean Huber <shuber@huberry.com>
 * @author Nick Lombard <curling@jigsoft.co.za>
 * */
class CurlResponse {
  public $body        = '',
          $debug_log   = '',
          $all_headers = array(),
          $headers = array(
                  'Status-Line'        => null,
                  // status line parsed
                  'Http-Version'       => null,
                  'Status-Code'        => null,
                  'Status'             => null,
                  // response header fields
                  'Accept-Ranges'      => null,
                  'Age'                => null,
                  'ETag'               => null,
                  'Location'           => null,
                  'Proxy-Authenticate' => null,
                  'Retry-After'        => null,
                  'Server'             => null,
                  'Vary'               => null,
                  'WWW-Authenticate'   => null,
                  // entity header fields
                  'Allow'              => null,
                  'Content-Encoding'   => null,
                  'Content-Language'   => null,
                  'Content-Length'     => null,
                  'Content-Location'   => null,
                  'Content-MD5'        => null,
                  'Content-Range'      => null,
                  'Content-Type'       => null,
                  'Expires'            => null,
                  'Last-Modified'      => null,
                  'extension-header'   => null,
  );

  /**
   * Accepts the result of a curl request as a string
   *
   * <code>
   * $response = new CurlResponse(curl_exec($curl_handle));
   * echo $response->body;
   * echo $response->headers['Status'];
   * </code>
   *
   * @param string $response
   * */
  function __construct($response, $outstr = null) {
    if (isset($outstr)) {
      if (Curl::$debug) {
        $this->debug_log = \preg_replace('/^([^\*|>|<])/m', '> $1', $outstr);
      }
      if (Curl::$with_headers) {
        $outstr = preg_replace('/^\*.*$/m', '', $outstr);

        preg_match_all('/>[^<]*|<[^>]*/', $outstr, $matches);
        $matches = array_map(function ($a) {
                  return preg_replace('/<\s*|>\s*/m', '', $a);
                }, $matches[0]);
        $ttt  = $matches;
        if (($last = end($matches)) !== false) {
          $this->all_headers = $matches;
          # Extract headers from response
          preg_match_all('/\w.*$/m', end($this->all_headers), $matches);
          reset($this->all_headers);
          $headers = array_pop($matches);
          # Extract the version and status from the first header
          $status  = trim(array_shift($headers));
          $this->headers['Status-Line'] = $status;
          $status  = preg_split('/\s/', $status, 3);
          $this->headers['Http-Version'] = $status[0];
          $this->headers['Status-Code'] = $status[1];
          $this->headers['Reason-Phrase'] = $status[2];

          # Convert headers into an associative array
          foreach ($headers as $header) {
            preg_match('/(.*?)\:\s(.*)\r/', $header, $matches);
            $this->headers[$matches[1]] = $matches[2];
          }
        }
      }
    }

    $this->body = $response;
  }

  /**
   * Returns the response body
   *
   * <code>
   * $curl = new Curl;
   * $response = $curl->get('google.com');
   * echo $response;  # => echo $response->body;
   * </code>
   *
   * @return string
   * */
  public function __toString() {
    return $this->body;
  }

  /**
   * Determine if the response is html.
   * */
  public function isHtml() {
    $type = $this->headers['Content-Type'] ? : '';
    if (preg_match('/(x|ht)ml/i', $type)) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Determine if the response is html.
   * */
  public function isJson() {
    $type = $this->headers['Content-Type'] ? : '';
    if (preg_match('/json/i', $type)) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Retrieve the content type of the response.
   * */
  public function getMimeType() {
    $type = $this->headers['Content-Type'] ? : false;
    if ($type) {
      list($type) = explode(";", $type);
      $type = trim($type);
    }

    return $type;
  }
}
