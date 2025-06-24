<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_notifications".
 *
 * @property int $notification_id
 * @property int $applicant_id
 * @property int $application_ref_no
 * @property string $notification_type
 * @property string $recipient
 * @property string|null $sender
 * @property string|null $subject
 * @property string $message
 * @property string|null $date_added
 * @property string|null $date_sent
 * @property int|null $sent_status
 * @property int|null $message_read
 * @property int|null $user_deleted
 */
class AppNotifications extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_notifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sender', 'subject', 'date_added', 'date_sent'], 'default', 'value' => null],
            [['user_deleted'], 'default', 'value' => 0],
            [['applicant_id', 'application_ref_no', 'notification_type', 'recipient', 'message'], 'required'],
            [['applicant_id', 'application_ref_no', 'sent_status', 'message_read', 'user_deleted'], 'default', 'value' => null],
            [['applicant_id', 'application_ref_no', 'sent_status', 'message_read', 'user_deleted'], 'integer'],
            [['message'], 'string'],
            [['date_added', 'date_sent'], 'safe'],
            [['notification_type'], 'string', 'max' => 10],
            [['recipient'], 'string', 'max' => 200],
            [['sender'], 'string', 'max' => 40],
            [['subject'], 'string', 'max' => 120],
            [['applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_id' => 'applicant_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'notification_id' => 'Notification ID',
            'applicant_id' => 'Applicant ID',
            'application_ref_no' => 'Application Ref No',
            'notification_type' => 'Notification Type',
            'recipient' => 'Recipient',
            'sender' => 'Sender',
            'subject' => 'Subject',
            'message' => 'Message',
            'date_added' => 'Date Added',
            'date_sent' => 'Date Sent',
            'sent_status' => 'Sent Status',
            'message_read' => 'Message Read',
            'user_deleted' => 'User Deleted',
        ];
    }
}
