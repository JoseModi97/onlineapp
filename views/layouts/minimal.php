<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\Breadcrumbs;
use app\widgets\Alert;
use app\assets\AppAsset;

AppAsset::register($this);
Yii::$app->name = 'Online Application';

// Minimal layout, no need for route related variables or helper functions here.
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php $this->head() ?>

    <style>
        body {
            overflow-x: hidden; /* Prevent horizontal scroll */
            min-height: 100vh; /* Ensure full viewport height */
            background-color: #f0f2f5; /* Light grey background for the page */
        }

        #content-wrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex-grow: 1; /* Takes available vertical space, pushing footer down */
        }

        /* Minimal layout styles - main-content now acts as a simple wrapper */
        #main-content {
            width: 100%; /* Ensure it takes full width for the container inside */
            margin-left: 0 !important; /* Override any potential margin */
        }

        .login-form-container {
            background-color: #ffffff; /* White background for the form card */
            padding: 2rem; /* Inner spacing for the form card */
            border-radius: 0.5rem; /* Rounded corners for the card */
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); /* Subtle shadow for depth */
        }

        /* page-content-wrapper might not need specific padding if login-form-container handles it */
        .page-content-wrapper {
            /* padding-top: 1.5rem; /* Removed, handled by login-form-container's padding */
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100"> <!-- bg-light can be removed if body style handles background -->
    <?php $this->beginBody() ?>

    <div class="d-flex flex-grow-1"> <!-- Added flex-grow-1 to allow this div to expand -->

        <!-- Main Content and Footer Wrapper -->
        <div id="content-wrapper"> <!-- Classes managed by CSS above -->

            <!-- Main Content -->
            <div id="main-content"> <!-- bg-white removed, padding removed -->
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6 col-lg-5 login-form-container">
                            <div class="page-content-wrapper"> <!-- Existing inner wrapper -->
                                <?php if (!empty($this->params['breadcrumbs'])): ?>
                                    <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
                                <?php endif ?>

                                <?= Alert::widget() ?>
                                <?= $content ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- Closing main-content -->


        </div> <!-- Closing content-wrapper -->
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
