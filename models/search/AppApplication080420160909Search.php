<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplication080420160909;

/**
 * AppApplication080420160909Search represents the model behind the search form of `app\models\AppApplication080420160909`.
 */
class AppApplication080420160909Search extends AppApplication080420160909
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['application_id', 'applicant_id', 'intake_code', 'application_fee_id', 'payment_status', 'sync_status'], 'integer'],
            [['study_center_code', 'application_ref_no', 'application_date', 'offer_accepted', 'final_status', 'processing_date', 'phd_proposal', 'application_form'], 'safe'],
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
        $query = AppApplication080420160909::find();

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
            'application_id' => $this->application_id,
            'applicant_id' => $this->applicant_id,
            'intake_code' => $this->intake_code,
            'application_date' => $this->application_date,
            'application_fee_id' => $this->application_fee_id,
            'payment_status' => $this->payment_status,
            'processing_date' => $this->processing_date,
            'sync_status' => $this->sync_status,
        ]);

        $query->andFilterWhere(['ilike', 'study_center_code', $this->study_center_code])
            ->andFilterWhere(['ilike', 'application_ref_no', $this->application_ref_no])
            ->andFilterWhere(['ilike', 'offer_accepted', $this->offer_accepted])
            ->andFilterWhere(['ilike', 'final_status', $this->final_status])
            ->andFilterWhere(['ilike', 'phd_proposal', $this->phd_proposal])
            ->andFilterWhere(['ilike', 'application_form', $this->application_form]);

        return $dataProvider;
    }
}
