<?php

namespace zohomail\zohozeptomail\controllers;

use Craft;
use craft\web\Controller;
use  zohomail\zohozeptomail\Helper\ZeptoMailApi;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use zohomail\zohozeptomail\assets\ZeptoMailAssetBundle;

class ZeptoMailController extends Controller
{
    
    public function actionSaveMailtoken()
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $domain = Craft::$app->getRequest()->getBodyParam('domain');
        $fromEmail = Craft::$app->getRequest()->getBodyParam('from_address');
        $fromName = Craft::$app->getRequest()->getBodyParam('from_name');
        $mailtoken = Craft::$app->getRequest()->getBodyParam('mailtoken');



        $zeptoMailApi = new ZeptoMailApi($domain,$mailtoken);
        $json = $this->getTestPayload($fromEmail,$fromName);
        
        try {
            $zeptoMailApi->sendZeptoMail($json);
            $this->saveZeptoMailSettings($domain,$fromName,$fromEmail,base64_encode($mailtoken));
            return $this->asJson(['result' => 'success', 'message' => 'Action completed']);
        }
        catch(HttpTransportException $httpTransportException){
            Craft::error('HttpTransportException occurred while sending email: ' . $httpTransportException->getMessage(), __METHOD__);
            return $this->asJson(['result' => 'failure', 'message' => $httpTransportException->getMessage(),'json' => $json]);
        }
       
    }
    
    public function actionTestMail()
    {
        $this->requirePostRequest();
        $this->requireAdmin();
        $zeptoSettings = Craft::$app->getProjectConfig()->get("zeptomail.settings");

        if(!isset($zeptoSettings)) {
            return $this->asJson(['result' => 'failure', 'message' => 'Please configure mail settings']);
        }
        
        $fromEmail =  $zeptoSettings['fromEmail'];
        $fromName =  $zeptoSettings['fromName'];
        $mailtoken = base64_decode($zeptoSettings['apiKey']);
        $domain = $zeptoSettings['domain'];



        $zeptoMailApi = new ZeptoMailApi($domain,$mailtoken);
        $json = $this->getTestPayload($fromEmail,$fromName);
        
        try {
            $zeptoMailApi->sendZeptoMail($json);

            return $this->asJson(['result' => 'success', 'message' => 'Action completed']);
        }
        catch(HttpTransportException $httpTransportException){
            Craft::error('HttpTransportException occurred while sending email: ' . $httpTransportException->getMessage(), __METHOD__);
            return $this->asJson(['result' => 'failure', 'message' => $httpTransportException->getMessage(),'json' => $json]);
        }
       
    }
    public function actionIndex()
    {
        $this->requireAdmin();
        $zeptoSettings = Craft::$app->getProjectConfig()->get("zeptomail.settings");
        $data = array();
        if(!isset($zeptoSettings)) {
            $data['fromEmail'] = '';
            $data['fromName'] = '';
            $data['apiKey'] = '';
            $data['domain'] = 'zoho.com';

        } 
        else {
            $data['fromEmail'] = $zeptoSettings['fromEmail'];
            $data['fromName'] = $zeptoSettings['fromName'];
            $data['apiKey'] = $zeptoSettings['apiKey'];
            $data['domain'] = base64_decode($zeptoSettings['domain']);
        }
        Craft::$app->view->registerAssetBundle(ZeptoMailAssetBundle::class);

        $this->renderTemplate('zoho-zepto-mail/index',$data);
    }

    private function getTestPayload($fromAddress,$fromName) {
        $fromEmailDetail = ['address' => $fromAddress];
        $emailDetail = [
            'address' => $fromAddress
            ];
        if ('' !== $fromName) {
            $fromEmailDetail['name'] = $fromName;
        }
        $emailDetails = ['email_address' =>$emailDetail];
                $sendmailaddress[] = $emailDetails;
        $payload = [
            'subject' => 'ZeptoMail plugin for Craft CMS - Test Email',
            'htmlbody'  => '<html><body><p>Hello,</p><br><br><p>We\'re glad you\'re using our ZeptoMail plugin. This is a test email to verify your configuration details. 
    Thank you for choosing ZeptoMail for your transactional email needs.<p><br><br>Team ZeptoMail</body></html>',
            'from'     => $fromEmailDetail,
            'to'       => array($emailDetails)
        ];
        return $payload;
    }
    private function saveZeptoMailSettings($domain,$fromName,$fromEmail,$apiKey) {
        Craft::$app->getProjectConfig()->set("zeptomail.settings", [
            'domain' => $domain,
            'fromName' => $fromName,
            'fromEmail' => $fromEmail,
            'apiKey' => $apiKey
        ]);
    }
}

