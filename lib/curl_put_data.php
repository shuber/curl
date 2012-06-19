<?php
/**
 * A File handler class for curl PUT-data
 *
 * See the README for documentation/examples or http://php.net/curl for more information about the libcurl extension for PHP
 *
 * @package curl
 * @author Fabian Grassl
**/
class CurlPutData {

  /**
   * Stores resource handle for the file
   *
   * @var resource
   * @access protected
  **/
  protected $resource = null;

  /**
   * Stores file-size of the file
   *
   * @var integer
   * @access protected
  **/
  protected $resource_size = null;

  /**
   * Initializes a CurlPutData object
   *
   * @param resource $resource resource
   * @param resource $resource_size resource-size in bytes
   * @see CurlPutData::fromFile()
   * @see CurlPutData::fromString()
  **/
  public function __construct($resource, $resource_size)
  {
    $this->resource = $resource;
    $this->resource_size = $resource_size;
  }

  /**
   * Create CurlPutData object from file
   *
   * @param string $filename filename & path
  **/
  public static function fromFile($filename)
  {
    $resource_size = filesize($filename);
    $resource = fopen($filename, 'r');
    return new self($resource, $resource_size);
  }

  /**
   * Create CurlPutData object from string
   *
   * @param string $string content
  **/
  public static function fromString($string)
  {
    $resource_size = strlen($string);
    $resource = tmpfile();
    fwrite($resource, $string);
    fseek($resource, 0);
    return new self($resource, $resource_size);
  }

   /**
   * Get file-resource
  **/
 public function getResource()
  {
    return $this->resource;
  }

   /**
   * Get file-resource size
  **/
  public function getResourceSize()
  {
    return $this->resource_size;
  }

  public function __destruct()
  {
    fclose($this->resource);
  }

}