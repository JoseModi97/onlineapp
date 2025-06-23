<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_application_intake".
 *
 * @property int $intake_code
 * @property string|null $intake_name
 * @property string|null $academic_year
 * @property string|null $degree_code
 * @property string|null $application_deadline
 * @property string|null $reporting_date
 * @property string|null $start_date
 * @property string|null $end_date
 */
class AppApplicationIntake extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_application_intake';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['intake_name', 'academic_year', 'degree_code', 'application_deadline', 'reporting_date', 'start_date', 'end_date'], 'default', 'value' => null],
            [['intake_code'], 'required'],
            [['intake_code'], 'default', 'value' => null],
            [['intake_code'], 'integer'],
            [['application_deadline', 'reporting_date', 'start_date', 'end_date'], 'safe'],
            [['intake_name'], 'string', 'max' => 100],
            [['academic_year'], 'string', 'max' => 20],
            [['degree_code'], 'string', 'max' => 10],
            [['intake_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'intake_code' => 'Intake Code',
            'intake_name' => 'Intake Name',
            'academic_year' => 'Academic Year',
            'degree_code' => 'Degree Code',
            'application_deadline' => 'Application Deadline',
            'reporting_date' => 'Reporting Date',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
        ];
    }

}
