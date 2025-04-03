<?php
namespace zohomail\zohozeptomail\helper;

use Craft;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Exception\HttpTransportException;

class ZeptoMailApi {
	private $domain;
	private $authtoken;
	
	private function getZeptoUrl() {
		return "https://zeptomail.".$this->domainMapping[$this->domain];
	}
	public function __construct($domain,$authtoken) {
		$this->domain = $domain;
		$this->authtoken = $authtoken;
	}
	public function sendZeptoMail($mail_data) {
		$client = HttpClient::create();
		$response = $client->request('POST', $this->getZeptoUrl().'/v1.1/email', [
            'json' => $mail_data,
            'headers' => [
                'Authorization' => $this->authtoken,
                'Accept' => 'application/json',
                'user-agent' => 'Craft CMS'
            ]
        ]);

        try {
            $statusCode = $response->getStatusCode();
            $result = $response->toArray(false);
        } catch (DecodingExceptionInterface $e) {
            throw new HttpTransportException('Unable to send an email: ' . $response->getContent(false) . sprintf(' (code %d).', $statusCode), $response);
        } catch (TransportExceptionInterface $e) {
            throw new HttpTransportException('Could not reach the ZeptoMail server.', $response, 0, $e);
        }

		if (200 !== $statusCode && 201 !== $statusCode) {
            if (isset($result['error'])) {
				Craft::$app->getSession()->setError('Email transport not configured properly.'.$result['error']['details'][0]['message']);
                throw new HttpTransportException('Unable to send an email: ' .  $result['error']['details'][0]['message'] , $response);
            }
			throw new HttpTransportException(sprintf('Unable to send an email (code %d).', $result['code']), $response);
        }
		return $response;
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
	