<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_application_payments_08_04_2016_09_09".
 *
 * @property int $payment_id
 * @property int $application_id
 * @property int $transaction_id
 * @property string|null $receipt_no
 * @property int|null $amount_paid
 * @property string|null $currency_code
 * @property string|null $payment_channel
 * @property string|null $transaction_ref
 * @property string|null $payment_ref
 * @property string|null $processing_date
 * @property int|null $sync_status
 */
class AppApplicationPayments080420160909 extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_application_payments_08_04_2016_09_09';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['receipt_no', 'amount_paid', 'currency_code', 'payment_channel', 'transaction_ref', 'payment_ref', 'processing_date'], 'default', 'value' => null],
            [['sync_status'], 'default', 'value' => 0],
            [['application_id', 'transaction_id'], 'required'],
            [['application_id', 'transaction_id', 'amount_paid', 'sync_status'], 'default', 'value' => null],
            [['application_id', 'transaction_id', 'amount_paid', 'sync_status'], 'integer'],
            [['processing_date'], 'safe'],
            [['receipt_no'], 'string', 'max' => 30],
            [['currency_code'], 'string', 'max' => 20],
            [['payment_channel', 'transaction_ref', 'payment_ref'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'payment_id' => 'Payment ID',
            'application_id' => 'Application ID',
            'transaction_id' => 'Transaction ID',
            'receipt_no' => 'Receipt No',
            'amount_paid' => 'Amount Paid',
            'currency_code' => 'Currency Code',
            'payment_channel' => 'Payment Channel',
            'transaction_ref' => 'Transaction Ref',
            'payment_ref' => 'Payment Ref',
            'processing_date' => 'Processing Date',
            'sync_status' => 'Sync Status',
        ];
    }

}
