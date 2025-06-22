<?php

namespace app\models;

use Yii;
use app\models\AppApplicantUser; // Added for clarity and explicit dependency declaration

/**
 * This is the model class for table "onlineapp.app_applicant".
 *
 * @property int $applicant_id
 * @property int $applicant_user_id
 * @property string|null $gender
 * @property string|null $dob
 * @property string|null $religion
 * @property int|null $country_code
 * @property int|null $national_id
 * @property string|null $marital_status
 */
class AppApplicant extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_applicant';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['applicant_user_id'], 'required'],
            [['applicant_user_id', 'country_code', 'national_id'], 'integer'],
            [['dob'], 'date', 'format' => 'php:Y-m-d'], // Assuming Y-m-d format, adjust if needed
            [['gender', 'marital_status'], 'string', 'max' => 30],
            [['religion'], 'string', 'max' => 50],
            // Adding required rules for fields that are likely essential for an applicant profile
            [['gender', 'dob', 'marital_status', 'national_id', 'country_code'], 'required'],
            [['applicant_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicantUser::class, 'targetAttribute' => ['applicant_user_id' => 'applicant_user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'applicant_id' => 'Applicant ID',
            'applicant_user_id' => 'Applicant User ID',
            'gender' => 'Gender',
            'dob' => 'Dob',
            'religion' => 'Religion',
            'country_code' => 'Country Code',
            'national_id' => 'National ID',
            'marital_status' => 'Marital Status',
        ];
    }

    /**
     * Gets query for [[ApplicantUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApplicantUser()
    {
        return $this->hasOne(AppApplicantUser::class, ['applicant_user_id' => 'applicant_user_id']);
    }
}
