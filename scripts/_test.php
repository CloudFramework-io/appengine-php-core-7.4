<?php

/*
 * https://www.notion.so/cloudframework/TEST-JSON-FILE-d13852c566084eaaa4914b6723319f67
 */
class Script extends Scripts2020
{

    var $test = [];
    var $run_vars = [];
    var $user_email = '';
    var $user_token = '';

    function main()
    {

        $method = (isset($this->params[1])) ? $this->params[1] : 'default';
        if (!is_dir('./local_data')) mkdir('./local_data');
        $this->cache->debug = false;
        //$this->cache_secret_key = 'test';
        //$this->cache_secret_iv = 'test';


        // $this->core->cache->debug = false;
        //Call internal ENDPOINT_{$this->params[1]}
        $method = str_replace('-', '_', $method);
        if (!$this->useFunction('METHOD_' . $method)) {
            return ($this->setErrorFromCodelib('params-error', "/{$method} is not implemented"));
        }

        // Clean-up logs when everything is OK.
        if (!$this->error) $this->core->logs->data = null;
        $this->core->errors->data = null;
        $this->sendTerminal('');
        $this->prompt('Enter to finish');
    }

    /**
     * Default method for information
     */
    public function METHOD_default()
    {
        $this->sendTerminal('Available methods:');
        $this->sendTerminal(' - _test/json/{json_test_with_no_json_extension}  (to start bnext tests)');
        $this->sendTerminal(' - _test/clean  (to start bnext tests)');
    }

    /**
     * Default method for information
     */
    public function METHOD_clean()
    {
        $this->sendTerminal('Cleaning cache data: rm ./local_data/cache/*');
        exec('rm ./local_data/cache/*');
    }

    /**
     * Bnext test
     */
    public function METHOD_json() {


        //region SET $area
        if(!isset($this->params[2]) || !$this->params[2]) {
            $this->params[2] = $this->prompt('Write the name of your organization test/json/');
        }
        $test = $this->params[2];
        //endregion


        if(!$this->checkCloudFrameworkUserCredentials($test)) return;


        if(!$this->loadTest($test)) return;
        $this->run_vars = ($this->cache->get('CloudFramework_test_run_vars_'.$test))?:[];

        //region SET $area
        if(!isset($this->params[3]) || !$this->params[3]) {
            $this->sendTerminal('You can Use:');
            foreach ($this->test as $key=>$foo) if($key[0]!='_') {
                $this->sendTerminal('  - test/json/'.$test.'/'.$key);
            }
            $this->params[3] = $this->prompt('Select area to test test/json/'.$test.'/');
        }
        $area = $this->params[3];
        if(!isset($this->test[$area])) return($this->addError($area.' does not exist'));
        //endregion

        //region set $test
        if(!isset($this->params[4]) || !$this->params[4]) {
            $this->sendTerminal('You can Use:');
            foreach ($this->test[$area] as $key=>$foo) {
                $this->sendTerminal('  - test/json/'.$test.'/'.$area.'/'.$key);
            }
            $this->params[4] = $this->prompt('Select area to test test/json/'.$test.'/'.$area.'/');
        }
        $subarea = $this->params[4];
        if(!isset($this->test[$area][$subarea])) return($this->sendTerminal($area.'/'.$subarea.' does not exist in '.$test));
        //endregion

        $this->runTest($area,$subarea);

    }

    /**
     * Check a token with CloudFramework ERP platform
     * @param $token
     * @return bool[]|false[]|mixed|string[]|null
     */
    private function checkToken($token,$organization) {
        $url = "https://api.cloudframework.io/core/signin/{$organization}/check";
        $user_data = $this->core->request->get_json_decode($url,null,['X-WEB-KEY'=>'Production','X-DS-TOKEN'=>$token]);
        if($this->core->request->error) {
            $this->sendTerminal('Current CF token does not work or has expired.');
            $token=null;
            $this->core->request->error=false;
            $this->core->request->errorMsg=[];
            return null;
        }
        $this->cache_secret_key = md5(json_encode($user_data));
        $this->cache_secret_iv = $user_data['data']['User']['UserEmail'];
        $this->user_email = $user_data['data']['User']['UserEmail'];
        $this->user_token = $token;
        $this->sendTerminal('user confirmed: '.$this->user_email);
        return($user_data);
    }

