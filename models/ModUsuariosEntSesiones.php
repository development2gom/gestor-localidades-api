<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mod_usuarios_ent_sesiones".
 *
 * @property string $id_sesion
 * @property string $id_usuario Id del usuario que inicio sesión
 * @property string $id_status Status de la sesión
 * @property string $fch_creacion Fecha en la que el usuario inicio sesión
 * @property string $fch_logout Fecha en la que el usuario finalizo la sesión
 * @property string $num_minutos_sesion Minutos que duraro la sesión del usuario
 * @property string $txt_ip Ip de donde se conecto el usuario
 * @property string $txt_ip_logout Ip de donde el usuario se desconecto
 *
 * @property ModUsuariosCatStatusSesiones $status
 * @property ModUsuariosEntUsuarios $usuario
 */
class ModUsuariosEntSesiones extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mod_usuarios_ent_sesiones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario', 'txt_ip'], 'required'],
            [['id_usuario', 'id_status', 'num_minutos_sesion'], 'integer'],
            [['fch_creacion', 'fch_logout'], 'safe'],
            [['txt_ip', 'txt_ip_logout'], 'string', 'max' => 32],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => ModUsuariosCatStatusSesiones::className(), 'targetAttribute' => ['id_status' => 'id_status']],
            [['id_usuario'], 'exist', 'skipOnError' => true, 'targetClass' => ModUsuariosEntUsuarios::className(), 'targetAttribute' => ['id_usuario' => 'id_usuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_sesion' => 'Id Sesion',
            'id_usuario' => 'Id Usuario',
            'id_status' => 'Id Status',
            'fch_creacion' => 'Fch Creacion',
            'fch_logout' => 'Fch Logout',
            'num_minutos_sesion' => 'Num Minutos Sesion',
            'txt_ip' => 'Txt Ip',
            'txt_ip_logout' => 'Txt Ip Logout',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(ModUsuariosCatStatusSesiones::className(), ['id_status' => 'id_status']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(ModUsuariosEntUsuarios::className(), ['id_usuario' => 'id_usuario']);
    }
}
