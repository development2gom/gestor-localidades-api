<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mod_usuarios_ent_usuarios_facebook".
 *
 * @property string $id_usuario_facebook
 * @property string $id_usuario
 * @property string $id_facebook
 * @property string $txt_url_photo
 */
class ModUsuariosEntUsuariosFacebook extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mod_usuarios_ent_usuarios_facebook';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario', 'id_facebook'], 'required'],
            [['id_usuario', 'id_facebook', 'txt_url_photo'], 'integer'],
            [['id_usuario'], 'unique'],
            [['id_facebook'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_usuario_facebook' => 'Id Usuario Facebook',
            'id_usuario' => 'Id Usuario',
            'id_facebook' => 'Id Facebook',
            'txt_url_photo' => 'Txt Url Photo',
        ];
    }
}
