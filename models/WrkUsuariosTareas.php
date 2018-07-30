<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wrk_usuarios_tareas".
 *
 * @property string $id_usuario
 * @property string $id_tarea
 *
 * @property ModUsuariosEntUsuarios $usuario
 * @property WrkTareas $tarea
 */
class WrkUsuariosTareas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wrk_usuarios_tareas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario', 'id_tarea'], 'required'],
            [['id_usuario', 'id_tarea'], 'integer'],
            [['id_usuario', 'id_tarea'], 'unique', 'targetAttribute' => ['id_usuario', 'id_tarea']],
            [['id_usuario'], 'exist', 'skipOnError' => true, 'targetClass' => ModUsuariosEntUsuarios::className(), 'targetAttribute' => ['id_usuario' => 'id_usuario']],
            [['id_tarea'], 'exist', 'skipOnError' => true, 'targetClass' => WrkTareas::className(), 'targetAttribute' => ['id_tarea' => 'id_tarea']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_usuario' => 'Id Usuario',
            'id_tarea' => 'Id Tarea',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(ModUsuariosEntUsuarios::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTarea()
    {
        return $this->hasOne(WrkTareas::className(), ['id_tarea' => 'id_tarea']);
    }
}
