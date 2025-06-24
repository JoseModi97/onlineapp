<?php

namespace app\models;

use Yii;
use app\models\AppApplicant; // Ensure present
use app\models\AppApplicationFees; // Ensure present
use app\models\AppApplicationIntake; // Ensure present
// AppApplicantUser might be needed if getApplicant() needs to populate applicantUser for initValueText
use app\models\AppApplicantUser;


/**
 * This is the model class for table "onlineapp.app_application".
 *
 * @property int $application_id
 * @property int $applicant_id
 * @property int $intake_code
 * @property string $study_center_code
 * @property string $application_ref_no
 * @property string|null $application_date
 * @property string|null $offer_accepted
 * @property string|null $final_status
 * @property int|null $application_fee_id
 * @property int|null $payment_status
 * @property string|null $processing_date
 * @property string|null $phd_proposal
 * @property string|null $application_form
 * @property int|null $sync_status
 * @property int|null $app_publish
 * @property int|null $letter_downloaded
 */
class AppApplication extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_application';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['application_date', 'offer_accepted', 'final_status', 'application_fee_id', 'processing_date'], 'default', 'value' => null],
            [['letter_downloaded'], 'default', 'value' => 0],
            [['applicant_id', 'intake_code', 'study_center_code', 'application_ref_no'], 'required'],
            [['applicant_id', 'intake_code', 'application_fee_id', 'payment_status', 'sync_status', 'app_publish', 'letter_downloaded'], 'default', 'value' => null],
            [['applicant_id', 'intake_code', 'application_fee_id', 'payment_status', 'sync_status', 'app_publish', 'letter_downloaded'], 'integer'],
            [['application_date', 'processing_date'], 'safe'],
            [['study_center_code', 'application_ref_no', 'final_status'], 'string', 'max' => 50],
            [['offer_accepted'], 'string', 'max' => 20],
            [['phd_proposal', 'application_form'], 'string', 'max' => 255],
            [['applicant_id', 'intake_code'], 'unique', 'targetAttribute' => ['applicant_id', 'intake_code']],
            [['applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_id' => 'applicant_id']],
            [['application_fee_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicationFees::class, 'targetAttribute' => ['application_fee_id' => 'application_fee_id']],
            [['intake_code'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicationIntake::class, 'targetAttribute' => ['intake_code' => 'intake_code']],
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
            'app_publish' => 'App Publish',
            'letter_downloaded' => 'Letter Downloaded',
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

    /**
     * Gets query for [[ApplicationIntake]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApplicationIntake()
    {
        return $this->hasOne(AppApplicationIntake::class, ['intake_code' => 'intake_code']);
    }
}
