<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_referee_comments".
 *
 * @property int $comment_id
 * @property int $applicant_id
 * @property int $application_id
 * @property int $contact_id
 * @property string $duration_known
 * @property string $capacity
 * @property int $applicant_expression_ability
 * @property int $applicant_research_creativity
 * @property string $applicant_talent
 * @property string $applicant_graduate_ability
 * @property string $comment_date
 */
class AppRefereeComments extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_referee_comments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['applicant_id', 'application_id', 'contact_id', 'duration_known', 'capacity', 'applicant_expression_ability', 'applicant_research_creativity', 'applicant_talent', 'applicant_graduate_ability', 'comment_date'], 'required'],
            [['applicant_id', 'application_id', 'contact_id', 'applicant_expression_ability', 'applicant_research_creativity'], 'default', 'value' => null],
            [['applicant_id', 'application_id', 'contact_id', 'applicant_expression_ability', 'applicant_research_creativity'], 'integer'],
            [['applicant_talent', 'applicant_graduate_ability'], 'string'],
            [['comment_date'], 'safe'],
            [['duration_known'], 'string', 'max' => 100],
            [['capacity'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'comment_id' => 'Comment ID',
            'applicant_id' => 'Applicant ID',
            'application_id' => 'Application ID',
            'contact_id' => 'Contact ID',
            'duration_known' => 'Duration Known',
            'capacity' => 'Capacity',
            'applicant_expression_ability' => 'Applicant Expression Ability',
            'applicant_research_creativity' => 'Applicant Research Creativity',
            'applicant_talent' => 'Applicant Talent',
            'applicant_graduate_ability' => 'Applicant Graduate Ability',
            'comment_date' => 'Comment Date',
        ];
    }

}