    /**
     * Generate a new token
     * @return bool[]|false[]|mixed|string[]|null
     */
    private function generateCloudFrameworkToken() {
        $this->sendTerminal('Sign in ERP/Backoffice '.$this->params[2]);
        $user = $this->prompt(' - Give me your user: ',null,'user');
        $password = $this->prompt(' - Give me your password: ');

        $url = "https://api.cloudframework.io/core/signin/{$this->params[2]}/in";
        $params = ['user'=>$user,'password'=>$password,'type'=>'userpassword'];
        $user_data = $this->core->request->post_json_decode($url,$params,['X-WEB-KEY'=>'Production']);
        if($this->core->request->error) {
            $this->core->request->error=false;
            $this->core->request->errorMsg=[];
            return($this->sendTerminal(' Error user/password'));
        }
        $token = $user_data['data']['dstoken'];
        $this->cache->set('token',$token);
        $this->sendTerminal(' - new token generated');
        return($token);
    }

    /**
     * Check if there are right credentials to run this test script
     */
    private function checkCloudFrameworkUserCredentials($organization) {

        $this->sendTerminal('Verifying user credentials');
        // Get Cloudframework token
        $token = $this->cache->get('token');
        $user_data = [];
        if($token && !($user_data = $this->checkToken($token,$organization))) $token = null;

        if(!$token) {
            if(!($token = $this->generateCloudFrameworkToken())) return;
            if(!($user_data = $this->checkToken($token,$organization))) return;
        }

        return true;
    }

