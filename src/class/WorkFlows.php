<?php


/**
 * [$cfos = $this->core->loadClass('CFOs');] Class CFOs to handle CFO app for CloudFrameworkInterface
 * https://www.notion.so/cloudframework/CFI-PHP-Class-c26b2a1dd2254ddd9e663f2f8febe038
 * Mandrill references: https://mailchimp.com/developer/transactional/api/
 * last_update: 202201
 * @package CoreClasses
 */
class WorkFlows
{

    var $version = '20230122';
    /** @var Core7 */
    var $core;
    /** @var Mandrill */
    var $mandrill;
    var $error = false;                 // When error true
    var $errorMsg = [];                 // When error array of messages

    /**
     * DataSQL constructor.
     * @param Core $core
     * @param array $model where [0] is the table name and [1] is the model ['model'=>[],'mapping'=>[], etc..]
     */
    function __construct(Core7 &$core, $params = [])
    {
        $this->core = $core;
        //region Create a
        require_once $this->core->system->root_path.'/vendor/mandrill/mandrill/src/Mandrill.php'; //Not required with Composer

        if(($params['mandrill_api_key']??null)) $this->setMandrillApiKey($params['mandrill_api_key']);
    }

    /**
     * SET API for mandrill interation and SETUP $this->mandrill
     * @param $apiKey
     * @throws Mandrill_Error
     */
    public function setMandrillApiKey($apiKey) {
        $this->mandrill = new Mandrill($apiKey);
    }

    /**
     * SET API for mandrill interation and SETUP $this->mandrill
     * @param $label
     * @return array|void if there is no error it returns the array of templates in the server
     */
    public function getMandrillTemplates($label=null) {
        if(!$this->mandrill) return $this->addError('Missing Mandrill API_KEY. use function setMandrillApiKey($pau_key)');
        try {
            $templates = $this->mandrill->templates->getList($label);
            /**
            [0] => slug
            [1] => name
            [2] => code
            [3] => publish_code
            [4] => published_at
            [5] => created_at
            [6] => updated_at
            [7] => draft_updated_at
            [8] => publish_name
            [9] => labels
            [10] => text
            [11] => publish_text
            [12] => subject
            [13] => publish_subject
            [14] => from_email
            [15] => publish_from_email
            [16] => from_name
            [17] => publish_from_name
             */
            return $templates;
        } catch (Error $e) {
            return $this->addError($e->getMessage());
        }
    }

    /**
     * Retrive a email template with if $slug
     * @param $slug
     * @return array|void if there is no error it returns the array of templates in the server
     */
    public function getMandrillTemplate($slug)
    {
        if(!$this->mandrill) return $this->addError('Missing Mandrill API_KEY. use function setMandrillApiKey($pau_key)');
        try {
            $template = $this->mandrill->templates->info($slug);
        } catch (Error $e) {
            return $this->addError($e->getMessage());
        }

        return $template;

    }

    /**
     * Retrive Mandrill WebHooks
     * @return array|void if there is no error it returns the array of templates in the server
     */
    public function getMandrillWebHooks()
    {
        if(!$this->mandrill) return $this->addError('Missing Mandrill API_KEY. use function setMandrillApiKey($pau_key)');
        try {
            $webhooks = $this->mandrill->webhooks->getList();
        } catch (Error $e) {
            return $this->addError($e->getMessage());
        }

        return $webhooks;

    }

    /**
     * Retrive Mandrill Domains
     * @return array|void if there is no error it returns the array of templates in the server
     * [{
     * [0] => domain
     * [1] => created_at
     * [2] => last_tested_at
     * [3] => spf
     * [4] => dkim
     * [5] => verified_at
     * [6] => valid_signing
     * [7] => verify_txt_key
     * }]
     */
    public function getMandrillDomains()
    {
        if(!$this->mandrill) return $this->addError('Missing Mandrill API_KEY. use function setMandrillApiKey($pau_key)');
        try {
            $domains = $this->mandrill->senders->domains();
        } catch (Error $e) {
            return $this->addError($e->getMessage());
        }

        return $domains;

    }

    /**
     * Retrive Mandrill Senders used in the email marketing
     * @return array|void if there is no error it returns the array of templates in the server
     * [{
     * [0] => sent
     * [1] => hard_bounces
     * [2] => soft_bounces
     * [3] => rejects
     * [4] => complaints
     * [5] => unsubs
     * [6] => opens
     * [7] => clicks
     * [8] => unique_opens
     * [9] => unique_clicks
     * [10] => reputation
     * [11] => address
     * }]
     */
    public function getMandrillSenders()
    {
        if(!$this->mandrill) return $this->addError('Missing Mandrill API_KEY. use function setMandrillApiKey($pau_key)');
        try {
            $senders = $this->mandrill->senders->getList();
        } catch (Error $e) {
            return $this->addError($e->getMessage());
        }

        return $senders;

    }



