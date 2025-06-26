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
        return [
            [['year_from', 'year_to', 'grade_per_student', 'points_score', 'pi_gpa', 'relevant', 'remarks', 'name_as_per_cert', 'file_path', 'file_name'], 'default', 'value' => null],
            // 'education_id' is likely an auto-incrementing PK, should not be required for new records.
            // It is required in DB schema but Yii handles it for new records if auto PK.
            // If it's not auto PK, then it must be provided. Assuming it's auto PK for now.
            [['applicant_id', 'edu_system_code', 'institution_name', 'edu_ref_no', 'grade', 'cert_source'], 'required'],
            [['education_id', 'applicant_id', 'edu_system_code', 'year_from', 'year_to', 'points_score', 'pi_gpa', 'cert_source'], 'default', 'value' => null],
            [['education_id', 'applicant_id', 'edu_system_code', 'year_from', 'year_to', 'points_score', 'pi_gpa', 'cert_source'], 'integer'],
            [['institution_name', 'grade', 'grade_per_student'], 'string', 'max' => 80],
            [['education_certificate_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf, doc, docx', 'maxSize' => 1024 * 1024 * 5], // Max 5MB
            [['edu_ref_no'], 'string', 'max' => 50],
            [['relevant'], 'string', 'max' => 8],
            [['remarks', 'file_name'], 'string', 'max' => 255],
            [['name_as_per_cert', 'file_path'], 'string', 'max' => 150],
            [['applicant_id', 'edu_system_code', 'year_to'], 'unique', 'targetAttribute' => ['applicant_id', 'edu_system_code', 'year_to']],
            [['education_id'], 'unique'],
            [['applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_id' => 'applicant_id']],
            [['edu_system_code'], 'exist', 'skipOnError' => true, 'targetClass' => AppEducationSystem::class, 'targetAttribute' => ['edu_system_code' => 'edu_system_code']],
        ];
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
