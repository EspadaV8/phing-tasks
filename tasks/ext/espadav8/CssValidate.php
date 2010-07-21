<?php
require_once "phing/Task.php";

class CssValidate extends Task
{
    protected $_file = null;
    protected $_validatorUrl = 'http://jigsaw.w3.org/css-validator/validator';
    protected $_profile = 'css21';

    private $_output = 'soap12';
    private $_soapClient = null;

    public function init()
    {
    }

    public function main()
    {
        if( empty( $this->_file ) )
        {
            throw new Exception( 'Empty file.' );
        }
        else if( !file_exists( $this->_file ) )
        {
            throw new Exception( "File: " . $this->_file . " - doesn't exist." );
        }
        print( 'Trying to validate: ' . $this->_file . PHP_EOL );

        $params = array(
            'text'      => file_get_contents( $this->_file ),
            'output'    => $this->_output,
            'profile'   => $this->_profile,
        );
        $post = http_build_query($params);

        $xml = simplexml_load_file( $this->_validatorUrl . '?' . $post );

        $this->processResponse( $xml );
    }

    private function processResponse( $xml )
    {
        $xml -> registerXPathNamespace('env', 'http://www.w3.org/2003/05/soap-envelope');
        $xml -> registerXPathNamespace('m', 'http://www.w3.org/2005/07/css-validator');

        $errorCount = $xml->xpath('/env:Envelope/env:Body/m:cssvalidationresponse/m:result/m:errors/m:errorcount');
        $warningCount = $xml->xpath('/env:Envelope/env:Body/m:cssvalidationresponse/m:result/m:warnings/m:warningcount');

        //print( $output . PHP_EOL );
        echo 'Errors: ' . (string)$errorCount[0] . PHP_EOL;
        echo 'Warnings: ' . (string)$warningCount[0] . PHP_EOL;
    }


    public function setFile( $filename )
    {
        $this->_file = $filename;
    }

    public function setProfile( $profile )
    {
        $this->_profile = $profile;
    }

    public function setValidatorUrl( $validatorUrl )
    {
        $this->_validatorUrl = $validatorUrl;
    }
}