    /**
     * Retrive Mandrill message info
     * @return array|void if there is no error it returns the array of templates in the server
     * [{
     * [0] => ts
     *   [1] => _id
     *   [2] => state
     *   [3] => subject
     *   [4] => email
     *   [5] => tags
     *   [6] => opens
     *   [7] => clicks
     *   [8] => smtp_events
     *   [9] => resends
     *   [10] => sender
     *   [11] => template
     *   [12] => opens_detail
     *   [13] => clicks_detail
     * }]
     */
    public function getMandrillMessageInfo(string $id)
    {
        if(!$this->mandrill) return $this->addError('Missing Mandrill API_KEY. use function setMandrillApiKey($pau_key)');
        try {
            $info = $this->mandrill->messages->info($id);
        } catch (Error $e) {
            return $this->addError($e->getMessage());
        }
        return $info;
    }


    /**
     * Retrive Mandrill message content
     * @return array|void if there is no error it returns the array of templates in the server
     * [{
     *    [0] => subject
     *    [1] => from_email
     *    [2] => from_name
     *    [3] => tags
     *    [4] => to
     *    [5] => html
     *    [6] => headers
     *    [7] => attachments
     *    [8] => text
     *    [9] => ts
     *    [10] => _id
     * }]
     */
    public function getMandrillMessageContent(string $id)
    {
        if(!$this->mandrill) return $this->addError('Missing Mandrill API_KEY. use function setMandrillApiKey($pau_key)');
        try {
            $content = $this->mandrill->messages->content($id);
        } catch (Error $e) {
            return $this->addError($e->getMessage());
        }
        return $content;
    }

    /**
     * SET API for mandrill interation and SETUP $this->mandrill
     * @param string $slug
     * @param array $params
     * @throws Mandrill_Error
     */
    public function sendMandrillEmail(string $slug,array $params) {

        if(!$this->mandrill) return $this->addError('Missing Mandrill API_KEY. use function setMandrillApiKey($pau_key)');
        if(!$template = $this->getMandrillTemplate($slug)) return;

        if(!$from = $params['from']??($template['DefaultFromEmail']??null)) return $this->addError('sendMandrillEmail($slug,$params) missing email in $params because the template does not have a default from email');
        if(!$subject = $params['subject']??($template['DefaultSubject']??null)) return $this->addError('sendMandrillEmail($slug,$params) missing subject in $params because the template does not have a default subject');
        $from_name = $params['name']??($template['DefaultFromName']??'');
        if(!$email_tos = $params['to']) return $this->addError('sendMandrillEmail($slug,$params) missing to in $params');
        if(!is_array($email_tos)) $email_tos = explode(',',$email_tos);
        $email_data= $params['data']??[];
        if(!is_array($email_data)) $email_data=[];
        $tags = $params['tags']??[];
        if(!is_array($tags)) $tags=explode(',',$tags);


        try {
            $message = array(
                'subject' => $subject,
                'from_email' => $from,
                'from_name' => $from_name,
                // 'headers' => array('Reply-To' => 'Cloudframework@cloudframework.io'),
                'important' => false,
                'track_opens' => null,
                'track_clicks' => null,
                'auto_text' => null,
                'auto_html' => null,
                'inline_css' => null,
                'url_strip_qs' => null,
                'preserve_recipients' => null,
                'view_content_link' => null,
                // 'bcc_address' => "Cloudframework@cloudframework.io",
                'tracking_domain' => null,
                'signing_domain' => null,
                'return_path_domain' => null,
                'merge' => true,
                'tags' => $tags?:null,
                //'google_analytics_domains' => array($domain),
                //'google_analytics_campaign' => $domain,
                //'metadata' => array('website' => $domain)
            );

            //region to: into $message['to']

            $message['to'] = [];
            foreach ($email_tos as $email_to) {
                if(is_array($email_to)) {
                    if(!($email_to['email']??null)) return $this->addError('sendMandrillEmail($slug,$params) Wrong $params["to"] array. Missing email attribute');
                    $message['to'][] = ['email' => $email_to['email'], 'name' => $email_to['name'] ?? $email_to['email'], 'type' => 'to'];
                }else
                    $message['to'][] = ['email'=>$email_to,'name'=> $email_to,'type'=>'to'];
            }
            //endregion

             //region Add $email_data into the email template
            if(is_array($email_data)) foreach ($email_data as $key=>$value) {
                $template_content[] = ['name'=>$key,'content'=>$value];
                $message['global_merge_vars'][] = ['name'=>$key,'content'=>$value];
            }
            //endregion
            $async = false;
            $ip_pool = 'Main Pool';
//            $send_at = date("Y-m-d h:m:i");
//            $result = $this->mandrill->messages->sendTemplate($slug, $template_content, $message, $async, $ip_pool, $send_at);
            $result = $this->mandrill->messages->sendTemplate($slug, $template_content, $message, $async, $ip_pool);
            return ['success'=>true,'result'=>$result];

        } catch (Error $e) {
            return ['success'=>false,'result'=>$e->getMessage()];
            return $this->addError($e->getMessage());
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