<?php

class API extends RESTful
{
    function main()
    {
        //Call internal ENDPOINT_$end_point
        $end_point = (isset($this->params[0]))?str_replace('-','_',$this->params[0]):'default';
        if(!$this->useFunction('ENDPOINT_'.$end_point)) {
            return($this->setErrorFromCodelib('params-error',"/{$this->params[1]} is not implemented"));
        }
    }

    /**
     * Endpoint to add a default feature
     */
    public function ENDPOINT_default()
    {
        // Verify you have set-up the datastore environment

        if($this->core->is->development()) {

        }
        $this->addReturnData('Advanced hello World');
    }
}
