<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_token_seguridad".
 *
 * @property string $id_usuario
 * @property string $txt_token
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
            [['id_usuario', 'txt_token'], 'required'],
            [['id_usuario'], 'integer'],
            [['txt_token'], 'string', 'max' => 100],
            [['txt_token'], 'unique'],
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
            'txt_token' => 'Txt Token',
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
