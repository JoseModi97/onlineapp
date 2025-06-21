<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use yii\helpers\Url;

// Registering assets directly from the auth_bundle
// It's generally better to create a dedicated AssetBundle for this,
// but for a direct replacement of a static HTML, this is a quicker approach.

$this->registerCssFile(Url::to('@web/auth_bundle/vendor/fontawesome-free/css/all.min.css'));
$this->registerCssFile('https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i');
$this->registerCssFile(Url::to('@web/auth_bundle/css/sb-admin-2.min.css'));

$this->registerJsFile(Url::to('@web/auth_bundle/vendor/jquery/jquery.min.js'), ['position' => \yii\web\View::POS_END]);
$this->registerJsFile(Url::to('@web/auth_bundle/vendor/bootstrap/js/bootstrap.bundle.min.js'), ['position' => \yii\web\View::POS_END]);
$this->registerJsFile(Url::to('@web/auth_bundle/vendor/jquery-easing/jquery.easing.min.js'), ['position' => \yii\web\View::POS_END]);
$this->registerJsFile(Url::to('@web/auth_bundle/js/sb-admin-2.min.js'), ['position' => \yii\web\View::POS_END]);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="bg-gradient-primary">
<?php $this->beginBody() ?>

    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <?= $content ?>
    </div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
