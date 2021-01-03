<?php

class API extends RESTful
{
    function main()
    {
        //Call internal ENDPOINT_$end_point
        $end_point = (isset($this->params[1]))?str_replace('-','_',$this->params[1]):'default';
        if(!$this->useFunction('ENDPOINT_'.$end_point)) {
            return($this->setErrorFromCodelib('params-error',"/{$this->params[0]}/{$end_point} is not implemented"));
        }
    }

    /**
     * Endpoint to add a default feature
     */
    public function ENDPOINT_default()
    {
        $this->addReturnData('Advanced hello World');
    }
}
