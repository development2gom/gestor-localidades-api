<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;
use app\models\AuthItem;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "mod_usuarios_ent_usuarios".
 *
 * @property string $id_usuario
 * @property string $txt_auth_item
 * @property string $txt_token
 * @property string $txt_imagen
 * @property string $txt_username
 * @property string $txt_apellido_paterno
 * @property string $txt_apellido_materno
 * @property string $txt_auth_key
 * @property string $txt_password_hash
 * @property string $txt_password_reset_token
 * @property string $txt_email
 * @property string $fch_creacion
 * @property string $fch_actualizacion
 * @property string $id_status
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItem[] $itemNames
 * @property CatPorcentajeRentaAbogados $catPorcentajeRentaAbogados
 * @property EntLocalidades[] $entLocalidades
 * @property EntLocalidadesArchivadas[] $entLocalidadesArchivadas
 * @property ModUsuariosEntSesiones[] $modUsuariosEntSesiones
 * @property ModUsuariosCatStatusUsuarios $status
 * @property AuthItem $txtAuthItem
 * @property WrkComentariosTareas[] $wrkComentariosTareas
 * @property WrkDocumentos[] $wrkDocumentos
 * @property WrkHistorialTareas[] $wrkHistorialTareas
 * @property WrkTareas[] $wrkTareas
 * @property WrkTareasArchivadas[] $wrkTareasArchivadas
 * @property WrkUsuariosLocalidadesArchivadas[] $wrkUsuariosLocalidadesArchivadas
 * @property EntLocalidadesArchivadas[] $localidads
 * @property WrkUsuariosTareas[] $wrkUsuariosTareas
 * @property WrkTareas[] $tareas
 * @property WrkUsuariosTareasArchivadas[] $wrkUsuariosTareasArchivadas
 * @property WrkTareasArchivadas[] $tareas0
 */
