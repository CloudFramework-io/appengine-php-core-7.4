<?php
/**
 * Use $this->core->user object to handle authentication authentication
 */
class API extends RESTful
{
    var $user='test';           // User to use in the login
    var $password='password';   // Password to use in the login
    var $namespace='_apis';   // Namespace to associate the users
    var $end_point = '';

    /**
     * Main function
     */
    function main()
    {
        //You can restrict methods in main level
        if (!$this->checkMethod('GET,POST,PUT,DELETE')) return;

        //Call internal ENDPOINT_$end_point
        $this->end_point = str_replace('-', '_', ($this->params[0] ?? 'default'));
        if (!$this->useFunction('ENDPOINT_' . str_replace('-', '_', $this->end_point))) {
            return ($this->setErrorFromCodelib('params-error', "/{$this->service}/{$this->end_point} is not implemented"));
        }
    }

    /**
     * Endpoint to add a default feature. We suggest to use this endpoint to explain
     * how to use other endpoints
     */
    public function ENDPOINT_default()
    {
        // return Data in json format by default
        $this->addReturnData(
            [
                "end-point /default [current]"=>"use /{$this->service}/default"
                ,"end-point /hello"=>"use /{$this->service}/auth"
                ,"end-point /hello"=>"use /{$this->service}/check"
            ]);
    }

    /**
     * Endpoint to add a default feature. We suggest to use this endpoint to explain
     * how to use other endpoints
     */
    public function ENDPOINT_auth()
    {

        //region VERIFY user,password with a basic algorithm
        if(!$this->checkMandatoryFormParams(['user','password'])) return;
        if($this->formParams['user']!=$this->user) return($this->setErrorFromCodelib('security-error','wrong user. Use {"user":"test","password":"password"}'));
        if($this->formParams['password']!=$this->password) return($this->setErrorFromCodelib('security-error','wrong password. Use {"user":"test","password":"password"}'));
        //endregion

        //region CREATE $token and info will be saved in Memory Cache under $this->namespace
        $this->core->user->maxTokens = 2;  // Max tokens to be kept in memory
        if(!$this->core->user->createUserToken($this->formParams['user'],$this->namespace,['updated_at'=>date('Y-m-d H:i:s')])) return $this->setErrorFromCodelib('system-error',$this->core->user->errorMsg);
        //endregion

        //region SET response to 201 and return Data in json format by default
        $this->ok = 201;
        $this->addReturnData(
            [
                'token'=>$this->core->user->token,
                'expires'=>$this->core->user->tokenExpiration
            ]);
        //endregion

    }

    /**
     * Verify a token sent through X-WEB-KEY header
     * how to use other endpoints
     */
    public function ENDPOINT_check()
    {

        if(!$token = $this->getHeader('X-WEB-KEY')) return($this->setErrorFromCodelib('params-error','Missing X-WEB-KEY header'));
        if(!$this->core->user->checkUserToken($token))  return $this->setErrorFromCodelib('security-error',$this->core->user->errorMsg);


        // return Data in json format by default
        $this->addReturnData(
            [
                '$this->core->user->isAuth()'=>$this->core->user->isAuth(),
                '$this->core->user->id'=>$this->core->user->id,
                '$this->core->user->namespace'=>$this->core->user->namespace,
                '$this->core->user->token'=>$this->core->user->token,
                '$this->core->user->tokenExpiration'=>round($this->core->user->tokenExpiration),
                '$this->core->user->data'=>$this->core->user->data,
            ]);
    }
}