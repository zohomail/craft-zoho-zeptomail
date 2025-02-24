<?php

namespace zohomail\craftzohozeptomail\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;  // This includes the Craft CP assets, if needed

class ZeptoMailAssetBundle extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__; 
        $this->js = [
            'js/admin-page.js', 
        ];
        $this->css = [
            'css/admin-page.css', 
        ];
        $this->depends = [CpAsset::class]; 

        parent::init();
    }
}
