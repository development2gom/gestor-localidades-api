<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use app\models\searchs\ConCategoiriesSearch;
use app\models\EntProductosSearch;
use app\models\EntLocalidadesSearch;
use app\models\EntLocalidades;
use yii\web\HttpException;
use app\models\ModUsuariosEntUsuarios;
use app\models\ConstantesWeb;
use app\models\Utils;
use app\config\ConstantesDropbox;
use app\models\Dropbox;
use app\models\EntEstatus;

/**
 * ConCategoiriesController implements the CRUD actions for ConCategoiries model.
 */
class ApiController extends Controller
{   
    public $serializer = [
        'class' => 'app\components\SerializerExtends',
        'collectionEnvelope' => 'items',
    ];

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'localidades' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    public function actionLocalidades($token = null, $page = 0){
        
        if($token){
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->one();

            if($user){
                $modelSearch = new EntLocalidadesSearch();
                $dataProvider = $modelSearch->search(Yii::$app->getRequest()->get(), $page);

                return $dataProvider;
            }else{
                throw new HttpException(400, "Usuario no disponible");
            }
        }
    }

    public function actionCreate($token = null){
        $request = Yii::$app->request;

        if($token){
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->one();

            if($user){
                $model = new EntLocalidades();
                $estatus = new EntEstatus();

                if($model->load($request->bodyParams, "") && $estatus->load($request->bodyParams, "")){ //print_r($request->bodyParams);exit;

                    $hoy = Utils::getFechaActual();
                    $model->id_usuario = $user->id_usuario;
                    $model->txt_token = Utils::generateToken('tok');
                    $model->fch_creacion = $hoy;
                    $model->fch_vencimiento_contratro = Utils::changeFormatDateInput($model->fch_vencimiento_contratro);
                    $model->fch_asignacion = Utils::changeFormatDateInput($model->fch_asignacion);

                    if($model->validate()){
                        $dropbox = Dropbox::crearFolder(ConstantesDropbox::NOMBRE_CARPETA . $model->txt_nombre);
                        $decodeDropbox = json_decode(trim($dropbox), true);

                        if(isset($decodeDropbox['metadata'])){
                            
                            if($model->save()){
                                if(!empty($request->getBodyParam('txt_estatus'))){
                                    $estatus->id_localidad = $model->id_localidad;
                                    if ($estatus->save()) {
                                    
                                        return $model;                                        
                                    }else{
                                        throw new HttpException(400, "No se guardo el estatus la localidad");
                                    }
                                }

                                return $model;
                            }else{
                                throw new HttpException(400, "No se guardo la localidad");
                            }
                        }else{
                            print_r($decodeDropbox);exit;
                        }
                    }else{
                        echo "no validado";
                    }exit;
                }
            }else{
                throw new HttpException(400, "Usuario no disponible");
            }
        }
    }

    public function actionUpdate($id){
        $request = Yii::$app->request;
        //$request->getBodyParam('id');

        // returns all parameters
        //$params = $request->bodyParams;

        $model = EntLocalidades::find()->where(['id_localidad'=>$id])->one();

        if($model->load($request->bodyParams, "")){
            if($model->save()){
                
                return $model;
            }else{
                throw new HttpException(400, "No se guardo");
            }
        }
        print_r($params);exit;
    }

    public function actionView($id){
        $model = EntLocalidades::find()->where(['id_localidad'=>$id])->one();

        if($model){

            return $model;
        }else{
            throw new HttpException(400, "No se encontro la localidad");
        }
    }

    public function actionDelete($id){
        $model = EntLocalidades::find()->where(['id_localidad'=>$id])->one();

        if($model){
            if($model->delete()){

                echo json_encode(array('status'=>'success', 'message'=>'Se elimino correctamente la localidad'),JSON_PRETTY_PRINT);
            }else{
                throw new HttpException(400, "No se pudo eliminar la localidad");
            }
        }else{
            throw new HttpException(400, "No se encontro la localidad");
        }
    }
}