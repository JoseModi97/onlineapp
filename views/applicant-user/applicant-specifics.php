<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
// This step is now the final step and its original fields have been moved to "Personal Details".
// It can serve as a confirmation or summary view if needed in the future.
// For now, it will be a simple placeholder.

$this->title = 'Applicant Specifics - Final Step'; // Example title
?>

<div class="applicant-specifics-final-step">

    <h4>Final Review</h4>

    <p>
        All required information has been collected. Please click "Save" to complete the applicant registration/update process.
    </p>
    <p>
        You can use the "Previous" button to go back and review or modify any information provided in the earlier steps.
    </p>

    <?php
    // If this step were to display a summary, you would fetch data from session or models here.
    // For example:
    // $session = Yii::$app->session;
    // $personalDetailsData = $session->get('applicant_wizard_data_step_personal-details', []);
    // $workExpData = $session->get('applicant_wizard_data_step_applicant-work-exp', []);
    // ... then render this data.
    ?>

    <?php // Navigation buttons (Previous, Save) are handled by the main update-wizard.php view and its JavaScript. ?>

</div>