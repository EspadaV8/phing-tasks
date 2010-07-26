<?php
require_once "phing/Task.php";

class CssValidate extends Task
{
    static const HTML           = 'html';
    static const SOAP           = 'soap12';
    static const TEXT           = 'text';

    static const CSS1           = 'css1';    
    static const CSS2           = 'css2';
    static const CSS21          = 'css21';
    static const CSS3           = 'css3';
    
    protected $_file            = null;
    protected $_uri             = null;
    protected $_validatorUrl    = 'http://jigsaw.w3.org/css-validator/validator';
    protected $_profile         = self::CSS21;

    private $_output            = self::SOAP;
    private $_soapClient        = null;
    private $_destDir           = null;
    private $_xslFile           = 'cssvalidate.xsl';

    public function init()
    {
    }

    public function main()
    {
        $params = array(
            'output'    => $this->_output,
            'profile'   => $this->_profile,
        );
        if( $this->_file !== null ) {
            print( 'Trying to validate: ' . $this->_file . PHP_EOL );
            $params['text'] = file_get_contents( $this->_file );
        }
        else if( $this->_uri !== null ) {
            print( 'Trying to URI: ' . $this->_uri . PHP_EOL );
            $params['uri'] = $this->_uri;
        } else {
            throw new Exception( "File: " . $this->_file . " - doesn't exist." );
        }
        
        $post = http_build_query($params);
        
        if( $this->_output == self::HTML )
        {
            $html = file_get_contents( $this->_validatorUrl . '?' . $post );
            return $this->saveHtmlOutput( $html );
        }
        else if( $this->_output == self::SOAP )
        {
            $xml = simplexml_load_file( $this->_validatorUrl . '?' . $post );
            return $this->processResponse( $xml );
        }
        else if( $this->_output == self::TEXT ) {
            return false;
        }
    }
    
    private function saveHtmlOutput( $html )
    {
        return file_put_contents( 'validate-css.html', $html );
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
    
    /**
     * Returns the path to the XSL stylesheet
     */
    private function getStyleSheet()
    {
        $xslname = 'cssvalidate.xsl';
        
        if( $this->styleDir )
        {
            $file = new PhingFile( $this->styleDir, $xslname );
        }
        else
        {
            $path = Phing::getResourcePath("phing/etc/$xslname");
            
            if ($path === NULL)
            {
                $path = Phing::getResourcePath("etc/$xslname");

                if ($path === NULL)
                {
                    throw new BuildException("Could not find $xslname in resource path");
                }
            }
            
            $file = new PhingFile($path);
        }

        if (!$file->exists())
        {
            throw new BuildException("Could not find file " . $file->getPath());
        }

        return $file;
    }



    public function setFile( $filename )
    {
        $this->_file = $filename;
    }
    
    public function setUri( $uri )
    {
        $this->_uri = $uri;
    }

    public function setProfile( $profile )
    {
        $this->_profile = $profile;
    }

    public function setValidatorUrl( $validatorUrl )
    {
        $this->_validatorUrl = $validatorUrl;
    }
    
    public function setDestDir( $destDir )
    {
        $this->_destDir = $destDir;
    }
}
