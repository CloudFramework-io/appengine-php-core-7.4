<?php
// https://cloud.google.com/appengine/docs/standard/php/mail/sending-receiving-with-mail-api
// It requires in app.yaml
/*
inbound_services:
- mail
- mail_bounce
 */
$_include = '';
$_email = file_get_contents('php://input');
$_ah = $this->config->get('_ah');
if($_email && is_array($_ah)) foreach ($_ah as $_emailfrom=>$_include_file) {
    if(strpos($_email,$_emailfrom)!== false) {
        $_include = $_include_file;
        break;
    }
}
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
                    $this->core->logs->add(['mail'=>$this->formParams['_raw_input_']],'_ah_mail');
                    //Incoming mail
                    break;
                case "bounce":
                    $this->core->logs->add(['bounce'=>$this->formParams['_raw_input_']],'_ah_bouce');
                    //bounce email
                    break;
                default:
                    $this->core->logs->add([$this->params[0]=>$this->formParams['_raw_input_']],'_ah_none');
                    //unknown
                    break;
            }

        }
    }
}
