<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_porcentaje_renta_abogados".
 *
 * @property string $id_usuario
 * @property string $num_porcentaje
 *
 * @property ModUsuariosEntUsuarios $usuario
 */
class CatPorcentajeRentaAbogados extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cat_porcentaje_renta_abogados';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario', 'num_porcentaje'], 'required'],
            [['id_usuario', 'num_porcentaje'], 'integer'],
            [['id_usuario'], 'unique'],
            [['id_usuario'], 'exist', 'skipOnError' => true, 'targetClass' => ModUsuariosEntUsuarios::className(), 'targetAttribute' => ['id_usuario' => 'id_usuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_usuario' => 'Id Usuario',
            'num_porcentaje' => 'Num Porcentaje',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(ModUsuariosEntUsuarios::className(), ['id_usuario' => 'id_usuario']);
    }
}
