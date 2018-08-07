<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_token_seguridad".
 *
 * @property string $id_usuario
 * @property string $fch_limite
 *
 * @property ModUsuariosEntUsuarios $usuario
 */
class CatTokenSeguridad extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cat_token_seguridad';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario'], 'required'],
            [['id_usuario'], 'integer'],
            [['fch_limite'], 'safe'],
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
            'fch_limite' => 'Fch Limite',
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