    /**
     * Execute a test previously readed
     * @param $test
     */
    private function runTest($area,$test) {

        if(!isset($this->test[$area][$test])) return($this->addError($area.'/'.$test.' does not exist'));

        //region Evaluate prompt variables: "prompt_vars": { .. }
        if(isset($this->test[$area][$test]['prompt_vars']) && is_array($this->test[$area][$test]['prompt_vars'])) {
            foreach ($this->test[$area][$test]['prompt_vars'] as $prompt=>$prompt_description) {
                // extract mandatory values $mandatory_values
                $mandatory_values = (isset($prompt_description['values']) && $prompt_description['values'] && is_array($prompt_description['values']))?$prompt_description['values']:[];

                // Assign title with default value
                $title = ((isset($prompt_description['title']))?$prompt_description['title']:$prompt);
                if($mandatory_values) $title.= ' '.json_encode($prompt_description['values']);
                $title.=': ';
                $default_value = (isset($prompt_description['defaultvalue']))?$prompt_description['defaultvalue']:'';

                // Do de prompt
                do {
                    $this->test[$area][$test]['vars'][$prompt]['value']  = $this->prompt($title,$default_value,"{$area}{$test}{$prompt}");
                } while($mandatory_values && !in_array($this->test[$area][$test]['vars'][$prompt]['value'],$mandatory_values));

            }
        }
        //endregion

        //region Merge _default vars with test vars: "_default": { "vars": { .. } }"
        if(!isset($this->test['_default']['vars']) || !is_array($this->test['_default']['vars'])) $this->test['_default']['vars'] = [];
        if(!isset($this->test[$area][$test]['vars']) || !is_array($this->test[$area][$test]['vars'])) $this->test[$area][$test]['vars'] = [];

        $this->test[$area][$test]['vars'] = array_merge($this->test['_default']['vars'],$this->test[$area][$test]['vars']);
        $this->test[$area][$test]['vars'] = array_merge($this->test[$area][$test]['vars'],$this->run_vars);
        if(isset($this->test[$area][$test]['vars'])) {
            $vars_txt = json_encode($this->test[$area][$test]['vars']);
            if(strpos($vars_txt,'{{') && strpos($vars_txt,'}}')) {
                foreach ($this->test[$area][$test]['vars'] as $var=>$content) if(strpos($vars_txt,'{{'.$var)) {
                    if(isset($this->test[$area][$test]['vars'][$var]['value']))
                        $vars_txt = str_replace('{{'.$var.'}}',$this->test[$area][$test]['vars'][$var]['value'],$vars_txt);
                }
                $this->test[$area][$test]['vars'] = json_decode($vars_txt,true);
            }
        }
        //endregion

        //region execute "calls": [ {..},{..} ]
        if(isset($this->test[$area][$test]['calls']) && is_array($this->test[$area][$test]['calls'])) {
            foreach ($this->test[$area][$test]['calls'] as $i=>$content) {

                //region verify mandatory fields: $content['url']
                if(!isset($content['url']) || !$content['url']) {
                    $this->sendTerminal('ERROR. Missing url in call: '.$i);
                    continue;
                }
                //endregion

                //region apply {{var}} substitutions in $content
                $content_txt = json_encode($content);
                if(strpos($content_txt,'{{') && strpos($content_txt,'}}')) {
                    foreach ($this->test[$area][$test]['vars'] as $var=>$var_content) if(strpos($content_txt,'{{'.$var)) {
                        if(isset($this->test[$area][$test]['vars'][$var]['value']))
                            $content_txt = str_replace('{{'.$var.'}}',$this->test[$area][$test]['vars'][$var]['value'],$content_txt);
                    }
                    $content = json_decode($content_txt,true);
                }
                //endregion

                //region SET: $url,$payload,$method,$headers
                $url = $content['url'];
                $title = (isset($content['title']) && $content['title'])? $content['title']:null;
                $payload = (isset($content['payload']) && $content['payload'])? $content['payload']:null;
                $method = (isset($content['method']) && in_array(strtoupper($content['method']),['GET','POST','PUT','PATCH']))?strtoupper($content['method']):'GET';
                $headers = (isset($content['headers']) && is_array($content['headers']))?$content['headers']:null;
                //endregion

                //region GET,POST,PUT..: $url.
                $this->sendTerminal();
                $this->sendTerminal("{$area}/{$test} {$title}");
                $ret = $this->core->request->call($url,$payload, $method,$headers);
                if($this->core->request->error) {
                    $this->sendTerminal(' - RESPONSE NOT OK '.$this->core->request->getLastResponseCode());
                } else {
                    $this->sendTerminal(' - OK '.$this->core->request->getLastResponseCode());
                }
                //TODO: send report to CloudFramework.
                //endregion

                //region EVALUATE status
                if(isset($content['status']) && $content['status']) {
                    if($this->core->request->getLastResponseCode() == $content['status']) {
                        $this->sendTerminal(' - OK Status is '.$content['status']);
                    } else {
                        $this->sendTerminal(' - ERROR Status is not '.$content['status']);
                    }
                }
                //endregion

                //region EVALUATE check-json-values
                if(isset($content['check-json-values']) && is_array($content['check-json-values'])) {
                    $ret_array = $this->core->jsonDecode($ret);

                    foreach ($content['check-json-values'] as $check_var=>$check_var_content) {
                        $array_path = explode('.',$check_var);

                        $pointer = $ret_array;
                        foreach ($array_path as $item) {
                            if(isset($pointer[$item])) {
                                $pointer = &$pointer[$item];
                            }
                            else {
                                $pointer = null;
                                $this->sendTerminal(' - ERROR returned structure. Does not exist: '.$check_var.'=>'.json_encode($check_var_content));
                                break;
                            }
                        }
                        if($pointer) {
                            $this->sendTerminal(' - OK JSON Var exist: '.$check_var.'=>'.json_encode($check_var_content));
                        }
                        unset($pointer);
                    }
                }
                //endregion

                //region EVALUATE set-run-vars
                if(isset($content['set-run-vars']) && is_array($content['set-run-vars'])) {
                    $ret_array = $this->core->jsonDecode($ret);

                    foreach ($content['set-run-vars'] as $check_var=>$check_var_content) {
                        $array_path = explode('.',$check_var_content);

                        $pointer = &$ret_array;
                        foreach ($array_path as $item) {
                            if(isset($pointer[$item])) {
                                $pointer = &$pointer[$item];
                            }
                            else {
                                $pointer = null;
                                $this->sendTerminal('  ERROR returned structure. It can not create set-run-var: '.$check_var.'=>'.$check_var_content);
                            }
                        }
                        if($pointer) {
                            $this->run_vars[$check_var] = ['value'=>$pointer];
                            $this->cache->set('CloudFramework_test_run_vars_'.$this->params[1], $this->run_vars);
                            $this->sendTerminal(' - OK Updated set-run-var "'.$check_var.'" <- {'.$check_var_content.'}: '.$pointer);
                        }
                        unset($pointer);
                    }
                    $this->test[$area][$test]['vars'] = array_merge($this->test[$area][$test]['vars'],$this->run_vars);

                }
                //endregion

            }
        }
        //endregion
    }

    /**
     * Load JSON test file description
     * @param $test
     * @return bool|void
     */
    private function loadTest($test) {
        $file = $this->core->system->app_path.'/tests/'.$test.'.json';
        if(!is_file($file))
            return($this->sendTerminal($test.' does not exist'));

        $this->test = json_decode(file_get_contents($file),true);
        return true;
    }
}
