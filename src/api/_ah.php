<?php
// https://cloud.google.com/appengine/docs/standard/php/mail/sending-receiving-with-mail-api
// It requires in app.yaml
// last-update: 2020-09
/*
inbound_services:
- mail
- mail_bounce
 */

// Read email source
$_include = '';
$_email = file_get_contents('php://input');

// Evaluate if there is a var: '_ah' in config vars with the structure ['emai_source1'=>'file_to_include', 'emai_source2'=>'file_to_include']
$_ah = $this->config->get('_ah');
if($_email && is_array($_ah)) foreach ($_ah as $_emailfrom=>$_include_file) {
    if(strpos($_email,$_emailfrom)!== false) {
        $_include = $_include_file;
        break;
    }
}

//If there is not file to include evaluate if there is a var: _ah_default_include to include that file by default
if(!$_include && $this->config->get('_ah_default_include')) {
    $_include =$this->config->get('_ah_default_include');
}

// Include the file if found and if not sent the output to logs.
if($_include) {
    include_once $_include;
} else {
    class API extends RESTful
    {
        function main()
        {
            if(!isset($this->params[0])) $this->params[0]='none';
            switch ($this->params[0]) {
                case "mail":
                    $this->core->logs->add($this->formParams['_raw_input_'],'_ah_mail');
                    //Incoming mail
                    break;
                case "bounce":
                    $this->core->logs->add($this->formParams['_raw_input_'],'_ah_bouce');
                    //bounce email
                    break;
                default:
                    $this->core->logs->add($this->formParams['_raw_input_'],'_ah_'.$this->params[0]);
                    //unknown
                    break;
            }

        }
    }
}
