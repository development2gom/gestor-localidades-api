<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wrk_usuario_usuarios".
 *
 * @property string $id_usuario_padre
 * @property string $id_usuario_hijo
 */
class WrkUsuarioUsuarios extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wrk_usuario_usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_usuario_padre', 'id_usuario_hijo'], 'required'],
            [['id_usuario_padre', 'id_usuario_hijo'], 'integer'],
            [['id_usuario_padre', 'id_usuario_hijo'], 'unique', 'targetAttribute' => ['id_usuario_padre', 'id_usuario_hijo']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_usuario_padre' => 'Id Usuario Padre',
            'id_usuario_hijo' => 'Id Usuario Hijo',
        ];
    }
}
