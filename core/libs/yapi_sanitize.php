<?php

class YAPI_Sanitize
{
    const TYPE_STRING       = 0;
    const TYPE_NUMBER       = 0;
    const TYPE_INTEGER      = 0;
    const TYPE_FLOAT        = 0;
    const TYPE_EMAIL        = 1;
    const TYPE_URL          = 2;
    const TYPE_ARRAY        = 3;
    const TYPE_ALPHADASH    = 4;
    
    private $method = "POST";
    
    public function __construct( $method = "POST" )
    {
        $this->method = ( $method === "POST" ) ? "POST" : "GET";
    }
    
    private function getInput( $field )
    {
        if ( isset( $_GET[$field] ) || isset( $_POST[$field] ) ) {
            return ( $this->method === "POST" ) ? $_POST[$field] : $_GET[$field];
        } else {
            return false;
        }
    }
    
    // required
    // minlength
    // maxlength
    // exactlength
    // greater_than
    // greater_or_equal
    // less_than
    // less_or_equal
    // alpha
    // alpha_num
    // alpha_num_space
    // alpha_dash
    // numeric
    // integer
    // float
    // natural
    // no_zero
    // valid_url
    // valid_email
    // valid_base64
}