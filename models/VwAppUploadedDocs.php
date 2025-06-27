<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.vw_app_uploaded_docs".
 *
 * @property int $applicant_id
 * @property int $applicant_user_id
 * @property int $uploaded_doc_id
 * @property string|null $doc_path
 * @property string|null $doc_type_name
 */
class VwAppUploadedDocs extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.vw_app_uploaded_docs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['doc_path', 'doc_type_name'], 'default', 'value' => null],
            [['uploaded_doc_id'], 'default', 'value' => 0],
            [['applicant_id', 'applicant_user_id', 'uploaded_doc_id'], 'default', 'value' => null],
            [['applicant_id', 'applicant_user_id', 'uploaded_doc_id'], 'integer'],
            [['applicant_user_id'], 'required'],
            [['doc_path'], 'string', 'max' => 210],
            [['doc_type_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'applicant_id' => 'Applicant ID',
            'applicant_user_id' => 'Applicant User ID',
            'uploaded_doc_id' => 'Uploaded Doc ID',
            'doc_path' => 'Doc Path',
            'doc_type_name' => 'Doc Type Name',
        ];
    }

}
