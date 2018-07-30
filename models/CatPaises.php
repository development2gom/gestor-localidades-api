<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_paises".
 *
 * @property string $id_pais
 * @property string $txt_nombre
 * @property string $txt_descripcion
 * @property bool $b_habilitado
 *
 * @property CatEstados[] $catEstados
 */
class CatPaises extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cat_paises';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['txt_nombre'], 'required'],
            [['b_habilitado'], 'boolean'],
            [['txt_nombre'], 'string', 'max' => 100],
            [['txt_descripcion'], 'string', 'max' => 2500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_pais' => 'Id Pais',
            'txt_nombre' => 'Txt Nombre',
            'txt_descripcion' => 'Txt Descripcion',
            'b_habilitado' => 'B Habilitado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatEstados()
    {
        return $this->hasMany(CatEstados::className(), ['id_pais' => 'id_pais']);
    }
}
