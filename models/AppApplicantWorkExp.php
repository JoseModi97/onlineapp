<?php

namespace app\models;

use Yii;
use app\models\AppApplicant; // Added

/**
 * This is the model class for table "onlineapp.app_applicant_work_exp".
 *
 * @property int $experience_id
 * @property int|null $applicant_id
 * @property string|null $employer_name
 * @property string|null $designation
 * @property string|null $year_from
 * @property string|null $year_to
 * @property string|null $assignment
 * @property string|null $relevant
 */
class AppApplicantWorkExp extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_applicant_work_exp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['applicant_id', 'employer_name', 'designation', 'year_from', 'year_to', 'assignment', 'relevant'], 'default', 'value' => null],
            [['experience_id'], 'required'],
            [['experience_id', 'applicant_id'], 'default', 'value' => null],
            [['experience_id', 'applicant_id'], 'integer'],
            [['year_from', 'year_to'], 'safe'],
            [['employer_name', 'designation'], 'string', 'max' => 100],
            [['assignment'], 'string', 'max' => 255],
            [['relevant'], 'string', 'max' => 50],
            [['experience_id'], 'unique'],
            [['applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_id' => 'applicant_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'experience_id' => 'Experience ID',
            'applicant_id' => 'Applicant ID',
            'employer_name' => 'Employer Name',
            'designation' => 'Designation',
            'year_from' => 'Year From',
            'year_to' => 'Year To',
            'assignment' => 'Assignment',
            'relevant' => 'Relevant',
        ];
    }

    /**
     * Gets query for [[Applicant]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApplicant()
    {
        // Assuming AppApplicant model has 'applicantUser' relation to get user details
        return $this->hasOne(AppApplicant::class, ['applicant_id' => 'applicant_id']);
    }
}
