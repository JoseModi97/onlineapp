<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use yii\helpers\Url;

// We need to construct correct URLs for assets within auth_bundle
$authBundleBaseUrl = Url::to('@web/auth_bundle');

?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <!-- Custom fonts for this template-->
    <link href="<?= $authBundleBaseUrl ?>/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= $authBundleBaseUrl ?>/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom CSS for background image -->
    <style>
        .bg-register-image {
            background: url("<?= $authBundleBaseUrl ?>/img/register-image.jpg");
            background-position: center;
            background-size: cover;
        }
        /* Added similar styles for login and password pages, anticipating shared layout */
        .bg-login-image {
            background: url("<?= $authBundleBaseUrl ?>/img/login-image.jpg");
            background-position: center;
            background-size: cover;
        }
        .bg-password-image {
            background: url("<?= $authBundleBaseUrl ?>/img/password-image.jpg");
            background-position: center;
            background-size: cover;
        }
        .bg-signup-svg {
            background: url('<?= \yii\helpers\Url::to('@web/img/signup.svg') ?>');
            background-position: center;
            background-size: cover; /* Or 'contain' or specific dimensions if SVG needs different scaling */
        }
    </style>
</head>
<body class="bg-gradient-primary">
<?php $this->beginBody() ?>

    <?= $content ?>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= $authBundleBaseUrl ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?= $authBundleBaseUrl ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= $authBundleBaseUrl ?>/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= $authBundleBaseUrl ?>/js/sb-admin-2.min.js"></script>

<?php $this->endBody() ?>
</body>
</html>
