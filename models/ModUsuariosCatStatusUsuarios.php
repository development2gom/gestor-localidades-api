<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mod_usuarios_cat_status_usuarios".
 *
 * @property string $id_status
 * @property string $txt_nombre Estatus del usuario
 * @property string $txt_descripcion Descripción del elemento
 * @property string $b_habilitado Booleano para saber si el registro esta habilitado
 *
 * @property ModUsuariosEntUsuarios[] $modUsuariosEntUsuarios
 */
class ModUsuariosCatStatusUsuarios extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mod_usuarios_cat_status_usuarios';
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
    public function getModUsuariosEntUsuarios()
    {
        return $this->hasMany(ModUsuariosEntUsuarios::className(), ['id_status' => 'id_status']);
    }
}
