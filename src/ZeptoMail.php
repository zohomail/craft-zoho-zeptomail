<?php

namespace zohomail\zohozeptomail;

use Craft;
use craft\base\Plugin;
use craft\base\Model;
use craft\services\Plugins;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\MailerHelper;
use zohomail\zohozeptomail\mail\ZeptoMailAdapter;
use yii\base\Event;
use craft\services\Email;
use craft\mail\MailTransportType;
use craft\events\ModelEvent;
use craft\web\UrlManager;
use craft\helpers\UrlHelper;
use craft\events\RegisterUrlRulesEvent;
use zohomail\zohozeptomail\controllers\ZeptoMailController;
use zohomail\zohozeptomail\models\Settings;
use zohomail\zohozeptomail\assets\ZeptoMailAssetBundle;
use craft\web\Controller;
use yii\web\Response;
/**
 * Zoho ZeptoMail plugin
 *
 * @method static ZeptoMail getInstance()
 * @method Settings getSettings()
 * @author Zoho Mail <support@zeptomail.com>
 * @copyright Zoho Mail
 * @license MIT
 */
class ZeptoMail extends Plugin
{
    public string $schemaVersion = '1.0.0';
    /**
     * @inheritdoc
     */
    public bool $hasCpSettings = true;

    public const CMS_ZEPTO_HANDLER = 'zoho-zepto-mail';
    /**
     * @inheritdoc
     */
    public bool $hasCpSection = true;
    public static ZeptoMail $plugin;

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        \Yii::setAlias('@zohomail', __DIR__);
        

        $request = Craft::$app->getRequest();
        $this->_registerCpRoutes();
        $eventType =defined(sprintf('%s::EVENT_REGISTER_MAILER_TRANSPORT_TYPES', MailerHelper::class))
        ? MailerHelper::EVENT_REGISTER_MAILER_TRANSPORT_TYPES // Craft 4
        : MailerHelper::EVENT_REGISTER_MAILER_TRANSPORTS; // Craft 5+

        Event::on(MailerHelper::class, $eventType,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ZeptoMailAdapter::class;  
            }
        );
      
      
    }   

       /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $ret = parent::getCpNavItem();

        $ret['label'] = Craft::t(self::CMS_ZEPTO_HANDLER, 'Zoho ZeptoMail');
        $ret['url'] = 'zeptomail';
        return $ret;

    }

    private function _registerCpRoutes(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['zeptomail'] = self::CMS_ZEPTO_HANDLER.'/zepto-mail/index';
            $event->rules['POST zeptomail/saveauthtoken'] = self::CMS_ZEPTO_HANDLER.'/zepto-mail/save-mailtoken';
            $event->rules['POST zeptomail/testmail'] = self::CMS_ZEPTO_HANDLER.'/zepto-mail/test-mail';
        });
    }
    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('zeptomail'));
    }

    /**
     * @inheritdoc
     */
    public function uninstall():void {
        
        Craft::$app->getProjectConfig()->remove("zeptomail.settings");
    }

}
