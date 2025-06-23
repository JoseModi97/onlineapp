<?php

namespace app\components\FormWizard\assets;

use yii\web\AssetBundle;

class FormWizardAsset extends AssetBundle
{
    public $sourcePath = '@app/components/FormWizard/assets'; // Defines the base directory for the assets

    public $css = [
        // 'css/generic-wizard.css', // Example: if we add a CSS file later
    ];

    public $js = [
        'js/generic-wizard-ajax.js',
    ];

    public $depends = [
        \yii\web\JqueryAsset::class,
        \yii\bootstrap5\BootstrapPluginAsset::class, // For Bootstrap JS components like modals, tabs (if used by JS)
    ];
}
