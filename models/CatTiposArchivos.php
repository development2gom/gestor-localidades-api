<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_tipos_archivos".
 *
 * @property string $id_tipo
 * @property string $txt_nombre
 *
 * @property WrkTareasArchivadas[] $wrkTareasArchivadas
 */
class CatTiposArchivos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cat_tipos_archivos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['txt_nombre'], 'required'],
            [['txt_nombre'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_tipo' => 'Id Tipo',
            'txt_nombre' => 'Txt Nombre',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkTareasArchivadas()
    {
        return $this->hasMany(WrkTareasArchivadas::className(), ['id_tipo' => 'id_tipo']);
    }
}
