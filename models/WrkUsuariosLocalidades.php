<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wrk_usuarios_localidades".
 *
 * @property string $id_localidad
 * @property string $id_usuario
 */
class WrkUsuariosLocalidades extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wrk_usuarios_localidades';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_localidad', 'id_usuario'], 'required'],
            [['id_localidad', 'id_usuario'], 'integer'],
            [['id_localidad', 'id_usuario'], 'unique', 'targetAttribute' => ['id_localidad', 'id_usuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_localidad' => 'Id Localidad',
            'id_usuario' => 'Id Usuario',
        ];
    }
}
