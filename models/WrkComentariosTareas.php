<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wrk_comentarios_tareas".
 *
 * @property string $id_comentario
 * @property string $id_usuario
 * @property string $id_tarea
 * @property string $txt_comentario
 * @property string $fch_comentario
 * @property string $b_habilitado
 *
 * @property ModUsuariosEntUsuarios $usuario
 * @property WrkTareas $tarea
 */
class WrkComentariosTareas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wrk_comentarios_tareas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario', 'id_tarea'], 'required'],
            [['id_usuario', 'id_tarea', 'b_habilitado'], 'integer'],
            [['txt_comentario'], 'string'],
            [['fch_comentario'], 'safe'],
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
            'id_comentario' => 'Id Comentario',
            'id_usuario' => 'Id Usuario',
            'id_tarea' => 'Id Tarea',
            'txt_comentario' => 'Txt Comentario',
            'fch_comentario' => 'Fch Comentario',
            'b_habilitado' => 'B Habilitado',
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
