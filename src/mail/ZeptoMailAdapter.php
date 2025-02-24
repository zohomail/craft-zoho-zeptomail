<?php


namespace zohomail\craftzohozeptomail\mail;

use AsyncAws\Ses\SesClient;
use Craft;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;
use craft\mail\transportadapters\BaseTransportAdapter;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use zohomail\craftzohozeptomail\Helper\ZeptoMailApi;
use Symfony\Component\Mailer\Exception\HttpTransportException;

/**
 * @property-read null|string $settingsHtml
 */
class ZeptoMailAdapter extends BaseTransportAdapter
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }
    
    public const REGIONS = [
       'zoho.com',
       'zoho.eu',
       'zoho.com.cn',
       'zoho.in',
       'zoho.com.au',
       'zoho.jp',
       'zoho.sa',
       'zohocloud.ca'
    ];

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Zoho ZeptoMail';
    }




    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        
        return Craft::$app->getView()->renderTemplate('zoho-zepto-mail/_settings', [
            'adapter' => $this,
            'regions' => self::REGIONS,
            
        ]);
    }
   /**
     * @inheritdoc
     */
    public function defineTransport(): array|AbstractTransport
    {
        
        $transport = new ZeptoMailTransport();
        return $transport;
    }


     /**
     * @inheritdoc
     */
    public function validate($attributeNames = null, $clearErrors = true) {
         $zeptoSettings = Craft::$app->getProjectConfig()->get("zeptomail.settings");
        if(!isset($zeptoSettings)){
            Craft::error('Please configure zeptomail settings before proceed ', __METHOD__);
            return false;
        }
        return true;
        
    }
    
}
