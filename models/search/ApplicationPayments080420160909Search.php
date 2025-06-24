<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplicationPayments080420160909;

/**
 * ApplicationPayments080420160909Search represents the model behind the search form of `app\models\AppApplicationPayments080420160909`.
 */
class ApplicationPayments080420160909Search extends AppApplicationPayments080420160909
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_id', 'application_id', 'transaction_id', 'amount_paid', 'sync_status'], 'integer'],
            [['receipt_no', 'currency_code', 'payment_channel', 'transaction_ref', 'payment_ref', 'processing_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = AppApplicationPayments080420160909::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'payment_id' => $this->payment_id,
            'application_id' => $this->application_id,
            'transaction_id' => $this->transaction_id,
            'amount_paid' => $this->amount_paid,
            'processing_date' => $this->processing_date,
            'sync_status' => $this->sync_status,
        ]);

        $query->andFilterWhere(['ilike', 'receipt_no', $this->receipt_no])
            ->andFilterWhere(['ilike', 'currency_code', $this->currency_code])
            ->andFilterWhere(['ilike', 'payment_channel', $this->payment_channel])
            ->andFilterWhere(['ilike', 'transaction_ref', $this->transaction_ref])
            ->andFilterWhere(['ilike', 'payment_ref', $this->payment_ref]);

        return $dataProvider;
    }
}
