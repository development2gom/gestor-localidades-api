<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mod_usuarios_ent_usuarios_activacion".
 *
 * @property string $id_usuario_activacion
 * @property string $id_usuario
 * @property string $txt_token
 * @property string $txt_ip_activacion
 * @property string $fch_creacion
 * @property string $fch_activacion
 */
class ModUsuariosEntUsuariosActivacion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mod_usuarios_ent_usuarios_activacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario', 'txt_token'], 'required'],
            [['id_usuario'], 'integer'],
            [['fch_creacion', 'fch_activacion'], 'safe'],
            [['txt_token', 'txt_ip_activacion'], 'string', 'max' => 60],
            [['txt_token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_usuario_activacion' => 'Id Usuario Activacion',
            'id_usuario' => 'Id Usuario',
            'txt_token' => 'Txt Token',
            'txt_ip_activacion' => 'Txt Ip Activacion',
            'fch_creacion' => 'Fch Creacion',
            'fch_activacion' => 'Fch Activacion',
        ];
    }
}
