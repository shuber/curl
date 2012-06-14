<?php

class CurlTest extends ztest\UnitTestCase {
    
    function setup() {
        $this->curl = new Curl;
        $this->response = $this->curl->get('www.google.com');
    }
    
    function test_get() {
        assert_matches('#google#', $this->response);
        assert_equal(200, $this->response->headers['Status-Code']);
    }
    
    function test_error() {
        assert_throws('CurlException', function() {
          $curl = new Curl();
          $curl->get('diaewkaksdljf-invalid-url-dot-com.com');
        });
    }
    
}