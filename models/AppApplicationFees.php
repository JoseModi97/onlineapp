<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_application_fees".
 *
 * @property int $application_fee_id
 * @property string|null $programme_type
 * @property float|null $amount
 * @property string|null $currency
 * @property bool|null $status
 * @property string|null $date_added
 */
class AppApplicationFees extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_application_fees';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['programme_type', 'amount', 'currency', 'status', 'date_added'], 'default', 'value' => null],
            [['application_fee_id'], 'required'],
            [['application_fee_id'], 'default', 'value' => null],
            [['application_fee_id'], 'integer'],
            [['amount'], 'number'],
            [['status'], 'boolean'],
            [['date_added'], 'safe'],
            [['programme_type'], 'string', 'max' => 50],
            [['currency'], 'string', 'max' => 20],
            [['application_fee_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'application_fee_id' => 'Application Fee ID',
            'programme_type' => 'Programme Type',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'status' => 'Status',
            'date_added' => 'Date Added',
        ];
    }

}
