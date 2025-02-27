<?php


 namespace zohomail\zohozeptomail\mail;


use Craft;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use zohomail\zohozeptomail\Helper\ZeptoMailApi;

class ZeptoMailTransport extends AbstractApiTransport
{
    private string $authtoken;

    private string $region = '';

    /**
     * @param string $key
     * @param HttpClientInterface|null $client
     * @param EventDispatcherInterface|null $dispatcher
     * @param LoggerInterface|null $logger
     */
    public function __construct(HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        parent::__construct($client, $dispatcher, $logger);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('zepto+api://%s', $this->getEndpoint());
    }

    /**
     * @param SentMessage $sentMessage
     * @param Email $email
     * @param Envelope $envelope
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        $zeptoSettings = Craft::$app->getProjectConfig()->get("zeptomail.settings");
        if(!isset($zeptoSettings)) {
            throw new HttpTransportException('Configure ZeptoMail settings before proceed');
        }
        $domain = $zeptoSettings['domain'];
        $apiKey = $zeptoSettings['apiKey'];
        $fromEmail = $zeptoSettings['fromEmail'];
        $zeptoMailApi = new ZeptoMailApi($domain,$apiKey);
        $mail_data = $this->getPayload($email, $envelope,$fromEmail);
        
        $response = $zeptoMailApi->sendZeptoMail($mail_data);
        return $response;
        
    }

    /**
     * @return string|null
     */
    private function getEndpoint(): ?string
    {

        return "https://zeptomail.".$this->domainMapping[$this->region].'/v1.1/email';
    }

    /**
     * @param Email $email
     * @param Envelope $envelope
     * @return array
     */
    private function getPayload(Email $email, Envelope $envelope,$fromEmail): array
    {
        $recipients = $this->getRecipients($email, $envelope);
        $toaddress = $this->getEmailDetailsByType($recipients,'to');
        $ccaddress = $this->getEmailDetailsByType($recipients,'cc');
        $bccaddress = $this->getEmailDetailsByType($recipients,'bcc');
        $attachmentJSONArr = array();
        $fromEmailDetail = ['address' => $fromEmail];
        if ('' !== $envelope->getSender()->getName()) {
            $fromEmailDetail['name'] = $envelope->getSender()->getName();
        }
        $payload = [
            'htmlbody' => $email->getHtmlBody(),
            'subject' => $email->getSubject(),
            'from' => $fromEmailDetail
        ];
       
        
        if(isset($toaddress) && !empty($toaddress)) {
            $payload['to'] =$toaddress;
        }
        if(isset($ccaddress) && !empty($ccaddress)) {
            $payload['cc'] =$ccaddress;
        }
        if(isset($bccaddress) && !empty($bccaddress)) {
            $payload['bcc'] =$bccaddress;
        }

        

        foreach ($email->getAttachments() as $attachment) {
            
            $headers = $attachment->getPreparedHeaders();
            $disposition = $headers->getHeaderBody('Content-Disposition');
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');

            $att = [
                'content' => base64_encode($attachment->bodyToString()),
                'name' => $filename,
                'mime_type' => $headers->get('Content-Type')->getBody()
              ];

            if ($name = $headers->getHeaderParameter('Content-Disposition', 'name')) {
                $att['name'] = $name;
            }

            $attachmentJSONArr[] = $att;
        }
        if(isset($attachmentJSONArr)) {
            $payload['attachments'] = $attachmentJSONArr;
        }
        

        return $payload;
    }

    /**
     * @param Email $email
     * @param Envelope $envelope
     * @return array
     */
    protected function getRecipients(Email $email, Envelope $envelope): array
    {
        $recipients = [];

        foreach ($envelope->getRecipients() as $recipient) {
            $type = 'to';

            if (\in_array($recipient, $email->getBcc(), true)) {
                $type = 'bcc';
            } elseif (\in_array($recipient, $email->getCc(), true)) {
                $type = 'cc';
            }

            $recipientPayload = [
                'email' => $recipient->getAddress(),
                'type' => $type,
            ];

            if ('' !== $recipient->getName()) {
                $recipientPayload['name'] = $recipient->getName();
            }

            $recipients[] = $recipientPayload;
        }

        return $recipients;
    }
    protected function getEmailDetailsByType(array $recipients,string $type): array
    {
        $sendmailaddress = [];
        foreach ($recipients as $recipient) {
            if($type === $recipient['type']){
                $emailDetail = [
                    'address' => $recipient['email']
                    ];
                if(isset($recipient['name'])) {
                    $emailDetail['name'] = $recipient['name'];
                }
                $emailDetails = ['email_address' =>$emailDetail];
                $sendmailaddress[] = $emailDetails;
            }
           
        }
        return $sendmailaddress;
    }


    
    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public $domainMapping = [
		"zoho.com"          => "zoho.com",
		"zoho.eu"           => "zoho.eu", 
		"zoho.in"           => "zoho.in", 
		"zoho.com.cn"       => "zoho.com.cn",
		"zoho.com.au"       => "zoho.com.au",
		"zoho.jp"           => "zoho.jp",
		"zohocloud.ca"      => "zohocloud.ca",
		"zoho.sa"           => "zoho.sa"
    ];

   
}

