<?php

namespace app\models;

use Yii;
use app\models\AppAdmissionStatus; // Added

/**
 * This is the model class for table "onlineapp.app_application_tracking".
 *
 * @property int $tracking_id
 * @property int|null $application_id
 * @property int|null $status_id
 * @property string|null $remarks
 * @property string|null $audit_date
 * @property string|null $user_id
 */
class AppApplicationTracking extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_application_tracking';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['application_id', 'status_id', 'audit_date', 'user_id'], 'default', 'value' => null],
            [['remarks'], 'default', 'value' => 'APPLICATION RECEIVED'],
            [['application_id', 'status_id'], 'default', 'value' => null],
            [['application_id', 'status_id'], 'integer'],
            [['audit_date'], 'safe'],
            [['remarks'], 'string', 'max' => 255],
            [['user_id'], 'string', 'max' => 50],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppAdmissionStatus::class, 'targetAttribute' => ['status_id' => 'status_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'tracking_id' => 'Tracking ID',
            'application_id' => 'Application ID',
            'status_id' => 'Status ID',
            'remarks' => 'Remarks',
            'audit_date' => 'Audit Date',
            'user_id' => 'User ID',
        ];
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(AppAdmissionStatus::class, ['status_id' => 'status_id']);
    }
}
