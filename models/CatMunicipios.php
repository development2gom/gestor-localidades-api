<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_municipios".
 *
 * @property string $id_municipio
 * @property string $id_estado Relación con estados
 * @property string $id_area
 * @property string $txt_nombre
 * @property string $txt_descripcion
 * @property double $num_latitud
 * @property double $num_longitud
 * @property bool $b_habilitado
 *
 * @property CatColonias[] $catColonias
 * @property CatEstados $estado
 */
class CatMunicipios extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cat_municipios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_estado', 'txt_nombre'], 'required'],
            [['id_estado', 'id_area'], 'integer'],
            [['num_latitud', 'num_longitud'], 'number'],
            [['b_habilitado'], 'boolean'],
            [['txt_nombre'], 'string', 'max' => 50],
            [['txt_descripcion'], 'string', 'max' => 2500],
            [['id_estado'], 'exist', 'skipOnError' => true, 'targetClass' => CatEstados::className(), 'targetAttribute' => ['id_estado' => 'id_estado']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_municipio' => 'Id Municipio',
            'id_estado' => 'Id Estado',
            'id_area' => 'Id Area',
            'txt_nombre' => 'Txt Nombre',
            'txt_descripcion' => 'Txt Descripcion',
            'num_latitud' => 'Num Latitud',
            'num_longitud' => 'Num Longitud',
            'b_habilitado' => 'B Habilitado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatColonias()
    {
        return $this->hasMany(CatColonias::className(), ['id_municipio' => 'id_municipio']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEstado()
    {
        return $this->hasOne(CatEstados::className(), ['id_estado' => 'id_estado']);
    }
}
