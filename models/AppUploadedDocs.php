<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_uploaded_docs".
 *
 * @property int $uploaded_doc_id
 * @property int|null $applicant_id
 * @property int|null $doc_type_id
 * @property string|null $doc_path
 * @property int|null $follow_up
 */
class AppUploadedDocs extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_uploaded_docs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['applicant_id', 'doc_type_id', 'doc_path', 'follow_up'], 'default', 'value' => null],
            [['applicant_id', 'doc_type_id', 'follow_up'], 'default', 'value' => null],
            [['applicant_id', 'doc_type_id', 'follow_up'], 'integer'],
            [['doc_path'], 'string', 'max' => 210],
            [['applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_id' => 'applicant_id']],
            [['doc_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppDocumentTypes::class, 'targetAttribute' => ['doc_type_id' => 'doc_type_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uploaded_doc_id' => 'Uploaded Doc ID',
            'applicant_id' => 'Applicant ID',
            'doc_type_id' => 'Doc Type ID',
            'doc_path' => 'Doc Path',
            'follow_up' => 'Follow Up',
        ];
    }
}
