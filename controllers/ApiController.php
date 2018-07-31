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
use app\models\WrkUsuarioUsuarios;

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
        /**
         * Verificar si trae algun valor el token para buscar al usuario 
         */
        if($token){
            /**
             * Buscar al usuario con el token
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->one();

            /**
             * Verificar si el usuario existe
             */
            if($user){
                /**
                 * Buscar localidades pasando los parametro de la peticion como parametro de la funcion search
                 */
                $modelSearch = new EntLocalidadesSearch();
                $dataProvider = $modelSearch->search(Yii::$app->getRequest()->get(), $page);

                return $dataProvider;
            }else{
                throw new HttpException(400, "Usuario no disponible");
            }
        }else{
            throw new HttpException(400, "Usuario no disponible");
        }
    }

    public function actionCreate($token = null){
        $request = Yii::$app->request;

        /**
         * Verificar si trae algun valor el token para buscar al usuario 
         */
        if($token){
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->one();

            /**
             * Verificar si el usuario existe
             */
            if($user){
                /**
                 * Verificar si el usuario tiene los permisos para realizar esta operacion
                 */
                if($user->txt_auth_item == ConstantesWeb::ABOGADO || $user->txt_auth_item == ConstantesWeb::ASISTENTE){
                    $model = new EntLocalidades();
                    $estatus = new EntEstatus();

                    /**
                     * Verificar si trae los parametros 
                     */
                    if($model->load($request->bodyParams, "") && $estatus->load($request->bodyParams, "")){ //print_r($request->bodyParams);exit;

                        /**
                         * Asignar valores a la localidad que no estan en los params
                         */
                        $hoy = Utils::getFechaActual();
                        $model->id_usuario = $user->id_usuario;
                        $model->txt_token = Utils::generateToken('tok');
                        $model->fch_creacion = $hoy;
                        $model->fch_vencimiento_contratro = Utils::changeFormatDateInput($model->fch_vencimiento_contratro);
                        $model->fch_asignacion = Utils::changeFormatDateInput($model->fch_asignacion);

                        /**
                         * Validar si los datos de la localidad son correctos para crear carpeta en dropbox
                         */
                        if($model->validate()){
                            /**
                             * Crear carpeta de dropbox
                             */
                            $dropbox = Dropbox::crearFolder(ConstantesDropbox::NOMBRE_CARPETA . $model->txt_nombre);
                            $decodeDropbox = json_decode(trim($dropbox), true);

                            /**
                             * Con el indice 'metadata' verificamos que se alla creado la carpeta en dropbox
                             */
                            if(isset($decodeDropbox['metadata'])){
                                /**
                                 * Guardar la localidad en la BD
                                 */
                                if($model->save()){
                                    /**
                                     * Verificar si en params esta el parametro 'txt_estatus' para crear un estatus de la localidad
                                     */
                                    if(!empty($request->getBodyParam('txt_estatus'))){
                                        $estatus->id_localidad = $model->id_localidad;
                                        
                                        if(!$estatus->save()){
                                            throw new HttpException(400, "No se guardo el estatus la localidad");                                                                                
                                        }
                                    }

                                    return $model;
                                }else{
                                    throw new HttpException(400, "No se guardo la localidad");
                                }
                            }else{
                                throw new HttpException(400, $decodeDropbox);
                            }
                        }else{
                            throw new HttpException(400, "Usuario no disponible");
                        }
                    }else{
                        throw new HttpException(400, "No hay datos para procesar la petición");
                    }
                }else{
                    throw new HttpException(400, "El usuario no tiene permisos");
                }
            }else{
                throw new HttpException(400, "Usuario no disponible");
            }
        }else{
            throw new HttpException(400, "Usuario no disponible");
        }
    }

    public function actionUpdate($token = null, $cms = null){
        $request = Yii::$app->request;
        //$request->getBodyParam('id');
        // returns all parameters
        //$params = $request->bodyParams;

        $model = null;
        
        /**
         * Verificar si trae algun valor el token para buscar al usuario y el id para buscar la localidad
         */
        if($token && $cms){
            $estatus = new EntEstatus();
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->one();

            /**
             * Verificar si el usuario existe
             */
            if($user){
                /**
                 * Verificar si el usuario tiene los permisos para realizar esta operacion
                 */
                if($user->txt_auth_item == ConstantesWeb::ABOGADO || $user->txt_auth_item == ConstantesWeb::ASISTENTE){

                    /**
                     * Si el usuario es abogado, buscar la localidad creada por el abogado
                     */
                    if($user->txt_auth_item == ConstantesWeb::ABOGADO){
                        $model = EntLocalidades::find()->where(['cms'=>$cms, 'id_usuario'=>$user->id_usuario])->one();
                    }

                    /**
                     * Si el usuario es asistente, buscar la localidad creada por el o su padre abogado
                     */
                    if($user->txt_auth_item == ConstantesWeb::ASISTENTE){
                        $padre = WrkUsuarioUsuarios::find()->where(['id_usuario_hijo'=>$user->id_usuario])->one();
                        $model = EntLocalidades::find()->where(['cms'=>$cms])
                            ->andWhere(['id_usuario'=>$padre->id_usuario_padre])
                            ->orWhere(['id_usuario'=>$user->id_usuario])->one();
                    }

                    /**
                     * Verificar si la localidad existe
                     */
                    if($model){
                        /**
                         * Verificar si trae los parametros 
                         */
                        if($model->load($request->bodyParams, "") && $estatus->load($request->bodyParams, "")){ //print_r($request->bodyParams);exit;    
                            /**
                             * Guardar la localidad en la BD
                             */
                            if($model->save()){
                                /**
                                 * Verificar si en params esta el parametro 'txt_estatus' para crear un estatus de la localidad
                                 */
                                if(!empty($request->getBodyParam('txt_estatus'))){
                                    $estatus->id_localidad = $model->id_localidad;
                                    
                                    if(!$estatus->save()) {
                                        throw new HttpException(400, "No se guardo el estatus la localidad");                                                                                
                                    }
                                }
                                
                                return $model;
                            }else{
                                throw new HttpException(400, "No se guardo la localidad");
                            }
                        }else{
                            throw new HttpException(400, "No hay datos para procesar la petición");
                        }
                    }else{
                        throw new HttpException(400, "No se encontro la localidad");
                    }
                }else{
                    throw new HttpException(400, "El usuario no tiene permisos");
                }
            }else{
                throw new HttpException(400, "Usuario no disponible");
            }
        }else{
            throw new HttpException(400, "No hay datos para procesar la petición");
        }
    }

    public function actionView($cms = null){
        /**
         * Verificar si el parametro cms no es nulo 
         */
        if($cms){
            /**
             * Buscar localidad por el cms
             */
            $model = EntLocalidades::find()->where(['cms'=>$cms])->one();

            /**
             * Verificar si la localidad existe 
             */
            if($model){

                return $model;
            }else{
                throw new HttpException(400, "No se encontro la localidad");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    // public function actionDelete($id){
    //     $model = EntLocalidades::find()->where(['id_localidad'=>$id])->one();

    //     if($model){
    //         if($model->delete()){

    //             echo json_encode(array('status'=>'success', 'message'=>'Se elimino correctamente la localidad'),JSON_PRETTY_PRINT);
    //         }else{
    //             throw new HttpException(400, "No se pudo eliminar la localidad");
    //         }
    //     }else{
    //         throw new HttpException(400, "No se encontro la localidad");
    //     }
    // }
}