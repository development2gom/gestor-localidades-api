<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wrk_documentos".
 *
 * @property string $id_documento
 * @property string $id_tarea
 * @property string $id_usuario
 * @property string $txt_nombre
 * @property string $txt_url_archivo
 * @property string $fch_creacion
 *
 * @property ModUsuariosEntUsuarios $usuario
 * @property WrkTareas $tarea
 */
class WrkDocumentos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wrk_documentos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_tarea', 'id_usuario', 'txt_url_archivo'], 'required'],
            [['id_tarea', 'id_usuario'], 'integer'],
            [['fch_creacion'], 'safe'],
            [['txt_nombre'], 'string', 'max' => 100],
            [['txt_url_archivo'], 'string', 'max' => 500],
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
            'id_documento' => 'Id Documento',
            'id_tarea' => 'Id Tarea',
            'id_usuario' => 'Id Usuario',
            'txt_nombre' => 'Txt Nombre',
            'txt_url_archivo' => 'Txt Url Archivo',
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
