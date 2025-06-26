<?php

namespace app\models;

use Yii;
use app\models\AppApplicant; // Added
use app\models\AppEducationSystem; // Added

/**
 * This is the model class for table "onlineapp.app_applicant_education".
 *
 * @property int $education_id
 * @property int $applicant_id
 * @property int $edu_system_code
 * @property string $institution_name
 * @property string $edu_ref_no
 * @property int|null $year_from
 * @property int|null $year_to
 * @property string $grade
 * @property string|null $grade_per_student
 * @property int|null $points_score
 * @property int|null $pi_gpa
 * @property string|null $relevant
 * @property string|null $remarks
 * @property string|null $name_as_per_cert
 * @property string|null $file_path
 * @property string|null $file_name
 * @property int $cert_source
 */
class AppApplicantEducation extends \yii\db\ActiveRecord
{
    public $education_certificate_file; // Virtual attribute for file upload
    const SCENARIO_WIZARD_EDUCATION_STEP = 'wizard_education';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_applicant_education';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            // General rules applicable to all or default scenarios
            [['year_from', 'year_to', 'grade_per_student', 'points_score', 'pi_gpa', 'relevant', 'remarks', 'name_as_per_cert', 'file_path', 'file_name'], 'default', 'value' => null],
            [['education_id', 'applicant_id', 'edu_system_code', 'year_from', 'year_to', 'points_score', 'pi_gpa', 'cert_source'], 'integer'],
            [['institution_name', 'grade', 'grade_per_student'], 'string', 'max' => 80],
            [['education_certificate_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf, doc, docx', 'maxSize' => 1024 * 1024 * 5, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_WIZARD_EDUCATION_STEP]], // Max 5MB, specify scenarios
            [['edu_ref_no'], 'string', 'max' => 50],
            [['relevant'], 'string', 'max' => 8],
            [['remarks', 'file_name'], 'string', 'max' => 255],
            [['name_as_per_cert', 'file_path'], 'string', 'max' => 150],
            [['education_id'], 'unique'], // PK unique

            // Rules for default scenario (e.g., direct CRUD operations, assuming all fields might be mass assigned)
            [['applicant_id', 'edu_system_code', 'institution_name', 'grade', 'cert_source'], 'required', 'on' => self::SCENARIO_DEFAULT],
            // The unique constraint below might be too broad for a wizard if user can enter multiple similar records before final save.
            // Consider if this unique rule should only be on SCENARIO_DEFAULT or if wizard logic handles it.
            // For now, keeping it general.
            [['applicant_id', 'edu_system_code', 'institution_name', 'year_from', 'year_to'], 'unique',
                'targetAttribute' => ['applicant_id', 'edu_system_code', 'institution_name', 'year_from', 'year_to'],
                'message' => 'A similar education record already exists for this applicant, system, institution, and period.',
                'on' => self::SCENARIO_DEFAULT // Apply this strict uniqueness only on default save, not during wizard step
            ],

            // Foreign key checks - should generally apply
            [['applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_id' => 'applicant_id']],
            [['edu_system_code'], 'exist', 'skipOnError' => true, 'targetClass' => AppEducationSystem::class, 'targetAttribute' => ['edu_system_code' => 'edu_system_code']],

            // Scenario-specific rules for Wizard Education Step
            [['edu_system_code', 'institution_name', 'year_from', 'year_to', 'name_as_per_cert', 'cert_source'], 'required', 'on' => self::SCENARIO_WIZARD_EDUCATION_STEP],
            [['year_from', 'year_to'], 'integer', 'min' => 1900, 'max' => date('Y') + 10, 'on' => self::SCENARIO_WIZARD_EDUCATION_STEP],
            // Compare validator for year_from and year_to
            ['year_to', 'compare', 'compareAttribute' => 'year_from', 'operator' => '>=', 'type' => 'number',
                'message' => '"Year To" must be greater than or equal to "Year From".',
                'on' => self::SCENARIO_WIZARD_EDUCATION_STEP,
                'when' => function ($model) {
                    return !empty($model->year_from) && !empty($model->year_to);
                }
            ],
             // 'grade' might not be universally required or could have different meanings.
             // Adding it as required for the wizard step for now.
            [['grade'], 'required', 'on' => self::SCENARIO_WIZARD_EDUCATION_STEP],
        ];

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'education_id' => 'Education ID',
            'applicant_id' => 'Applicant ID',
            'edu_system_code' => 'Edu System Code',
            'institution_name' => 'Institution Name',
            'edu_ref_no' => 'Edu Ref No',
            'year_from' => 'Year From',
            'year_to' => 'Year To',
            'grade' => 'Grade',
            'grade_per_student' => 'Grade Per Student',
            'points_score' => 'Points Score',
            'pi_gpa' => 'Pi Gpa',
            'relevant' => 'Relevant',
            'remarks' => 'Remarks',
            'name_as_per_cert' => 'Name As Per Cert',
            'file_path' => 'File Path',
            'file_name' => 'File Name',
            'cert_source' => 'Cert Source',
            'education_certificate_file' => 'Certificate Upload', // Label for the file input
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
     * Gets query for [[EduSystem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEduSystem()
    {
        return $this->hasOne(AppEducationSystem::class, ['edu_system_code' => 'edu_system_code']);
    }
}
