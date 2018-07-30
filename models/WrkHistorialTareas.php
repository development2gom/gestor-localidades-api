<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wrk_historial_tareas".
 *
 * @property string $id_historial_tarea
 * @property string $id_tarea
 * @property string $id_usuario
 * @property string $txt_descripcion
 * @property string $fch_creacion
 *
 * @property ModUsuariosEntUsuarios $usuario
 * @property WrkTareas $tarea
 */
class WrkHistorialTareas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wrk_historial_tareas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_tarea', 'id_usuario'], 'integer'],
            [['txt_descripcion'], 'string'],
            [['fch_creacion'], 'safe'],
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
            'id_historial_tarea' => 'Id Historial Tarea',
            'id_tarea' => 'Id Tarea',
            'id_usuario' => 'Id Usuario',
            'txt_descripcion' => 'Txt Descripcion',
            'fch_creacion' => 'Fch Creacion',
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
