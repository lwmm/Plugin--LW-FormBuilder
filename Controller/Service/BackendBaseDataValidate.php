<?php

/**
 * Informations of the add/edit form will be checked if it's a valid input.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_formbuilder
 */

namespace LwFormBuilder\Controller\Service;

define("REQUIRED", "1");    # array( 1 => array( "error" => 1, "options" => "" ));
define("MAXLENGTH", "2");   # array( 2 => array( "error" => 1, "options" => array( "maxlength" => $maxlength, "actuallength" => $strlen ) ));
define("YEAR", "3");        # array( 3 => array( "error" => 1, "options" => array( "enteredyear" => $year ) ));
define("DATE", "4");        # array( 4 => array( "error" => 1, "options" => array( "entereddate" => $date ) ));  [$date = JJJJMMDD]
define("EMAIL", "5");       # array( 5 => array( "error" => 1, "options" => "" ));
define("DIGITFIELD", "6");  # array( 6 => array( "error" => 1, "options" => "" ));
define("ZIP", "7");         # array( 7 => array( "error" => 1, "options" => "" ));
define("PAYMENT", "8");     # array( 8 => array( "error" => 1, "options" => "" ));
define("BOOL", "9");        # array( 9 => array( "error" => 1, "options" => "" ));
define("MAINTEXTANDPAGEID", "10");# array( 10 => array( "error" => 1, "options" => "" ));
define("TODATELOWERFROMDATE", "11");# array( 11 => array( "error" => 1, "options" => "" ));
define("URL", "12");        # array( 12 => array( "error" => 1, "options" => "" ));
define("NOTALLOWEDFILEEXTENSION", "13");        # array( 12 => array( "error" => 1, "options" => "" ));

class BackendBaseDataValidate
{

    public function __construct()
    {
        $this->allowedKeys = array(
            "mail_sender",             
            "mail_reciever",         
            "url_error",                 
            "url_success",             
            "max_registrations", 
            "url_full");

        $this->errors = array();
    }

    public function setValues($array)
    {
        $this->array = $array;
    }

    public function validate()
    {
        $valid = true;
        foreach ($this->allowedKeys as $key) {
            $function = $key . "Validate";
            $result = $this->$function($this->array[$key]);
            if ($result == false) {
                $valid = false;
            }
        }
        return $valid;
    }

    private function addError($key, $number, $array = false)
    {
        $this->errors[$key][$number]['error'] = 1;
        $this->errors[$key][$number]['options'] = $array;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getErrorsByKey($key)
    {
        return $this->errors[$key];
    }

    private function mail_senderValidate($value)
    {
        if($this->eMailValidation("mail_sender", $value)){
            return true;
        }
        return false;
    }
    
    private function mail_recieverValidate($value)
    {
        $explodedReciever = explode(PHP_EOL, $value);
        
        foreach($explodedReciever as $reciever){
            if(!$this->eMailValidation("mail_reciever", trim($reciever))){
                return false;
            }
        }
        return true;
    }
    
    private function url_errorValidate($value)
    {
        if($this->urlValidation("url_error", $value)){
            return true;
        }
        return false;
    }
    
    private function url_successValidate($value)
    {
        if($this->urlValidation("url_success", $value)){
            return true;
        }
        return false;
    }
    
    private function url_fullValidate($value)
    {
        if($this->urlValidation("url_full", $value)){
            return true;
        }
        return false;
    }
    
    private function max_registrationsValidate($value)
    {
        if(!intval($value)){
            $this->addError("max_registrations", 6);
            return false;
        }
        return true;
    }
    

    private function defaultValidation($key, $value, $length)
    {
        $bool = true;

        if (strlen($value) > $length) {
            $this->addError($key, 2, array("maxlength" => $length, "actuallength" => strlen($value)));
            $bool = false;
        }

        if ($bool == false) {
            return false;
        }
        return true;
    }

    private function requiredValidation($key, $value)
    {
        if ($value == "") {
            $this->addError($key, 1);
            return false;
        }
        return true;
    }
    
    private function eMailValidation($key, $value)
    {
        if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
            $this->addError($key, 5);
            return false;
        }
        return true;
    }
    
    private function urlValidation($key, $value)
    {
        if (!filter_var(trim($value), FILTER_VALIDATE_URL)) {
            $this->addError($key, 12);
            return false;
        }
        return true;
    }

}