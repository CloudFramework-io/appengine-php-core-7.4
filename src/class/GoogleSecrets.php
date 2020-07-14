<?php
/*
 */

use Google\Cloud\SecretManager\V1\Replication;
use Google\Cloud\SecretManager\V1\Replication\Automatic;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\SecretPayload;


/*
 * Based on: https://cloud.google.com/secret-manager/docs/reference/libraries#client-libraries-usage-php
 */

if (!defined ("_Google_CLASS_") ) {
    define("_Google_CLASS_", TRUE);

    class GoogleSecrets
    {

        var $core;
        var $error = false;
        var $errorMsg = [];

        var $client = null;
        var $projectPath = null;

        function __construct(Core7 &$core)
        {
            if(!getenv('PROJECT_ID')) return($this->addError('Missing PROJECT_ID env_var'));
            $this->client = new SecretManagerServiceClient();
            $this->projectPath = $this->client->projectName(getenv('PROJECT_ID'));
        }

        /**
         * Create a secret
         * @param $secretId
         * @return Secret|void
         * @throws \Google\ApiCore\ApiException
         */
        public function createSecret($secretId) {
            if($this->error) return;
            try {
                $secret = $this->client->createSecret($this->projectPath, $secretId,
                    new Secret([
                        'replication' => new Replication([
                            'automatic' => new Automatic(),
                        ]),
                    ])
                );
            } catch (Exception $e) {
                return($this->addError($e->getMessage()));
            }
            return $secret;
        }


        public function getSecret($secretId,$version='latest') {
            if($this->error) return;
            try {
                $secretName = $this->client->secretVersionName(getenv('PROJECT_ID'), $secretId,$version);
                $response = $this->client->accessSecretVersion($secretName);
                return($response->getPayload()->getData());
            } catch (Exception $e) {
                return($this->addError($e->getMessage()));
            }
        }


        /**
         * Add an error in the class
         * @param $value
         */
        function addError($value)
        {
            $this->error = true;
            $this->errorMsg[] = $value;
        }
    }
}
