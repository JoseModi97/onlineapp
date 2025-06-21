<?php

use yii\bootstrap5\Html;
// Remove Breadcrumbs and Alert widgets if not used by the new theme's direct content area
// use yii\bootstrap5\Breadcrumbs;
// use app\widgets\Alert;
use app\assets\AppAsset; // Keep this if it registers global assets like jQuery, Bootstrap JS still needed by Yii.

// AppAsset::register($this); // We will manage assets more directly or via a new theme-specific asset bundle.
Yii::$app->name = 'Online Application';

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

    <title><?= Html::encode($this->title) ?></title>

    <!-- Custom fonts for this template-->
    <link href="<?= Yii::getAlias('@web/auth_bundle/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= Yii::getAlias('@web/auth_bundle/css/sb-admin-2.min.css') ?>" rel="stylesheet">

    <?php $this->head() ?>
    <style>
        /* Particle.js styles - keep if desired, or remove if theme's background is preferred */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0; /* Behind card */
            /* background-color: #232741; /* Original particle background */
        }
        canvas {
            display: block;
            vertical-align: bottom;
        }
        /* Ensure body takes full height and new theme's background is applied */
        body {
             min-height: 100vh;
             /* The class bg-gradient-primary from sb-admin-2 will handle the background */
        }
        /* Adjust container to be centered like in the SB Admin 2 theme */
        .container {
            display: flex;
            flex-direction: column;
            justify-content: center; /* Center vertically */
            align-items: center; /* Center horizontally */
            min-height: 100vh; /* Full viewport height */
            position: relative; /* For z-index stacking with particles */
            z-index: 1;
        }
    </style>
</head>

<body class="bg-gradient-primary"> <!-- Added class from SB Admin 2 theme -->
    <?php $this->beginBody() ?>

    <div id="particles-js"></div> <!-- Keep particles if desired -->

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center" style="width: 100%;"> <!-- Ensure row takes width for centering its content -->

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block" style="background: url('<?= Yii::getAlias('@web/auth_bundle/img/login-image.jpg') ?>'); background-position: center; background-size: cover;"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                     <!-- Yii's Alert and Content will go here -->
                                    <?php
                                    // It's common to show alerts within the form area
                                    // Not using \app\widgets\Alert directly here, but Yii's flash messages
                                    // would typically be handled by the view ($content) or a layout widget.
                                    // For now, let's assume $content (views/site/login.php) will handle its own alerts/structure.
                                    // Or, if Alert widget is generic enough:
                                    echo \app\widgets\Alert::widget();
                                    ?>
                                    <?= $content ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript - SB Admin uses an older Bootstrap version. Yii's AppAsset might register Bootstrap 5. -->
    <!-- It's usually better to stick to one Bootstrap version. -->
    <!-- For now, let's use SB Admin's included jQuery and Bootstrap bundle, and comment out Yii's default Bootstrap JS if it causes conflict. -->
    <script src="<?= Yii::getAlias('@web/auth_bundle/vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= Yii::getAlias('@web/auth_bundle/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= Yii::getAlias('@web/auth_bundle/vendor/jquery-easing/jquery.easing.min.js') ?>"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= Yii::getAlias('@web/auth_bundle/js/sb-admin-2.min.js') ?>"></script>

    <!-- Particle.js scripts - keep if desired -->
    <script src="<?= Yii::getAlias('@web/js/particles.js') ?>"></script>
    <script>
        // Initialize particles.js if it's kept
        // The app.js might contain the particlesJS initialization, or it might need to be explicit here.
        // Assuming app.js or particles.js itself handles initialization.
        // If not, add: particlesJS.load('particles-js', 'path/to/your/particles.json', function() { console.log('particles.js loaded - callback'); });
        // For now, check if existing app.js initializes it.
        // The original minimal.php had: <script src="<?= Yii::getAlias('@web/js/app.js') ? >"></script>
        // It's likely app.js contains the particlesJS init.
         if (typeof particlesJS !== 'undefined') {
            // Assuming a default particles.json might be in 'js/particles.json' or similar standard location for the project
            // If not, this path needs to be correct or the initialization should be in app.js
            // For now, this is a placeholder for where initialization would go if not in app.js
            // particlesJS.load('particles-js', '<?= Yii::getAlias('@web/js/particles.json') ?>', function() {
            //   console.log('particles.js loaded - callback via minimal.php');
            // });
            // Let's check if app.js is still needed or if its particle init can be moved here.
            // For now, we rely on the existing include of app.js if it was there before, or particle.js self-init.
            // The original minimal.php included app.js after particles.js, so let's re-add it if it's for particle init.
        }
    </script>
    <script src="<?= Yii::getAlias('@web/js/app.js') ?>"></script>


    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
