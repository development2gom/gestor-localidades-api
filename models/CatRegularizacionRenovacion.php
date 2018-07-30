<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_regularizacion_renovacion".
 *
 * @property string $id_catalogo
 * @property string $txt_nombre
 *
 * @property EntLocalidades[] $entLocalidades
 * @property EntLocalidadesArchivadas[] $entLocalidadesArchivadas
 */
class CatRegularizacionRenovacion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cat_regularizacion_renovacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['txt_nombre'], 'required'],
            [['txt_nombre'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_catalogo' => 'Id Catalogo',
            'txt_nombre' => 'Txt Nombre',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntLocalidades()
    {
        return $this->hasMany(EntLocalidades::className(), ['b_status_localidad' => 'id_catalogo']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntLocalidadesArchivadas()
    {
        return $this->hasMany(EntLocalidadesArchivadas::className(), ['b_status_localidad' => 'id_catalogo']);
    }
}
