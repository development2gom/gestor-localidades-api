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
use app\models\WrkUsuariosLocalidades;
use app\models\WrkTareas;
use app\models\WrkUsuariosTareas;
use yii\web\UploadedFile;

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

            'asignar-usuario-localidad' => ['PUT', 'PATCH'],
            'eliminar-usuario-localidad' => ['DELETE'],
            'create-tarea' => ['POST'],
            'editar-nombre-tarea' => ['PUT', 'PATCH'],
            'asignar-usuario-tarea' => ['PUT', 'PATCH'],
            'remover-usuario-tarea' => ['DELETE'],
            'responder-tarea' => ['POST'],
            'completar-tarea' => ['PUT', 'PATCH'],
        ];
    }

    /**
     * Mostrar localidades segun el tipo de usuario
     */
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

    /**
     * Crear localidad solo como usuario abogado o asistente
     */
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

    /**
     * Actualizar datos de una localidad
     */
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

    /**
     * Mostrar datos de una localidad
     */
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

    /**
     * Asignar usuario director a localidad como responsable
     */
    public function actionAsignarUsuarioLocalidad($tokenU = null, $cms = null){
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($tokenU && $cms){
            /**
             * Buscar modelos
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$tokenU, 'txt_auth_item'=>ConstantesWeb::CLIENTE, 'id_status'=>2])->one();
            $localidad = EntLocalidades::find()->where(['cms'=>$cms])->one();

            /**
             * Regresar error 400 si no esta el usuario
             */
            if(!$user){
                throw new HttpException(400, "El usuario no esta disponible");
            }
            /**
             * Regresar error 400 si no esta la localidad
             */
            if(!$localidad){
                throw new HttpException(400, "No se encontro la localidad");
            }
            
            /**
             * eliminar relacion si es que ya existe una con la localidad
             */
            $relUserLoc = WrkUsuariosLocalidades::find()->where(['id_localidad'=>$localidad->id_localidad])->one();
            if($relUserLoc)
                $relUserLoc->delete();

            /**
             * Crear nueva relacion y guardar
             */
            $relUserLocalidad = new WrkUsuariosLocalidades();
            $relUserLocalidad->id_localidad = $localidad->id_localidad;
            $relUserLocalidad->id_usuario = $user->id_usuario;

            if($relUserLocalidad->save()){

                return $localidad;
            }else{
                throw new HttpException(400, "No se pudo guardar la relacion");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Eliminar asignacion de usuario director de localidad como responsable
     */
    public function actionEliminarUsuarioLocalidad($tokenU = null, $cms = null){
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($tokenU && $cms){
            /**
             * Buscar modelos
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$tokenU, 'txt_auth_item'=>ConstantesWeb::CLIENTE, 'id_status'=>2])->one();
            $localidad = EntLocalidades::find()->where(['cms'=>$cms])->one();

            /**
             * Regresar error 400 si no esta el usuario
             */
            if(!$user){
                throw new HttpException(400, "El usuario no esta disponible");
            }
            /**
             * Regresar error 400 si no esta la localidad
             */
            if(!$localidad){
                throw new HttpException(400, "No se encontro la localidad");
            }
            
            /**
             * Buscar relacion y eliminar
             */
            $relUserLocalidad = WrkUsuariosLocalidades::find()->where(['id_usuario'=>$user->id_usuario, 'id_localidad'=>$localidad->id_localidad])->one();
            if($relUserLocalidad){
                if($relUserLocalidad->delete()){

                    return $localidad;
                }else{
                    throw new HttpException(400, "No se pudo eliminar la relación");
                }
            }else{
                throw new HttpException(400, "No se encuentra esa relación");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Crear tarea en una localidad tarea
     */
    public function actionCreateTarea($token = null, $cms = null){
        $request = Yii::$app->request;

        /**
         * Validar que vengan los parametros en la peticion
         */
        if($token){
            /**
             * Buscar usuario que sea abogado, asistente o colaborador
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->andWhere(['txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->one();

            if($user){
                $localidad = EntLocalidades::find()->where(['cms'=>$cms])->one();
                if($localidad){
                    $model = new WrkTareas();
                    
                    if($model->load($request->bodyParams, "")){
                        /**
                         * Asignar valores a la localidad que no estan en los params
                         */
                        $hoy = Utils::getFechaActual();
                        $model->fch_creacion = $hoy;
                        $model->id_localidad = $localidad->id_localidad;
                        $model->id_usuario = $user->id_usuario;

                        if($model->save()){
                            
                            return $localidad;
                        }
                    }else{
                        throw new HttpException(400, "No hay datos para crear la tarea");
                    }
                }else{
                    throw new HttpException(400, "No existe la localidad");
                }
            }else{
                throw new HttpException(400, "El usuario no tiene los permisos para realizar esta acción");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Editar el nombre de la tarea
     */
    public function actionEditarNombreTarea($token = null, $id = 0){
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($token && $id){
            /**
             * Buscar usuario que sea abogado, asistente o director
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$tokenU, 'id_status'=>2])->andWhere(['txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->orWhere(['txt_auth_item'=>ConstantesWeb::CLIENTE])
                ->one();

            if($user){
                $tarea = WrkTareas::find()->where(['id_tarea'=>$id])->one();
                if($tarea){
                    if($tarea->load($request->bodyParams, "")){
                        if($tarea->save()){

                        }
                    }
                }else{
                    throw new HttpException(400, "La tarea no existe");
                }
            }else{
                throw new HttpException(400, "El usuario no tiene los permisos para realizar esta acción");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Asignar un usuario colaborador a una tarea
     */
    public function actionAsignarUsuarioTarea($token = null, $id = 0){
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($token && $id){
            /**
             * Validar que el usuario exista y que sea colaborador
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->andWhere(['txt_auth_item'=>ConstantesWeb::COLABORADOR])->one();

            if($user){
                /**
                 * Buscar tarea por id
                 */
                $tarea = WrkTareas::find()->where(['id_tarea'=>$id])->one();

                if($tarea){
                    /**
                     * Buscar si ya hay alguien asignado a la tarea
                     */
                    $rel = WrkUsuariosTareas::find()->where(['id_tarea'=>$tarea->id_tarea])->one();
                    if($rel){
                        /**
                         * Eliminar la relacion existente
                         */
                        $rel->delete(); 
                    }else{
                        /**
                         * Crear una nueva relacion colaborador/tarea y guardar
                         */
                        $nuevaRel = new WrkUsuariosTareas();
                        $nuevaRel->id_usuario = $user->id_usuario;
                        $nuevaRel->id_tarea = $tarea->id_tarea;

                        if($nuevaRel->save()){

                            return $tarea->localidad;
                        }else{
                            throw new HttpException(400, "No se pudo guardar la relacion entre usuario y tarea");
                        }
                    }
                }else{
                    throw new HttpException(400, "La tarea no existe");
                }
            }else{
                throw new HttpException(400, "El usuario no puede ser asignado a una localidad");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Quitar relación de usuario colaborador responsable de tarea
     */
    public function actionRemoverUsuarioTarea($token = null, $id = 0){
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($token && $id){
             /**
             * Validar que el usuario exista y que sea colaborador
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->andWhere(['txt_auth_item'=>ConstantesWeb::COLABORADOR])->one();

            if($user){
                /**
                 * Buscar tarea por id
                 */
                $tarea = WrkTareas::find()->where(['id_tarea'=>$id])->one();
                
                if($tarea){
                    /**
                     * Buscar si ya hay alguien asignado a la tarea
                     */
                    $rel = WrkUsuariosTareas::find()->where(['id_tarea'=>$tarea->id_tarea, 'id_usuario'=>$user->id_usuario])->one();
                    if($rel){
                        /**
                         * Eliminar la relacion existente
                         */
                        if($rel->delete()){

                            return $tarea->localidad;
                        }else{
                            throw new HttpException(400, "No se pudo eliminar la relación");
                        } 
                    }else{
                        throw new HttpException(400, "La tarea no tiene ninguna relación");
                    }
                }else{
                    throw new HttpException(400, "La tarea no existe");
                }
            }else{

            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Responder tareas con archivo o texto
     * 
     * EJEMPLO CURL CON TAREA DE ARCHIVO
     *  curl -H "Content-type: multipart/form-data" "http://localhost/gestor-localidades-api/web/api/responder-tarea?id=114" -X POST -F 'fileTarea=@create.txt'
     * 
     * ELEMPLO CURL CON TAREA DE TEXTO
     * curl -H "Content-type: application/json" "http://localhost/gestor-localidades-api/web/api/responder-tarea?id=95" -X POST -d "{\"txt_tarea\":\"qwerty qwerty\"}"
     */
    public function actionResponderTarea($id = 0){
        $request = Yii::$app->request;
        //print_r($_FILES);exit;
        
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($id){
            /**
             * Buscar tarea por id
             */
            $tarea = WrkTareas::find()->where(['id_tarea'=>$id])->one();
            $localidad = $tarea->localidad;

            if($tarea){
                /**
                 * Verificar si la tarea es de tipo archivo
                 */
                if($tarea->id_tipo == ConstantesWeb::TAREA_ARCHIVO){
                    /**
                     * Cargar archivo mandado por post
                     */
                    $fileDropbox = UploadedFile::getInstanceByName('fileTarea');

                    if($fileDropbox){
                        /**
                         * Subir archivo a dropbox
                         */
                        $dropbox = Dropbox::subirArchivo($localidad->txt_nombre, $fileDropbox);
                        $decodeDropbox = json_decode(trim($dropbox), TRUE);

                        /**
                         * Verificar que la respuesta de dropbox sea correcta
                         */
                        if(isset($decodeDropbox['path_display'])){
                            /**
                             * Agregar path del archivo de la tarea a modelo
                             */
                            $tarea->txt_path = $decodeDropbox['path_display'];
                        }else{
                            throw new HttpException(400, "No se guardo correctamente el archivo en dropbox");
                        }
                    }else{
                        throw new HttpException(400, "No se cargo ningun archivo");
                    }
                /**
                 * Verificar si la tarea es de tipo texto
                 */
                }else if($tarea->id_tipo == ConstantesWeb::TAREA_ABIERTO){
                    /**
                     * Cargar dato de txt_tarea cargado por post 
                     */
                    if($tarea->load($request->bodyParams, "")){
                        
                    }else{
                        throw new HttpException(400, "No hay datos para responder la tarea");
                    }
                }else{
                    throw new HttpException(400, "No se definio el tipo de tarea");
                }

                /**
                 * Actualizar fecha y guardar tarea
                 */
                $tarea->fch_actualizacion = date("Y-m-d H:i:s");
                if($tarea->save()){
                    
                    return $localidad;
                }
            }else{
                throw new HttpException(400, "La tarea no existe");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Completar tarea por usuarios abogados, asistentes o dorectores juridicos
     */
    public function actionCompletarTarea($token = null, $id = 0){
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($token && $id){
            /**
             * Buscar usuario que sea abogado, asistente o director
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->andWhere(['txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->orWhere(['txt_auth_item'=>ConstantesWeb::CLIENTE])
                ->one();

            if($user){
                $tarea = WrkTareas::find()->where(['id_tarea'=>$id])->one();
                $localidad = $tarea->localidad;

                if($tarea){
                    $tarea->fch_actualizacion = date("Y-m-d H:i:s");
                    $tarea->b_completa = 1;

                    if(!$tarea->save()){
                        throw new HttpException(400, "La tarea no se guardo correctamente");
                    }

                    return $localidad;
                }else{
                    throw new HttpException(400, "La tarea no existe");
                }
            }else{
                throw new HttpException(400, "El usuario no tiene los permisos para realizar esta acción");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }
}