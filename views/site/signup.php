<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\SignupForm $model */

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm; // Using bootstrap5 ActiveForm as per original, may need to adjust if auth_bundle is strictly bootstrap 4
use yii\helpers\Url;

$this->title = 'Create an Account!'; // Updated title
// $this->params['breadcrumbs'][] = $this->title; // Breadcrumbs might not be needed for this auth page style

// It's expected that the layout file will include necessary CSS and JS for auth_bundle
?>
<div class="container">

    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
                <div class="col-lg-5 d-none d-lg-block bg-signup-svg"></div>
                <div class="col-lg-7">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4"><?= Html::encode($this->title) ?></h1>
                        </div>
                        <?php $form = ActiveForm::begin([
                            'id' => 'form-signup',
                            'options' => ['class' => 'user'],
                            'fieldConfig' => [
                                'template' => "{input}\n{hint}\n{error}", // Adjust template for form-control-user style
                                'inputOptions' => ['class' => 'form-control form-control-user'],
                                'errorOptions' => ['class' => 'invalid-feedback'], // Ensure errors are displayed if needed
                            ],
                        ]); ?>

                            <div class="form-group">
                                <?= $form->field($model, 'username', [
                                    'inputOptions' => [
                                        'class' => 'form-control form-control-user',
                                        'placeholder' => 'Username', // Changed from First Name
                                        'autofocus' => true
                                    ]
                                ])->label(false) ?>
                            </div>

                            <div class="form-group">
                                <?= $form->field($model, 'email', [
                                    'inputOptions' => [
                                        'class' => 'form-control form-control-user',
                                        'placeholder' => 'Email Address'
                                    ]
                                ])->input('email')->label(false) ?>
                            </div>

                            <div class="form-group">
                                <?= $form->field($model, 'password', [
                                     'inputOptions' => [
                                        'class' => 'form-control form-control-user',
                                        'placeholder' => 'Password'
                                    ]
                                ])->passwordInput()->label(false) ?>
                            </div>

                            <?= Html::submitButton('Register Account', ['class' => 'btn btn-primary btn-user btn-block', 'name' => 'signup-button']) ?>

                            <!-- Social login buttons - keeping structure, Yii integration for these is out of scope for now -->
                            <hr>
                            <a href="#" class="btn btn-google btn-user btn-block">
                                <i class="fab fa-google fa-fw"></i> Register with Google
                            </a>
                            <a href="#" class="btn btn-facebook btn-user btn-block">
                                <i class="fab fa-facebook-f fa-fw"></i> Register with Facebook
                            </a>
                        <?php ActiveForm::end(); ?>
                        <hr>
                        <div class="text-center">
                            <a class="small" href="<?= Url::to(['site/request-password-reset']) ?>">Forgot Password?</a>
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
