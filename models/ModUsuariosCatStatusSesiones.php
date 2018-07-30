<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mod_usuarios_cat_status_sesiones".
 *
 * @property string $id_status
 * @property string $txt_nombre Estatus de la sesión
 * @property string $txt_descripcion Descripción del elemento
 * @property string $b_habilitado Booleano para saber si el registro esta habilitado
 *
 * @property ModUsuariosEntSesiones[] $modUsuariosEntSesiones
 */
class ModUsuariosCatStatusSesiones extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mod_usuarios_cat_status_sesiones';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['txt_nombre'], 'required'],
            [['b_habilitado'], 'integer'],
            [['txt_nombre'], 'string', 'max' => 50],
            [['txt_descripcion'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_status' => 'Id Status',
            'txt_nombre' => 'Txt Nombre',
            'txt_descripcion' => 'Txt Descripcion',
            'b_habilitado' => 'B Habilitado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModUsuariosEntSesiones()
    {
        return $this->hasMany(ModUsuariosEntSesiones::className(), ['id_status' => 'id_status']);
    }
}
