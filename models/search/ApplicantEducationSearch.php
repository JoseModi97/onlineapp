<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplicantEducation;

/**
 * ApplicantEducationSearch represents the model behind the search form of `app\models\AppApplicantEducation`.
 */
class ApplicantEducationSearch extends AppApplicantEducation
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['education_id', 'applicant_id', 'edu_system_code', 'year_from', 'year_to', 'points_score', 'pi_gpa', 'cert_source'], 'integer'],
            [['institution_name', 'edu_ref_no', 'grade', 'grade_per_student', 'relevant', 'remarks', 'name_as_per_cert', 'file_path', 'file_name'], 'safe'],
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
        $query = AppApplicantEducation::find();

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
            'education_id' => $this->education_id,
            'applicant_id' => $this->applicant_id,
            'edu_system_code' => $this->edu_system_code,
            'year_from' => $this->year_from,
            'year_to' => $this->year_to,
            'points_score' => $this->points_score,
            'pi_gpa' => $this->pi_gpa,
            'cert_source' => $this->cert_source,
        ]);

        $query->andFilterWhere(['ilike', 'institution_name', $this->institution_name])
            ->andFilterWhere(['ilike', 'edu_ref_no', $this->edu_ref_no])
            ->andFilterWhere(['ilike', 'grade', $this->grade])
            ->andFilterWhere(['ilike', 'grade_per_student', $this->grade_per_student])
            ->andFilterWhere(['ilike', 'relevant', $this->relevant])
            ->andFilterWhere(['ilike', 'remarks', $this->remarks])
            ->andFilterWhere(['ilike', 'name_as_per_cert', $this->name_as_per_cert])
            ->andFilterWhere(['ilike', 'file_path', $this->file_path])
            ->andFilterWhere(['ilike', 'file_name', $this->file_name]);

        return $dataProvider;
    }
}
