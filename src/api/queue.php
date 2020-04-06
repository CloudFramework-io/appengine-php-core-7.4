<?php
// based on: https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/appengine/php72/tasks/snippets/src/create_task.php
// activate API at: https://console.developers.google.com/apis/api/cloudtasks.googleapis.com/overview?project={{PROJECT-ID}}
// Task to use in background
use Google\Cloud\Tasks\V2\AppEngineHttpRequest;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\Task;


class API extends RESTful
{
    function main()
    {

        // Allow ajax calls
        $this->sendCorsHeaders();
        if(isset($this->formParams['_raw_input_'])) unset($this->formParams['_raw_input_']);

        // CALL URL and wait until the response is received
        if (isset($this->formParams['interactive'])) {

            //Delete variable
            unset($this->formParams['interactive']);

            // In interactive we use CloudService Class to send and receive data with http...
            $_url = str_replace('/queue/', '/', urldecode($this->core->system->url['host_url_uri']));

            // Requires to create a complete URL
            $value['url_queued'] = $_url;
            $value['interative'] = true;
            $value['headers'] = $this->getHeadersToResend();

            // Avoid to send automatica Headers.
            $this->core->request->automaticHeaders = false;
            switch ($this->method) {
                case "GET":
                    $value['data_received'] = $this->core->request->get($_url, $this->formParams, $value['headers']);
                    break;
                case "POST":
                    $value['data_received'] = $this->core->request->post($_url, $this->formParams, $value['headers']);
                    break;
                case "PUT":
                    $value['data_received'] = $this->core->request->put($_url, $this->formParams, $value['headers']);
                    break;
                case "DELETE":
                    $value['data_received'] = $this->core->request->delete($_url,$value['headers']);
                    break;
            }

            // Data Received
            if ($value['data_received'] === false) $value['data_received'] = $this->core->errors->data;
            else $value['data_received'] = json_decode($value['data_received']);

        } // RUN THE TASK
        else {

            if(!getenv('PROJECT_ID')) return($this->setErrorFromCodelib('system-error','missing PROJECT_ID env_var'));

            // use: gcloud tasks locations list to get valid locations
            if(!getenv('LOCATION_ID')) return($this->setErrorFromCodelib('system-error','missing LOCATION_ID env_var'));

            // default if empty
            if(!getenv('QUEUE_ID')) return($this->setErrorFromCodelib('system-error','missing QUEUE_ID env_var'));

            $client = new CloudTasksClient();
            $queueName = $client->queueName(getenv('PROJECT_ID'),getenv('LOCATION_ID'),getenv('QUEUE_ID'));

            $_url = str_replace('/queue/', '/', urldecode($this->core->system->url['url_uri']));

            // Create an App Engine Http Request Object.
            $httpRequest = new AppEngineHttpRequest();


            // adding forms params
            $this->formParams['cloudframework_queued'] = true;
            $this->formParams['cloudframework_queued_id'] = uniqid('queue', true);
            $this->formParams['cloudframework_queued_ip'] = $this->core->system->ip;
            $this->formParams['cloudframework_queued_fingerprint'] = json_encode($this->core->system->getRequestFingerPrint(), JSON_PRETTY_PRINT);

            // Add special vars to the url if the method is not POST,PUT,PATCH
            if(!in_array($this->method,["POST","PUT","PATCH"])) {
                $_url.=(strpos($_url,'?'))?'&':'?';
                $_url.="cloudframework_queued=1&cloudframework_queued_id={$this->formParams['cloudframework_queued_id']}&cloudframework_queued_ip={$this->formParams['cloudframework_queued_ip']}";
            }

            // The path of the HTTP request to the App Engine service.
            $httpRequest->setRelativeUri($_url);

            $payload = json_encode($this->formParams);

            // POST is the default HTTP method, but any HTTP method can be used.
            switch ($this->method) {
                case "GET":
                    $httpRequest->setHttpMethod(HttpMethod::GET);
                    break;
                case "POST":
                    $httpRequest->setHttpMethod(HttpMethod::POST);
                    // Setting a body value is only compatible with HTTP POST and PUT requests.
                    if (isset($payload)) {
                        $httpRequest->setBody($payload);
                    }
                    break;
                case "PUT":
                    $httpRequest->setHttpMethod(HttpMethod::PUT);
                    // Setting a body value is only compatible with HTTP POST and PUT requests.
                    if (isset($payload)) {
                        $httpRequest->setBody($payload);
                    }
                    break;
                case "PATCH":
                    $httpRequest->setHttpMethod(HttpMethod::PATCH);
                    // Setting a body value is only compatible with HTTP POST and PUT requests.
                    if (isset($payload)) {
                        $httpRequest->setBody($payload);
                    }
                    break;
                case "DELETE":
                    $httpRequest->setHttpMethod(HttpMethod::DELETE);
                    break;
                case "OPTIONS":
                    $httpRequest->setHttpMethod(HttpMethod::OPTIONS);
                    break;
            }

            $httpRequest->setHeaders($this->getHeadersToResend());

            // Create a Cloud Task object.
            $task = new Task();
            $task->setAppEngineHttpRequest($httpRequest);

            // Send request and print the task name.
            $response = $client->createTask($queueName, $task);
            $this->core->logs->add('Task created: '.$response->getName(),'task_created');

            $value['url_queued'] = $_url;
            $value['method'] = $this->method;
            $value['data_sent'] = $this->formParams;

        }

        $this->addReturnData($value);
    }
}
