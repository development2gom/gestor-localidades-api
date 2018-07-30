<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mod_usuarios_cat_tipos_usuarios".
 *
 * @property string $id_tipo_usuario
 * @property string $txt_nombre
 * @property string $txt_descripcion
 * @property string $b_habiliado
 */
class ModUsuariosCatTiposUsuarios extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mod_usuarios_cat_tipos_usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['txt_nombre', 'txt_descripcion'], 'required'],
            [['b_habiliado'], 'integer'],
            [['txt_nombre'], 'string', 'max' => 100],
            [['txt_descripcion'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_tipo_usuario' => 'Id Tipo Usuario',
            'txt_nombre' => 'Txt Nombre',
            'txt_descripcion' => 'Txt Descripcion',
            'b_habiliado' => 'B Habiliado',
        ];
    }
}
