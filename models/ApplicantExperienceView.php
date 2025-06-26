<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.applicant_experience_view".
 *
 * @property int $experience_id
 * @property string $application_ref_no
 * @property string|null $employer_name
 * @property string|null $designation
 * @property string|null $year_from
 * @property string|null $year_to
 * @property string|null $assignment
 */
class ApplicantExperienceView extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.applicant_experience_view';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['employer_name', 'designation', 'year_from', 'year_to', 'assignment'], 'default', 'value' => null],
            [['experience_id', 'application_ref_no'], 'required'],
            [['experience_id'], 'default', 'value' => null],
            [['experience_id'], 'integer'],
            [['year_from', 'year_to'], 'safe'],
            [['application_ref_no'], 'string', 'max' => 50],
            [['employer_name', 'designation'], 'string', 'max' => 100],
            [['assignment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'experience_id' => 'Experience ID',
            'application_ref_no' => 'Application Ref No',
            'employer_name' => 'Employer Name',
            'designation' => 'Designation',
            'year_from' => 'Year From',
            'year_to' => 'Year To',
            'assignment' => 'Assignment',
        ];
    }

}
