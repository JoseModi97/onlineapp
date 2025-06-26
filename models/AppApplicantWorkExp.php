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
    const SCENARIO_WIZARD = 'wizard';

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
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_WIZARD] = ['employer_name', 'designation', 'year_from', 'year_to', 'assignment', 'relevant', 'applicant_id'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['applicant_id', 'employer_name', 'designation', 'year_from', 'year_to', 'assignment', 'relevant'], 'default', 'value' => null],
            [['applicant_id'], 'integer'],
            [['experience_id'], 'integer'], // experience_id is PK, auto-increment, not required on input

            [['employer_name', 'designation', 'year_from'], 'required', 'on' => self::SCENARIO_WIZARD],

            [['year_from', 'year_to'], 'date', 'format' => 'php:Y-m-d', 'on' => self::SCENARIO_WIZARD], // Assuming Y-m-d format, adjust if necessary
            ['year_to', 'compare', 'compareAttribute' => 'year_from', 'operator' => '>=', 'skipOnEmpty' => true, 'message' => '"Year To" must be greater than or equal to "Year From".', 'on' => self::SCENARIO_WIZARD],

            [['employer_name', 'designation'], 'string', 'max' => 100, 'on' => self::SCENARIO_WIZARD],
            [['assignment'], 'string', 'max' => 255, 'on' => self::SCENARIO_WIZARD],
            [['relevant'], 'string', 'max' => 50, 'on' => self::SCENARIO_WIZARD], // Consider boolean or enum if 'relevant' has fixed values

            // General rules (apply to all scenarios unless 'on' is specified)
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
            'designation' => 'Job Title/Designation',
            'year_from' => 'Start Date (YYYY-MM-DD)',
            'year_to' => 'End Date (YYYY-MM-DD)',
            'assignment' => 'Key Responsibilities/Assignments',
            'relevant' => 'Is this experience relevant?',
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
}
