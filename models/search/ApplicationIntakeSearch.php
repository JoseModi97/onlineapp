<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplicationIntake;

/**
 * ApplicationIntakeSearch represents the model behind the search form of `app\models\AppApplicationIntake`.
 */
class ApplicationIntakeSearch extends AppApplicationIntake
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['intake_code'], 'integer'],
            [['intake_name', 'academic_year', 'degree_code', 'application_deadline', 'reporting_date', 'start_date', 'end_date'], 'safe'],
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
        $query = AppApplicationIntake::find();

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
            'intake_code' => $this->intake_code,
            'application_deadline' => $this->application_deadline,
            'reporting_date' => $this->reporting_date,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $query->andFilterWhere(['ilike', 'intake_name', $this->intake_name])
            ->andFilterWhere(['ilike', 'academic_year', $this->academic_year])
            ->andFilterWhere(['ilike', 'degree_code', $this->degree_code]);

        return $dataProvider;
    }
}
