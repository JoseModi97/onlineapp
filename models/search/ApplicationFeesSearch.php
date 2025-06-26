<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplicationFees;

/**
 * ApplicationFeesSearch represents the model behind the search form of `app\models\AppApplicationFees`.
 */
class ApplicationFeesSearch extends AppApplicationFees
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['application_fee_id'], 'integer'],
            [['programme_type', 'currency', 'date_added'], 'safe'],
            [['amount'], 'number'],
            [['status'], 'boolean'],
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
        $query = AppApplicationFees::find();

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
            'application_fee_id' => $this->application_fee_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'date_added' => $this->date_added,
        ]);

        $query->andFilterWhere(['ilike', 'programme_type', $this->programme_type])
            ->andFilterWhere(['ilike', 'currency', $this->currency]);

        return $dataProvider;
    }
}
