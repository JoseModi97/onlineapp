<?php

namespace app\models;

use Yii;
use app\models\AppApplicant;
use app\models\AppApplicationFees;
use app\models\AppApplicantUser; // Needed for initValueText via $model->applicant->applicantUser

/**
 * This is the model class for table "onlineapp.app_application__08_04_2016_09_09".
 *
 * @property int $application_id
 * @property int|null $applicant_id
 * @property int|null $intake_code
 * @property string|null $study_center_code
 * @property string|null $application_ref_no
 * @property string|null $application_date
 * @property string|null $offer_accepted
 * @property string|null $final_status
 * @property int|null $application_fee_id
 * @property int|null $payment_status
 * @property string|null $processing_date
 * @property string|null $phd_proposal
 * @property string|null $application_form
 * @property int|null $sync_status
 */
class AppApplication080420160909 extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_application__08_04_2016_09_09';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['applicant_id', 'intake_code', 'study_center_code', 'application_ref_no', 'application_date', 'offer_accepted', 'final_status', 'application_fee_id', 'processing_date'], 'default', 'value' => null],
            [['sync_status'], 'default', 'value' => 0],
            [['applicant_id', 'intake_code', 'application_fee_id', 'payment_status', 'sync_status'], 'default', 'value' => null],
            [['applicant_id', 'intake_code', 'application_fee_id', 'payment_status', 'sync_status'], 'integer'],
            [['application_date', 'processing_date'], 'safe'],
            [['study_center_code', 'application_ref_no', 'final_status'], 'string', 'max' => 50],
            [['offer_accepted'], 'string', 'max' => 20],
            [['phd_proposal', 'application_form'], 'string', 'max' => 255],
            [['applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_id' => 'applicant_id']],
            [['application_fee_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicationFees::class, 'targetAttribute' => ['application_fee_id' => 'application_fee_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'application_id' => 'Application ID',
            'applicant_id' => 'Applicant ID',
            'intake_code' => 'Intake Code',
            'study_center_code' => 'Study Center Code',
            'application_ref_no' => 'Application Ref No',
            'application_date' => 'Application Date',
            'offer_accepted' => 'Offer Accepted',
            'final_status' => 'Final Status',
            'application_fee_id' => 'Application Fee ID',
            'payment_status' => 'Payment Status',
            'processing_date' => 'Processing Date',
            'phd_proposal' => 'Phd Proposal',
            'application_form' => 'Application Form',
            'sync_status' => 'Sync Status',
        ];
    }

    /**
     * Gets query for [[Applicant]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApplicant()
    {
        return $this->hasOne(AppApplicant::class, ['applicant_id' => 'applicant_id']);
    }

    /**
     * Gets query for [[ApplicationFee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApplicationFee()
    {
        return $this->hasOne(AppApplicationFees::class, ['application_fee_id' => 'application_fee_id']);
    }
}
