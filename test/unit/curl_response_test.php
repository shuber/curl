<?php

class CurlResponseTest extends ztest\UnitTestCase {
    
    function setup() {
        $this->curl = new Curl;
        $this->response = $this->curl->get('www.google.com');
    }
    
    function test_should_separate_response_headers_from_body() {
        ensure(is_array($this->response->headers));
        assert_matches('#^<!doctype#', $this->response->body);
    }
    
    function test_should_set_status_headers() {
        assert_equal(200, $this->response->headers['Status-Code']);
        assert_equal('200 OK', $this->response->headers['Status']);
    }
    
    function test_should_return_response_body_when_calling_toString() {
        ob_start();
        echo $this->response;
        assert_equal($this->response->body, ob_get_clean());
    }
    
}