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
use app\models\EntLocalidadesArchivadas;
use app\models\EntEstatusArchivados;
use app\models\WrkTareasArchivadas;
use app\models\WrkUsuariosTareasArchivadas;
use app\models\WrkUsuariosLocalidadesArchivadas;
use yii\helpers\Url;
use app\models\CatPorcentajeRentaAbogados;
use app\models\UsuariosSearch;
use app\models\CatTokenSeguridad;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\models\LoginForm;
use yii\web\Response;
use app\models\ModUsuariosEntUsuariosCambioPass;

/**
 * ConCategoiriesController implements the CRUD actions for ConCategoiries model.
 */
class ApiController extends Controller
{   
    public $serializer = [
        'class' => 'app\components\SerializerExtends',
        'collectionEnvelope' => 'items',
    ];
    private $seguridad = true;
    private $usuarioLogueado;

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'login' => ['POST'],
            'peticion-pass' => ['POST'],

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
            'archivar-localidad' => ['PUT', 'PATCH'],
            'desarchivar-localidad' => ['PUT', 'PATCH'],
            'descargar-archivo' => ['GET', 'HEAD'],
            'descargar-archivo-archivada' => ['GET', 'HEAD'],

            'usuarios' => ['GET', 'HEAD'],
            'crear-usuario' => ['POST'],
            'editar-usuario' => ['PUT', 'PATCH'],
            'bloquear-usuario' => ['PUT', 'PATCH'],
            'activar-usuario' => ['PUT', 'PATCH'],
            'reenviar-email-bienvenida' => ['GET', 'HEAD'],
        ];
    }

    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(), [
                'authenticator' => [
                    'class' => CompositeAuth::className(),
                    'except' => ['login', 'peticion-pass'],
                    'authMethods' => [
                        HttpBasicAuth::className(),
                        HttpBearerAuth::className(),
                        QueryParamAuth::className(),
                    ],
                ],
            ]
        );
    }

    /**
     * EJEMPLO DE CURL CON HEADERS PARA AUTENTIFICACION
     * curl -H "Content-type:application/json" -H "Authorization: Bearer usr260f29f837a575a57b3dcc4baa206e4b5aabf426d89"
     * "http://localhost/gestor-localidades-api/web/api/bloquear-usuario?token=usr2d551f91aef895993ee7d77d7354162f5b685b949ff21" -X PUT
     */

    public function beforeAction($action){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        if($this->seguridad){
            if(($action->id == "login") || ($action->id == "peticion-pass")){
                return parent::beforeAction($action);                                
            }else{
                if(isset($request->headers['authorization'])){
                    $tokenUser = explode(" ", $request->headers['authorization']);//print_r($tokenUser[1]);exit;
                    $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$tokenUser[1]])->one();
                    if($user){
                        $usuarioLogueado = $user;
                        $fechaSeg = CatTokenSeguridad::find()->where(['id_usuario'=>$user->id_usuario])->one();
                        if($fechaSeg){
                            $hoy = date('Y-m-d H:i');
                            if($fechaSeg->fch_limite < $hoy){
                                throw new HttpException(400, "Es necesario volverse a loguear");
                            }
    
                            return parent::beforeAction($action);
                        }
                    }
                }else{
                    throw new HttpException(400, "Falta autentificación");
                }
            }
        }else{
            return parent::beforeAction($action);
        }
   }

    public function actionLogin(){
        $request = Yii::$app->request;

        $model = new LoginForm();
		$model->scenario = 'login';

		if ($model->load($request->bodyParams, "")) {
            
			ActiveForm::validate($model);
		}

		if($model->load($request->bodyParams, "")){
            if($model->login()){
                $user = ModUsuariosEntUsuarios::findByEmail($model->username);

                $fechaActual = date('Y-m-d H:i');
                $semana = date("Y-m-d H:i", strtotime($fechaActual . '+7 day'));
                $seguridad = CatTokenSeguridad::find()->where(['id_usuario'=>$user->id_usuario])->one();
                $seguridad->fch_limite = $semana;
                $seguridad->save();

                return $user;
            }
		}
    }

    public function actionPeticionPass(){
        $request = Yii::$app->request;

        $model = new LoginForm ();
        $model->scenario = 'recovery';
        
		if ($model->load($request->bodyParams, "") && $model->validate()){
			
			$peticionPass = new ModUsuariosEntUsuariosCambioPass();
			
			$peticionPass->saveUsuarioPeticion ( $model->userEncontrado->id_usuario );
			$user = $peticionPass->idUsuario;
			
			// Enviar correo de activación
			$utils = new Utils();
			// Parametros para el email
			$parametrosEmail ['url'] = ConstantesDropbox::URL_EMAILS . 'cambiar-pass/' . $peticionPass->txt_token;
			$parametrosEmail ['user'] = $user->getNombreCompleto ();
			
			// Envio de correo electronico
			if($utils->sendEmailRecuperarPassword($user->txt_email, $parametrosEmail)){
                throw new HttpException(200, "Se ha enviado un correo eléctronico a '".$user->txt_email."' con instrucciones para recuperar tu contraseña");
            }else{
                throw new HttpException(400, "No se pudo mandar el email");
            }
		}else{
            throw new HttpException(400, "Usuario no encontrado");
        }
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
                        $model = EntLocalidades::find()->where(['cms'=>$cms, 'id_usuario'=>$padre->id_usuario_padre])
                            ->orWhere(['cms'=>$cms, 'id_usuario'=>$user->id_usuario])->one();
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

    /**
     * Archivar localidades por usuario abogado o asistente
     */
    public function actionArchivarLocalidad($token = null, $cms = null, $mot = 0){
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($token && $cms && $mot){
            /**
             * Buscar usuario que sea abogado o asistente
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->one();

            if($user){
                /**
                 * Buscar localidad por el cms
                 */
                $localidad = EntLocalidades::find()->where(['cms'=>$cms])->one();

                if($localidad){
                    $archivada = new EntLocalidadesArchivadas();
                    $archivada->attributes = $localidad->attributes;
                    $archivada->id_localidad = $localidad->id_localidad;
                    $archivada->b_archivada = $mot;

                    /**
                     * Archivar localidad con todos sus datos relacionados
                     */
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($archivada->save()) {
                            $tareas = $localidad->wrkTareas;
                            $estatus = EntEstatus::find()->where(['id_localidad'=>$localidad->id_localidad])->all();

                            foreach($estatus as $es){
                                $estatusArch = new EntEstatusArchivados();
                                $estatusArch->id_localidad = $archivada->id_localidad;
                                $estatusArch->txt_estatus = $es->txt_estatus;
                                $estatusArch->fch_creacion = $es->fch_creacion;

                                if(!$estatusArch->save()){
                                    $transaction->rollBack();
                                    throw new HttpException(400, "No se guardo el estatus de la localidad");
                                }else{
                                    $es->delete();
                                }
                            }

                            if($tareas){
                                foreach ($tareas as $tarea) {
                                    $tareaArchivada = new WrkTareasArchivadas();
                                    $tareaArchivada->attributes = $tarea->attributes;
                                    $tareaArchivada->id_tarea = $tarea->id_tarea;
                                    $tareaArchivada->id_localidad = $archivada->id_localidad;

                                    if ($tareaArchivada->save()) {
                                        $usersTareas = WrkUsuariosTareas::find()->where(['id_tarea' => $tarea->id_tarea])->all();
                                        if ($usersTareas) {
                                            foreach ($usersTareas as $userTarea) {
                                                $userTareaArchivada = new WrkUsuariosTareasArchivadas();
                                                $userTareaArchivada->attributes = $userTarea->attributes;
                                                $userTareaArchivada->id_tarea = $userTarea->id_tarea;

                                                if (!$userTareaArchivada->save()) {
                                                    $transaction->rollBack();
                                                    throw new HttpException(400, "No se guardo relacion usuario/tarea");                                                    
                                                }
                                                $userTarea->delete();
                                            }
                                        }
                                        
                                        $usersLocs = WrkUsuariosLocalidades::find()->where(['id_localidad' => $localidad->id_localidad])->one();
                                        //print_r($usersLocs);exit;
                                        if ($usersLocs) {
                                            $userLocArchivada = new WrkUsuariosLocalidadesArchivadas();
                                            $userLocArchivada->attributes = $usersLocs->attributes;

                                            if (!$userLocArchivada->save()) {
                                                $transaction->rollBack();
                                                throw new HttpException(400, "No se guardo relacion usuario/localidad");                                                                                                    
                                            }
                                            $usersLocs->delete();
                                        }
                                    }else{
                                        $transaction->rollBack();
                                        throw new HttpException(400, "No se guardo relacion la tarea al archivar");                                                                                            
                                    }
                                    $tarea->delete();
                                }
                            }
                            $localidad->delete();
                            $transaction->commit();
                            
                            return $archivada;
                        }else{
                            $transaction->rollBack();
                            
                            throw new HttpException(400, "La localidad no ha sido archivada");
                        }
                        $transaction->commit();

                        return $archivada;
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    }

                }else{
                    throw new HttpException(400, "La localidad no existe");
                }
            }else{
                throw new HttpException(400, "El usuario no tiene los permisos para realizar esta acción");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Desarchivar localidades por usuario abogado o asistente
     */
    public function actionDesarchivarLocalidad($token = null, $cms = null){
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($token && $cms){
            /**
             * Buscar usuario que sea abogado o asistente
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->one();

            if($user){
                /**
                 * Buscar localidad archivada por el cms
                 */
                $archivada = EntLocalidadesArchivadas::find()->where(['cms'=>$cms])->one();

                if($archivada){
                    $localidad = new EntLocalidades();
                    $localidad->attributes = $archivada->attributes;
                    $localidad->b_archivada = 0;

                    $transaction = EntLocalidades::getDb()->beginTransaction();
                    try{
                        if($localidad->save()){
                            $tareasArchivadas = $archivada->wrkTareasArchivadas;
            
                            $estatusArch = EntEstatusArchivados::find()->where(['id_localidad'=>$archivada->id_localidad])->all();
            
                            foreach($estatusArch as $es){
                                $estatus = new EntEstatus();
                                $estatus->id_localidad = $localidad->id_localidad;
                                $estatus->txt_estatus = $es->txt_estatus;
                                $estatus->fch_creacion = $es->fch_creacion;
            
                                if(!$estatus->save()){
                                    $transaction->rollBack();
                                    throw new HttpException(400, "No se guardo el estatus de la localidad");
                                }else{
                                    $es->delete();
                                }
                            }
            
                            foreach($tareasArchivadas as $tareaArchivada){
                                $tarea = new WrkTareas();
                                $tarea->attributes = $tareaArchivada->attributes;
                                $tarea->id_localidad = $localidad->id_localidad;
                                $tarea->txt_path = $tareaArchivada->txt_path;
                                $tarea->txt_tarea = $tareaArchivada->txt_tarea;
            
                                if(!$tarea->save()){
                                    $transaction->rollBack ();
                                    throw new HttpException(400, "No se guardo la tarea al desarchivar localidad");                                    
                                }else{
                                    $userTareaArchivada = WrkUsuariosTareasArchivadas::find()->where(['id_tarea'=>$tareaArchivada->id_tarea])->one();
                                    if($userTareaArchivada){
                                        $userTarea = new WrkUsuariosTareas();
                                        $userTarea->id_usuario = $userTareaArchivada->id_usuario;
                                        $userTarea->id_tarea = $tarea->id_tarea;
            
                                        if(!$userTarea->save()){
                                            $transaction->rollBack ();
                                            throw new HttpException(400, "No se guardo relacion usuario/tarea");
                                        }
                                    }
                                }
                                $tareaArchivada->delete();
                            }
                            $userLocArchivada = WrkUsuariosLocalidadesArchivadas::find()->where(['id_localidad'=>$archivada->id_localidad])->one();
                            if($userLocArchivada){
                                $userLoc = new WrkUsuariosLocalidades();
                                $userLoc->id_localidad = $localidad->id_localidad;
                                $userLoc->id_usuario = $userLocArchivada->id_usuario;
                                
                                if(!$userLoc->save()){
                                    $transaction->rollBack ();
                                    throw new HttpException(400, "No se guardo relacion usuario/localidad");                                    
                                }
                            }
                        }else{
                            $transaction->rollBack();
                            throw new HttpException(400, "No se guardo localidad");                            
                        }
                        $archivada->delete();
                        $transaction->commit ();
            
                        return $localidad;
                    }catch(\Exception $e) {
                        $transaction->rollBack ();
                        throw $e;
                    }
                }else{
                    throw new HttpException(400, "La localidad no existe");
                }
            }else{
                throw new HttpException(400, "El usuario no tiene los permisos para realizar esta acción");
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
                
                // Enviar correo
                $utils = new Utils();
                // Parametros para el email
                $parametrosEmail['localidad'] = $localidad->txt_nombre;
                $parametrosEmail['user'] = $user->getNombreCompleto();
                $parametrosEmail['url'] = ConstantesDropbox::URL_EMAILS . 'localidades/index/?token=' . $user->txt_token;

                // Envio de correo electronico
                $utils->sendEmailAsignacion($user->txt_email, $parametrosEmail);
                
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
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ASISTENTE])
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
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$tokenU, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_token'=>$tokenU, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->orWhere(['txt_token'=>$tokenU, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::CLIENTE])
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
                    }
                    /**
                     * Crear una nueva relacion colaborador/tarea y guardar
                     */
                    $nuevaRel = new WrkUsuariosTareas();
                    $nuevaRel->id_usuario = $user->id_usuario;
                    $nuevaRel->id_tarea = $tarea->id_tarea;

                    if($nuevaRel->save()){
                        $abogado = $tarea->usuario;
                        $loc = $tarea->localidad;

                        // Enviar correo
                        $utils = new Utils();
                        // Parametros para el email
                        $parametrosEmail['tarea'] = $tarea->txt_nombre;
                        $parametrosEmail['loc'] = $loc->txt_nombre;
                        $parametrosEmail['user'] = $user->getNombreCompleto();
                        $parametrosEmail['abogado'] = $abogado->getNombreCompleto();
                        $parametrosEmail['url'] = ConstantesDropbox::URL_EMAILS . 'localidades/index/?token=' . $user->txt_token . '&tokenLoc=' . $loc->txt_token;
                        
                        // Envio de correo electronico
                        $utils->sendEmailAsignacionTarea($user->txt_email, $parametrosEmail);

                        return $tarea->localidad;
                    }else{
                        throw new HttpException(400, "No se pudo guardar la relacion entre usuario y tarea");
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
                    $userActual = $usuarioLogueado;
                    $user = $tarea->usuario;
                    $localidad = $tarea->localidad;

                    // Enviar correo
                    $utils = new Utils ();
                    // Parametros para el email
                    $parametrosEmail ['localidad'] = $localidad;
                    $parametrosEmail ['tarea'] = $tarea->txt_nombre;
                    $parametrosEmail ['user'] = $user->getNombreCompleto ();
                    $parametrosEmail ['userActual'] = $userActual->getNombreCompleto ();
                    $parametrosEmail ['url'] = ConstantesDropbox::URL_EMAILS . 'localidades/index?token=' . $user->txt_token . '&tokenLoc=' . $localidad->txt_token;
                    
                    // Envio de correo electronico
                    $utils->sendEmailCargaTareas( $user->txt_email,$parametrosEmail );

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
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::CLIENTE])
                ->one();

            if($user){
                /**
                 * Buscar tarea por id
                 */
                $tarea = WrkTareas::find()->where(['id_tarea'=>$id])->one();
                /**
                 * Sacar localida dde modelo tarea
                 */
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

    /**
     * Eliminar tarea por usuarios abogados, asistentes o dorectores juridicos
     */
    public function actionEliminarTarea($token = null, $id = 0){
        /**
         * Validar que vengan los parametros en la peticion
         */
        if($token && $id){
            /**
             * Buscar usuario que sea abogado, asistente o director
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::CLIENTE])
                ->one();

            if($user){
                /**
                 * Buscar tarea por id
                 */
                $tarea = WrkTareas::find()->where(['id_tarea'=>$id])->one();
                /**
                 * Sacar localida dde modelo tarea
                 */
                $localidad = $tarea->localidad;

                if($tarea){
                    if(!$tarea->delete()){
                        throw new HttpException(400, "La tarea no se elimino correctamente");
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

    /**
     * Descargar archivo de tarea
     */
    public function actionDescargarArchivo($id = 0){
        /**
         * Validar que venga el parametro en la peticion
         */
        if($id){
            /**
             * Buscar tarea por id
             */
            $tarea = WrkTareas::find()->where(['id_tarea'=>$id, 'id_tipo'=>ConstantesWeb::TAREA_ARCHIVO])->one();

            if($tarea){
                $dropbox = Dropbox::descargarArchivo($tarea->txt_path);
                $decodeDropbox = json_decode(trim($dropbox), TRUE);

                if(isset($decodeDropbox['link'])){
                    return $this->redirect($decodeDropbox['link']);
                }else{
                    throw new HttpException(400, "No se encontro el archivo");
                }
            }else{
                throw new HttpException(400, "La tarea no existe");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Descargar archivo de tarea archivada
     */
    public function actionDescargarArchivoArchivada($id = 0){
        /**
         * Validar que venga el parametro en la peticion
         */
        if($id){
            /**
             * Buscar tarea por id
             */
            $tarea = WrkTareasArchivadas::find()->where(['id_tarea'=>$id, 'id_tipo'=>ConstantesWeb::TAREA_ARCHIVO])->one();

            if($tarea){
                $dropbox = Dropbox::descargarArchivo($tarea->txt_path);
                $decodeDropbox = json_decode(trim($dropbox), TRUE);

                if(isset($decodeDropbox['link'])){
                    return $this->redirect($decodeDropbox['link']);
                }else{
                    throw new HttpException(400, "No se encontro el archivo");
                }
            }else{
                throw new HttpException(400, "La tarea no existe");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    
    
    /**
     * Crear usuarios
     */
    public function actionCrearUsuario($token = null){
        $request = Yii::$app->request;
        $auth = Yii::$app->authManager;

        /**
         * Validar que venga el parametro en la peticion
         */
        if($token){
            /**
             * Buscar usuario que hace la peticion
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2])->one();

            if($user){
                $nuevoUser = new ModUsuariosEntUsuarios();
                
                if($nuevoUser->load($request->bodyParams, "")){
                    /**
                     * Asignar un passworg al usuario
                     */
                    $nuevoUser->password = $nuevoUser->randomPassword();
                    $nuevoUser->repeatPassword = $nuevoUser->password;
                    


                    /**
                     * Si el usuario que hace la peticion es SUPER-ADMIN
                     */
                    if($user->txt_auth_item == ConstantesWeb::SUPER_ADMIN){
                        /**
                         * Si el usuario a crear es abogado
                         */
                        if($nuevoUser->txt_auth_item == ConstantesWeb::ABOGADO){
                            
                            /**
                             * Guardar usuario
                             */
                            if($usuario = $nuevoUser->signup()){
                                $nuevoUser->enviarEmailBienvenida();

                                /**
                                 * Guardar porcentaje de abogado
                                 */
                                $porcentajeRenta = new CatPorcentajeRentaAbogados();
                                $porcentajeRenta->id_usuario = $nuevoUser->id_usuario;
                                $porcentajeRenta->num_porcentaje = 10;
                                
                                if(!$porcentajeRenta->save()){
                                    throw new HttpException(400, "No se pudo guardar el procentaje del usuario");
                                }

                                $token = new CatTokenSeguridad();

                                $fechaActual = date('Y-m-d H:i');
                                $token->fch_limite = $fechaActual;
                                $token->id_usuario = $nuevoUser->id_usuario;

                                if(!$token->save()){
                                    throw new HttpException(400, "No se pudo guardar el token de seguridad");
                                }

                                return $nuevoUser;
                            }else{
                                throw new HttpException(400, "No se pudo guardar al usuario");
                            }
                        }else{
                            throw new HttpException(400, "No puedes crear otro tipo de usuarios");
                        }



                    /**
                     * Si el usuario que hace la peticion es ABOGADO
                     */    
                    }else if($user->txt_auth_item == ConstantesWeb::ABOGADO){
                        /**
                         * Si el usuario a crear es asistente o director juridico
                         */
                        if($nuevoUser->txt_auth_item == ConstantesWeb::ASISTENTE || $nuevoUser->txt_auth_item == ConstantesWeb::CLIENTE){
                            
                            /**
                             * Guardar usuario nuevo
                             */
                            return $this->guardarUsuario($nuevoUser);
                        

                        /**
                         * Si el usuario a crear es colaborador
                         */
                        }else if($nuevoUser->txt_auth_item == ConstantesWeb::COLABORADOR){
                            /**
                             * Guardar usuario nuevo
                             */
                            return $this->guardarUsuarioPadre($nuevoUser, $request);
                        }else{
                            throw new HttpException(400, "El usuario debe de tener un rol para crearlo");
                        }


                    /**
                     * Si el usuario que hace la peticion es ASISTENTE
                     */ 
                    }else if($user->txt_auth_item == ConstantesWeb::ASISTENTE){
                         /**
                         * Si el usuario a crear es director juridico
                         */
                        if($nuevoUser->txt_auth_item == ConstantesWeb::CLIENTE){
                            /**
                             * Guardar usuario nuevo
                             */
                            return $this->guardarUsuario($nuevoUser);
                            

                        /**
                         * Si el usuario a crear es colaborador
                         */
                        }else if($nuevoUser->txt_auth_item == ConstantesWeb::COLABORADOR){
                            /**
                             * Guardar usuario nuevo
                             */
                            return $this->guardarUsuarioPadre($nuevoUser, $request);
                        }else{
                            throw new HttpException(400, "El usuario debe de tener un rol para crearlo");
                        }

                    /**
                     * Si el usuario que hace la peticion es DIRECTOR
                     */ 
                    }else if($user->txt_auth_item == ConstantesWeb::CLIENTE){
                        /**
                         * Si el usuario a crear es colaborador
                         */
                        if($nuevoUser->txt_auth_item == ConstantesWeb::COLABORADOR){
                            /**
                             * Guardar usuario nuevo
                             */
                            return $this->guardarUsuario($nuevoUser);
                        }else{
                            throw new HttpException(400, "No tienes permiso para crear usuarios");
                        }

                    }else if($user->txt_auth_item == ConstantesWeb::COLABORADOR){
                        throw new HttpException(400, "No tienes permiso para crear usuarios");
                    }else{
                        throw new HttpException(400, "El usuario debe de tener un rol para crearlo");
                    }
                }else{
                    throw new HttpException(400, "No tienes permiso para crear usuarios");
                }
            }else{
                throw new HttpException(400, "El usuario no existe");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    private function guardarUsuario($nuevoUser){
        if($usuario = $nuevoUser->signup()){
            $nuevoUser->enviarEmailBienvenida();

            /**
             * Crear relacion usuario padre y usuario hijo
             */
            $relUsuarios = new WrkUsuarioUsuarios();
            $relUsuarios->id_usuario_hijo =$nuevoUser->id_usuario;
            $relUsuarios->id_usuario_padre = $user->id_usuario;
            
            if(!$relUsuarios->save()){
                throw new HttpException(400, "No se guardo relacion entre usuarios");
            }

            $token = new CatTokenSeguridad();
            $fechaActual = date('Y-m-d H:i');
            $token->fch_limite = $fechaActual;
            $token->id_usuario = $nuevoUser->id_usuario;

            if(!$token->save()){
                throw new HttpException(400, "No se pudo guardar el token de seguridad");
            }

            return $nuevoUser;
        }else{
            throw new HttpException(400, "No se pudo guardar al usuario");
        }
    }

    private function guardarUsuarioPadre($nuevoUser, $request){
        if($usuario = $nuevoUser->signup()){
            $nuevoUser->enviarEmailBienvenida();

            /**
             * Crear relacion usuario padre y usuario hijo
             */
            $relUsuarios = new WrkUsuarioUsuarios();
            $relUsuarios->id_usuario_hijo =$nuevoUser->id_usuario;
            $relUsuarios->id_usuario_padre = $request->getBodyParam('usuarioPadre');
            
            if(!$relUsuarios->save()){
                throw new HttpException(400, "No se guardo relacion entre usuarios");
            }

            $token = new CatTokenSeguridad();
            $fechaActual = date('Y-m-d H:i');
            $token->fch_limite = $fechaActual;
            $token->id_usuario = $nuevoUser->id_usuario;

            if(!$token->save()){
                throw new HttpException(400, "No se pudo guardar el token de seguridad");
            }

            return $nuevoUser;
        }else{
            throw new HttpException(400, "No se pudo guardar al usuario");
        }
    }

    /**
     * Editar usuarios
     */
    public function actionEditarUsuario($token = null, $tokenU = null){
        $request = Yii::$app->request;

        /**
         * Validar que venga el parametro en la peticion
         */
        if($token && $tokenU){
            /**
             * Buscar usuario que hace la peticion
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::SUPER_ADMIN])
                ->one();

            if($user){
                /**
                 * Buscar usuario que se va a editar
                 */
                $userNuevo = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$tokenU])->one();
                if($userNuevo){
                    if($userNuevo->load($request->bodyParams, "")){
                        if(!$userNuevo->save()){
                            throw new HttpException(400, "No se guardo el nuevo usuario");
                        }

                        return $userNuevo;
                    }else{
                        throw new HttpException(400, "No hay datos para editar el usuario");
                    }
                }else{
                    throw new HttpException(400, "No existe el usuario que se quiere editar");
                }
            }else{
                throw new HttpException(400, "No tienes permiso para editar usuarios");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    /**
     * Mostrar usuarios
     */
    public function actionUsuarios($token = null, $page = 0){
        $auth = Yii::$app->authManager;

        /**
         * Validar que venga el parametro en la peticion
         */
        if($token){
            /**
             * Buscar usuario que hace la peticion
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ABOGADO])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::ASISTENTE])
                ->orWhere(['txt_token'=>$token, 'id_status'=>2, 'txt_auth_item'=>ConstantesWeb::SUPER_ADMIN])
                ->one();

            if($user){
                /**
                 * Buscar roles hijos de usuario que hace la peticion
                 */
                $hijos = $auth->getChildRoles($user->txt_auth_item);
                ksort($hijos);
                unset($hijos[$user->txt_auth_item]);

                /**
                 * Buscar usuarios hijos de usuario que hace la peticion
                 */
                //$usuarios = ModUsuariosEntUsuarios::find()->where(['in', 'txt_auth_item', array_keys($hijos)])->all();
                  
                $searchModel = new UsuariosSearch();
                $searchModel->txt_auth_item = array_keys($hijos);
                $dataProvider = $searchModel->search(Yii::$app->getRequest()->get(), $page);
                
                return $dataProvider;
            }else{
                throw new HttpException(400, "No tienes permiso para editar usuarios");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    public function actionBloquearUsuario($token = null){
        /**
         * Validar que venga el parametro en la peticion
         */
        if($token){
            /**
             * Buscar usuario que hace la peticion
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token])->one();
            if($user){
                $user->id_status = ModUsuariosEntUsuarios::STATUS_BLOCKED;
                if(!$user->save()){
                    throw new HttpException(400, "No se pudo bloquear al usuario");                    
                }

                return $user;
            }else{
                throw new HttpException(400, "No existe el usuario que se quiere bloquear");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    public function actionActivarUsuario($token=null){
        /**
         * Validar que venga el parametro en la peticion
         */
        if($token){
            /**
             * Buscar usuario que hace la peticion
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token])->one();
            if($user){
                $user->id_status = ModUsuariosEntUsuarios::STATUS_ACTIVED;
                if(!$user->save()){
                    throw new HttpException(400, "No se pudo activar al usuario");                    
                }

                return $user;
            }else{
                throw new HttpException(400, "No existe el usuario que se quiere activar");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }

    public function actionReenviarEmailBienvenida($token = null){
        /**
         * Validar que venga el parametro en la peticion
         */
        if($token){
            /**
             * Buscar usuario para mandar email
             */
            $user = ModUsuariosEntUsuarios::find()->where(['txt_token'=>$token])->one();
            if($user){
                /**
                 * Cambiar password
                 */
                $user->password = $user->randomPassword();
                $user->setPassword($user->password);

                if($user->save()){
                    $user->enviarEmailBienvenida();
                    
                    throw new HttpException(200, "Se ha enviado un email al usuario");
                }else{
                    throw new HttpException(400, "No se pudo mandar el email al usuario");
                }
            }else{
                throw new HttpException(400, "El usuario no existe");
            }
        }else{
            throw new HttpException(400, "Se necesitan datos para validar la petición");
        }
    }
}