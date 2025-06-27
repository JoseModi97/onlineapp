<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppContactTypes;

/**
 * ContactTypesSearch represents the model behind the search form of `app\models\AppContactTypes`.
 */
class ContactTypesSearch extends AppContactTypes
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contact_type_id'], 'integer'],
            [['contact_type_name'], 'safe'],
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
        $query = AppContactTypes::find();

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
            'contact_type_id' => $this->contact_type_id,
        ]);

        $query->andFilterWhere(['ilike', 'contact_type_name', $this->contact_type_name]);

        return $dataProvider;
    }
}
