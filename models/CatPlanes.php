<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cat_planes".
 *
 * @property string $id_plan
 * @property string $txt_plan_open_pay
 * @property string $txt_nombre
 * @property double $num_cantidad
 * @property double $num_intentos
 * @property double $num_dias_prueba
 * @property string $num_dia_repeticion
 * @property string $txt_moneda
 * @property string $b_habilitado
 */
class CatPlanes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cat_planes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['txt_plan_open_pay', 'txt_nombre', 'num_cantidad', 'num_intentos', 'num_dias_prueba', 'num_dia_repeticion', 'txt_moneda'], 'required'],
            [['num_cantidad', 'num_intentos', 'num_dias_prueba'], 'number'],
            [['num_dia_repeticion', 'b_habilitado'], 'integer'],
            [['txt_plan_open_pay', 'txt_nombre'], 'string', 'max' => 200],
            [['txt_moneda'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_plan' => 'Id Plan',
            'txt_plan_open_pay' => 'Txt Plan Open Pay',
            'txt_nombre' => 'Txt Nombre',
            'num_cantidad' => 'Num Cantidad',
            'num_intentos' => 'Num Intentos',
            'num_dias_prueba' => 'Num Dias Prueba',
            'num_dia_repeticion' => 'Num Dia Repeticion',
            'txt_moneda' => 'Txt Moneda',
            'b_habilitado' => 'B Habilitado',
        ];
    }
}
