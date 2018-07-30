<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wrk_tareas_archivadas".
 *
 * @property string $id_tarea
 * @property string $id_usuario
 * @property string $id_tarea_padre
 * @property string $id_localidad
 * @property string $id_tipo
 * @property string $txt_nombre
 * @property string $txt_descripcion
 * @property string $txt_tarea
 * @property string $txt_path
 * @property string $fch_creacion
 * @property string $fch_actualizacion
 * @property string $fch_asignacion
 * @property string $fch_due_date
 * @property string $b_completa
 *
 * @property EntLocalidadesArchivadas $localidad
 * @property CatTiposArchivos $tipo
 * @property ModUsuariosEntUsuarios $usuario
 * @property WrkTareasArchivadas $tareaPadre
 * @property WrkTareasArchivadas[] $wrkTareasArchivadas
 * @property WrkUsuariosTareasArchivadas[] $wrkUsuariosTareasArchivadas
 * @property ModUsuariosEntUsuarios[] $usuarios
 */
class WrkTareasArchivadas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wrk_tareas_archivadas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario', 'id_tarea_padre', 'id_localidad', 'id_tipo', 'b_completa'], 'integer'],
            [['id_localidad', 'txt_nombre'], 'required'],
            [['txt_descripcion', 'txt_tarea'], 'string'],
            [['fch_creacion', 'fch_actualizacion', 'fch_asignacion', 'fch_due_date'], 'safe'],
            [['txt_nombre'], 'string', 'max' => 100],
            [['txt_path'], 'string', 'max' => 500],
            [['id_localidad'], 'exist', 'skipOnError' => true, 'targetClass' => EntLocalidadesArchivadas::className(), 'targetAttribute' => ['id_localidad' => 'id_localidad']],
            [['id_tipo'], 'exist', 'skipOnError' => true, 'targetClass' => CatTiposArchivos::className(), 'targetAttribute' => ['id_tipo' => 'id_tipo']],
            [['id_usuario'], 'exist', 'skipOnError' => true, 'targetClass' => ModUsuariosEntUsuarios::className(), 'targetAttribute' => ['id_usuario' => 'id_usuario']],
            [['id_tarea_padre'], 'exist', 'skipOnError' => true, 'targetClass' => WrkTareasArchivadas::className(), 'targetAttribute' => ['id_tarea_padre' => 'id_tarea']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_tarea' => 'Id Tarea',
            'id_usuario' => 'Id Usuario',
            'id_tarea_padre' => 'Id Tarea Padre',
            'id_localidad' => 'Id Localidad',
            'id_tipo' => 'Id Tipo',
            'txt_nombre' => 'Txt Nombre',
            'txt_descripcion' => 'Txt Descripcion',
            'txt_tarea' => 'Txt Tarea',
            'txt_path' => 'Txt Path',
            'fch_creacion' => 'Fch Creacion',
            'fch_actualizacion' => 'Fch Actualizacion',
            'fch_asignacion' => 'Fch Asignacion',
            'fch_due_date' => 'Fch Due Date',
            'b_completa' => 'B Completa',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocalidad()
    {
        return $this->hasOne(EntLocalidadesArchivadas::className(), ['id_localidad' => 'id_localidad']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTipo()
    {
        return $this->hasOne(CatTiposArchivos::className(), ['id_tipo' => 'id_tipo']);
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
    public function getTareaPadre()
    {
        return $this->hasOne(WrkTareasArchivadas::className(), ['id_tarea' => 'id_tarea_padre']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkTareasArchivadas()
    {
        return $this->hasMany(WrkTareasArchivadas::className(), ['id_tarea_padre' => 'id_tarea']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkUsuariosTareasArchivadas()
    {
        return $this->hasMany(WrkUsuariosTareasArchivadas::className(), ['id_tarea' => 'id_tarea']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios()
    {
        return $this->hasMany(ModUsuariosEntUsuarios::className(), ['id_usuario' => 'id_usuario'])->viaTable('wrk_usuarios_tareas_archivadas', ['id_tarea' => 'id_tarea']);
    }
}