class ModUsuariosEntUsuarios extends \yii\db\ActiveRecord implements IdentityInterface
{
    const STATUS_PENDIENTED = 1;
	const STATUS_ACTIVED = 2;
	const STATUS_BLOCKED = 3;
    public $password;
	public $repeatPassword;
	public $repeat;
	public $repeatEmail;
	public $image;
    public $usuarioPadre;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mod_usuarios_ent_usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['txt_auth_item', 'txt_username', 'txt_auth_key', 'txt_password_hash', 'txt_email'], 'required'],
            [['fch_creacion', 'fch_actualizacion'], 'safe'],
            [['id_status'], 'integer'],
            [['txt_auth_item'], 'string', 'max' => 64],
            [['txt_token'], 'string', 'max' => 100],
            [['txt_imagen'], 'string', 'max' => 200],
            [['txt_username', 'txt_password_hash', 'txt_password_reset_token', 'txt_email'], 'string', 'max' => 255],
            [['txt_apellido_paterno', 'txt_apellido_materno'], 'string', 'max' => 30],
            [['txt_auth_key'], 'string', 'max' => 32],
            [['txt_token'], 'unique'],
            [['txt_password_reset_token'], 'unique'],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => ModUsuariosCatStatusUsuarios::className(), 'targetAttribute' => ['id_status' => 'id_status']],
            [['txt_auth_item'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['txt_auth_item' => 'name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_usuario' => 'Id Usuario',
            'txt_auth_item' => 'Txt Auth Item',
            'txt_token' => 'Txt Token',
            'txt_imagen' => 'Txt Imagen',
            'txt_username' => 'Txt Username',
            'txt_apellido_paterno' => 'Txt Apellido Paterno',
            'txt_apellido_materno' => 'Txt Apellido Materno',
            'txt_auth_key' => 'Txt Auth Key',
            'txt_password_hash' => 'Txt Password Hash',
            'txt_password_reset_token' => 'Txt Password Reset Token',
            'txt_email' => 'Txt Email',
            'fch_creacion' => 'Fch Creacion',
            'fch_actualizacion' => 'Fch Actualizacion',
            'id_status' => 'Id Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemNames()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'item_name'])->viaTable('auth_assignment', ['user_id' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatPorcentajeRentaAbogados()
    {
        return $this->hasOne(CatPorcentajeRentaAbogados::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntLocalidades()
    {
        return $this->hasMany(EntLocalidades::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntLocalidadesArchivadas()
    {
        return $this->hasMany(EntLocalidadesArchivadas::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModUsuariosEntSesiones()
    {
        return $this->hasMany(ModUsuariosEntSesiones::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(ModUsuariosCatStatusUsuarios::className(), ['id_status' => 'id_status']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTxtAuthItem()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'txt_auth_item']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkComentariosTareas()
    {
        return $this->hasMany(WrkComentariosTareas::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkDocumentos()
    {
        return $this->hasMany(WrkDocumentos::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkHistorialTareas()
    {
        return $this->hasMany(WrkHistorialTareas::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkTareas()
    {
        return $this->hasMany(WrkTareas::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkTareasArchivadas()
    {
        return $this->hasMany(WrkTareasArchivadas::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkUsuariosLocalidadesArchivadas()
    {
        return $this->hasMany(WrkUsuariosLocalidadesArchivadas::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocalidads()
    {
        return $this->hasMany(EntLocalidadesArchivadas::className(), ['id_localidad' => 'id_localidad'])->viaTable('wrk_usuarios_localidades_archivadas', ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkUsuariosTareas()
    {
        return $this->hasMany(WrkUsuariosTareas::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTareas()
    {
        return $this->hasMany(WrkTareas::className(), ['id_tarea' => 'id_tarea'])->viaTable('wrk_usuarios_tareas', ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWrkUsuariosTareasArchivadas()
    {
        return $this->hasMany(WrkUsuariosTareasArchivadas::className(), ['id_usuario' => 'id_usuario']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTareas0()
    {
        return $this->hasMany(WrkTareasArchivadas::className(), ['id_tarea' => 'id_tarea'])->viaTable('wrk_usuarios_tareas_archivadas', ['id_usuario' => 'id_usuario']);
    }

    public function getUsuariosHijos(){
        $relHijos = WrkUsuarioUsuarios::find()->where(['id_usuario_padre'=>$this->id_usuario])->all();
        
        if($relHijos){
            $arrayId = [];
            $i = 0;

            foreach($relHijos as $id){
                $arrayId[$i] = $id->id_usuario_hijo;
                $i++;
            }

            $hijos = ModUsuariosEntUsuarios::find()->where(['in', 'id_usuario', $arrayId])->all();
            if($hijos){
                return $hijos;
            }
        }

        return;
    }

    public function fields(){
        $fields = parent::fields();
        
        $fields[] = 'usuariosHijos';
        $fields[] = 'status';

        /**
         * Se quitan los siguientes campos cuando se regresan en el json de respuesta
         */
        unset($fields['txt_auth_key'], $fields['txt_password_hash'], $fields['txt_password_reset_token'], $fields['txt_estatus'], $fields['txt_apellido_materno']);

        return $fields;
    }

    /**
	 * Guarda al usuario en la base de datos
	 *
	 * @return EntUsuarios
	 */
	public function signup($isFacebook=false) {

		$this->image = UploadedFile::getInstance($this, 'image');

		$this->txt_token = Utils::generateToken ( 'usr' );
		
		if($this->image){
			$this->txt_imagen = $this->txt_token.".".$this->image->extension;
			if(!$this->upload()){
				return false;
			}
		}

		$this->setPassword ( $this->password );
		$this->generateAuthKey ();
		$this->fch_creacion = Utils::getFechaActual ();
		
		// Si esta activada la opcion de mandar correo de activación el usuario estara en status pendiente
		//if (Yii::$app->params ['modUsuarios'] ['mandarCorreoActivacion'] && !$isFacebook) {
			$this->id_status = self::STATUS_PENDIENTED;
		//} else {
			$this->id_status = self::STATUS_ACTIVED;
        //}
        
        if (! $this->validate ()) {
			return false;
		}

		if($this->save()){

			$this->guardarRoleUsuario();

			return true;
		}else{
			return false;
		}
		
    }
    
    public function upload()
    {
		$path = "profiles/".$this->txt_token;
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
       
    	if($this->image->saveAs($path."/".$this->txt_imagen)){
			return true;
		}else{
			return false;	
		}
            
    }

    public function guardarRoleUsuario(){
		
		$auth = \Yii::$app->authManager;
		$authorRole = $auth->getRole($this->txt_auth_item);
		$auth->assign($authorRole, $this->getId());
	}

	public function getRoleDescription(){

		return $this->txtAuthItem->description;
    }
    
    public function enviarEmailBienvenida(){
		
		// Parametros para el email
		$params ['url'] = Yii::$app->urlManager->createAbsoluteUrl ( [ 
			'ingresar/' . $this->txt_token 
		] );
		$params ['user'] = $this->nombreCompleto;
		$params ['usuario'] = $this->txt_email;
		$params ['password'] = $this->password;
		
        $email = new Email();
        $email->emailHtml = "@app/modules/ModUsuarios/email/bienvenida";
        $email->emailText = "@app/modules/ModUsuarios/email/layouts/text";
        $email->to = $this->txt_email;
        $email->subject = "Bienvenido";
        $email->params =$params ;
        
        // Envio de correo electronico
        $email->sendEmail();
        return true;
    }
    
    public function randomPassword() {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];

            	
        }
        return implode($pass);
    }

    /**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password        	
	 */
	public function setPassword($password) {
		$this->txt_password_hash = Yii::$app->security->generatePasswordHash ( $password );
	}
	
	/**
	 * Generates "remember me" authentication key
	 */
	public function generateAuthKey() {
		$this->txt_auth_key = Yii::$app->security->generateRandomString ();
    }
    
    /**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->getPrimaryKey ();
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['txt_token' => $token]);
    }

    /**
	 * INCLUDE USER LOGIN VALIDATION FUNCTIONS*
	 */
	/**
	 * @inheritdoc
	 */
	public static function findIdentity($id) {
		return static::findOne ( $id );
    }
    
    /**
	 * @inheritdoc
	 */
	public function getAuthKey() {
		return $this->txt_auth_key;
    }
    
    /**
	 * @inheritdoc
	 */
	public function validateAuthKey($authKey) {
		return $this->getAuthKey () === $authKey;
    }
    
    public static function loginByAccessToken($token, $type = null)
    {
        return static::findOne(['txt_token' => $token]);
    }

    /**
	 * Finds user by email
	 *
	 * @param string $email        	
	 * @return EntUsuarios|null
	 */
	public static function findByEmail($username) {
		return static::findOne ( [ 
				'txt_email' => $username,
				//'id_status' => self::STATUS_ACTIVED
		] );
    }
    
    /**
	 * Validates password
	 *
	 * @param string $password
	 *        	password to validate
	 * @return boolean if password provided is valid for current user
	 */
	public function validatePassword($password) {
		return Yii::$app->security->validatePassword ( $password, $this->txt_password_hash );
	}
}
