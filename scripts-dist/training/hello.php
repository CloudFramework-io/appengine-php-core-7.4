<?php
/**
 * https://cloudframework.io
 * Script Template
 */
class Script extends Scripts2020
{
    /**
     * This function is executed as the main method of the class
     */
    function main()
    {
        // We take parameter 2 to stablish the method to call when you execute: composer script hello/parameter-1/parameter-2/..
        // If the parameter 2 is empty we assign default by default :)
        $method = (isset($this->params[2])) ? $this->params[2] : 'default';
        // we convert - by _ because a method name does not allow '-' symbol
        $method = str_replace('-', '_', $method);

        //Call internal ENDPOINT_{$method}
        if (!$this->useFunction('METHOD_' . $method)) {
            return ($this->setErrorFromCodelib('params-error', "/{$method} is not implemented"));
        }
    }

    /**
     * This method is called from the main method taking the parameters of command line: composer script hello
     */
    function METHOD_default()
    {
        $this->sendTerminal("Available methods (use {$this->params[0]}/{$this->params[1]}/{method}):");
        $this->sendTerminal(" - {$this->params[0]}/{$this->params[1]}/hello");
    }

    /**
     * This method is called from the main method taking the parameters of command line: composer script hello/test
     */
    function METHOD_hello()
    {
        $this->sendTerminal('This is a Hello World :)');

    }
}