<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\PasswordResetRequestForm $model */

use yii\helpers\Html; // Changed from bootstrap5 Html to helpers Html
use yii\bootstrap5\ActiveForm; // Keeping bootstrap5 ActiveForm, potential JS conflict as noted for signup
use yii\helpers\Url;

$this->title = 'Forgot Your Password?';
// Breadcrumbs not typically used in this kind of auth page.
// $this->params['breadcrumbs'][] = $this->title;

// The layout file (views/layouts/auth.php) is expected to include necessary assets.
?>
<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

        <div class="col-xl-10 col-lg-12 col-md-9">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-forgot-password-svg"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-2"><?= Html::encode($this->title) ?></h1>
                                    <p class="mb-4">We get it, stuff happens. Just enter your email address below
                                        and we'll send you a link to reset your password!</p>
                                </div>
                                <?php $form = ActiveForm::begin([
                                    'id' => 'request-password-reset-form',
                                    'options' => ['class' => 'user'],
                                    'fieldConfig' => [
                                        'template' => "{input}\n{hint}\n{error}", // To match form-control-user style
                                        'inputOptions' => ['class' => 'form-control form-control-user'],
                                    ],
                                ]); ?>

                                    <?= $form->field($model, 'email', [
                                        'inputOptions' => [
                                            'class' => 'form-control form-control-user',
                                            'placeholder' => 'Enter Email Address...',
                                            'autofocus' => true,
                                            'aria-describedby' => 'emailHelp' // As in original template
                                        ]
                                    ])->label(false) ?>

                                    <?= Html::submitButton('Reset Password', ['class' => 'btn btn-primary btn-user btn-block']) ?>

                                <?php ActiveForm::end(); ?>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="<?= Url::to(['site/signup']) ?>">Create an Account!</a>
                                </div>
                                <div class="text-center">
                                    <a class="small" href="<?= Url::to(['site/login']) ?>">Already have an account? Login!</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
