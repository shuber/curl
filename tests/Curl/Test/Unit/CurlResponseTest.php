<?php

namespace Curl\Test\Unit;

use shuber\Curl\Curl;

class CurlResponseTest extends \PHPUnit_Framework_TestCase {

    function setUp() {
        $this->curl = new Curl;
        $this->response = $this->curl->get('www.google.com');
    }

    function test_should_separate_response_headers_from_body() {
        $this->assertTrue(is_array($this->response->headers));
        $this->assertRegExp('/<!doctype/', $this->response->body);
    }

    function test_should_set_status_headers() {
        $this->assertEquals(200, $this->response->headers['Status-Code']);
        $this->assertEquals('200 OK', $this->response->headers['Status']);
    }

    function test_should_return_response_body_when_calling_toString() {
        ob_start();
        echo $this->response;
        $this->assertEquals($this->response->body, ob_get_clean());
    }

}