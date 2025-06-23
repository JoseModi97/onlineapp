<?php
/**
 * @var \yii\web\View $this (if in Yii context)
 * @var \app\components\FormWizard\WizardController $wizardController
 * @var string $currentStepKey
 * @var array $stepConfig
 * @var \yii\base\Model $model (or your specific model class)
 * @var array $formData
 * @var array $errors
 */

use yii\helpers\Html; // Assuming Yii context

// Example of how to use the passed variables:
?>
<div class="sample-step-container">
    <h4><?= Html::encode($stepConfig['title'] ?? 'Sample Step') ?></h4>
    <p>This is a sample step view for step: <strong><?= Html::encode($currentStepKey) ?></strong></p>

    <?php if ($model): ?>
        <?php
            // Example for Yii ActiveForm. If not using Yii, build form fields manually.
            // $form = \yii\widgets\ActiveForm::begin();
            // echo $form->field($model, 'some_attribute')->textInput();
            // echo $form->field($model, 'another_attribute')->textarea();
            // \yii\widgets\ActiveForm::end();
        ?>
        <p><em>Form fields for a model would go here. For example, using Yii ActiveForm or manual HTML inputs.</em></p>
        <div class="mb-3">
            <label for="sample_field_1" class="form-label">Sample Field 1 (from model `<?= get_class($model) ?>`)</label>
            <input type="text" class="form-control <?= !empty($errors['sample_field_1']) ? 'is-invalid' : '' ?>"
                   id="sample_field_1" name="<?= Html::getInputName($model, 'sample_field_1') ?>"
                   value="<?= Html::encode($model->hasAttribute('sample_field_1') ? $model->sample_field_1 : ($formData['sample_field_1'] ?? '')) ?>">
            <?php if (!empty($errors['sample_field_1'])): ?>
                <div class="invalid-feedback"><?= Html::encode(implode(', ', (array)$errors['sample_field_1'])) ?></div>
            <?php endif; ?>
        </div>
         <div class="mb-3">
            <label for="sample_field_2" class="form-label">Sample Field 2</label>
            <input type="text" class="form-control <?= !empty($errors['sample_field_2']) ? 'is-invalid' : '' ?>"
                   id="sample_field_2" name="<?= Html::getInputName($model, 'sample_field_2') ?>"
                   value="<?= Html::encode($model->hasAttribute('sample_field_2') ? $model->sample_field_2 : ($formData['sample_field_2'] ?? '')) ?>">
            <?php if (!empty($errors['sample_field_2'])): ?>
                <div class="invalid-feedback"><?= Html::encode(implode(', ', (array)$errors['sample_field_2'])) ?></div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <p><em>No model provided for this step. You can render custom HTML content here.</em></p>
        <div class="mb-3">
            <label for="custom_field_<?= Html::encode($currentStepKey) ?>" class="form-label">Custom Field for <?= Html::encode($currentStepKey) ?></label>
            <input type="text" class="form-control <?= !empty($errors['custom_field']) ? 'is-invalid' : '' ?>"
                   id="custom_field_<?= Html::encode($currentStepKey) ?>" name="custom_fields[<?= Html::encode($currentStepKey) ?>][custom_field]"
                   value="<?= Html::encode($formData['custom_fields'][$currentStepKey]['custom_field'] ?? '') ?>">
            <?php if (!empty($errors['custom_field'])): ?>
                <div class="invalid-feedback"><?= Html::encode(implode(', ', (array)$errors['custom_field'])) ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors) && empty($errors['sample_field_1']) && empty($errors['sample_field_2']) && empty($errors['custom_field']) ): // Display general errors ?>
        <div class="alert alert-danger">
            <p>There were errors with your submission:</p>
            <ul>
                <?php foreach ($errors as $field => $messages): ?>
                    <li><?= Html::encode($field) ?>: <?= Html::encode(implode(', ', (array)$messages)) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <p><small>Data passed to this view:</small></p>
    <pre style="font-size: 0.8em; background-color: #f8f9fa; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;">
Step Key: <?= Html::encode($currentStepKey) ?>
Step Config: <?= Html::encode(var_export($stepConfig, true)) ?>
Model Class: <?= $model ? Html::encode(get_class($model)) : 'N/A' ?>
Form Data: <?= Html::encode(var_export($formData, true)) ?>
Errors: <?= Html::encode(var_export($errors, true)) ?>
    </pre>
</div>
