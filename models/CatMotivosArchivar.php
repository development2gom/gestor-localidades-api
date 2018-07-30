<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_motivos_archivar".
 *
 * @property string $id_motivo
 * @property string $txt_motivo
 * @property string $b_habilitado
 */
class CatMotivosArchivar extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cat_motivos_archivar';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['txt_motivo'], 'required'],
            [['b_habilitado'], 'integer'],
            [['txt_motivo'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_motivo' => 'Id Motivo',
            'txt_motivo' => 'Txt Motivo',
            'b_habilitado' => 'B Habilitado',
        ];
    }
}
