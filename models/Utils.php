<?php

namespace app\models;

use Yii;

class Utils {

	public static function changeFormatDate($string) {
		$date = date_create ( $string );
		return date_format ( $date, "m-d-Y" );
	}
	
	public static function changeFormatDateNormal($string) {
		$date = date_create ( $string );
		return date_format ( $date, "d-m-Y" );
    }
    
    public static function changeFormatDateInput($string) {
		$date = \DateTime::createFromFormat('d-m-Y',$string);
		return date_format ( $date, "Y-m-d H:i:s" );
    }
	
	/**
	 * Obtenemos la fecha actual para almacenarla
	 *
	 * @return string
	 */
	public static function getFechaActual() {
		
		// Inicializamos la fecha y hora actual
		$fecha = date ( 'Y-m-d H:i:s', time () );
		return $fecha;
	}
	
	/**
	 * Genera un token para guardarlo en la base de datos
	 *
	 * @param string $pre        	
	 * @return string
	 */
	public static function generateToken($pre = 'usr') {
		$token = $pre . md5 ( uniqid ( $pre ) ) . uniqid ();
		return $token;
	}
	
	/**
	 * Obtiene fecha de vencimiento para una fecha
	 * @param unknown $fechaActualTimestamp
	 */
	public static function getFechaVencimiento($fechaActualTimestamp) {
		$date = date ( 'Y-m-d H:i:s', strtotime ( "+".Yii::$app->params ['usuarios'] ['recueperarPass'] ['diasValidos']." day", strtotime ( $fechaActualTimestamp ) ) );
	
		return $date;
	}
	
	/**
	 * Envia el correo electronico para la activiación de la cuenta
	 *
	 * @param array $parametrosEmail
	 * @return boolean        	
	 */
	public function sendEmailActivacion($email,$parametrosEmail) {
		
		// Envia el correo electronico
		return $this->sendEmail ( '@app/mail/activarCuenta', '@app/mail/layouts/text', Yii::$app->params ['usuarios'] ['email'] ['emailActivacion'],$email, Yii::$app->params ['usuarios'] ['email'] ['subjectActivacion'], $parametrosEmail );
	}

	public function sendEmailAsignacion($email,$parametrosEmail) {
		
		// Envia el correo electronico
		return $this->sendEmail ( '@app/mail/asignacion', '@app/mail/layouts/text', Yii::$app->params ['usuarios'] ['email'] ['emailActivacion'],$email, "Asignacion de localidad", $parametrosEmail );
	}

	public function sendEmailAsignacionTarea($email,$parametrosEmail) {
		
		// Envia el correo electronico
		return $this->sendEmail ( '@app/mail/asignacionTarea', '@app/mail/layouts/text', Yii::$app->params ['usuarios'] ['email'] ['emailActivacion'],$email, "Asignacion de tarea", $parametrosEmail );
	}

	public function sendEmailNotificacionesTareas($email,$parametrosEmail) {
		
		// Envia el correo electronico
		return $this->sendEmail ( '@app/mail/notificacionTarea', '@app/mail/layouts/text', Yii::$app->params ['usuarios'] ['email'] ['emailActivacion'],$email, "Notificacion", $parametrosEmail );
	}

	public function sendEmailNotificacionesTareasDirector($email,$parametrosEmail) {
		
		// Envia el correo electronico
		return $this->sendEmail ( '@app/mail/notificacionTareaDirector', '@app/mail/layouts/text', Yii::$app->params ['usuarios'] ['email'] ['emailActivacion'],$email, "Notificacion", $parametrosEmail );
	}

	public function sendEmailCargaTareas($email,$parametrosEmail) {
		
		// Envia el correo electronico
		return $this->sendEmail ( '@app/mail/cargaDropbox', '@app/mail/layouts/text', Yii::$app->params ['usuarios'] ['email'] ['emailActivacion'],$email, "Tarea", $parametrosEmail );
	}

	public function sendEmailCambiarPass($email,$parametrosEmail) {
		
		// Envia el correo electronico
		return $this->sendEmail ( '@app/mail/cambiarPass', '@app/mail/layouts/text', Yii::$app->params ['usuarios'] ['email'] ['emailActivacion'],$email, "Cambiar contraseña", $parametrosEmail );
	}
	
	/**
	 * Envia el correo electronico para recuperar el correo electronico
	 *
	 * @param array $parametrosEmail
	 * @return boolean
	 */
	public  function sendEmailRecuperarPassword($email,$parametrosEmail) {
		// Envia el correo electronico
		return $this->sendEmail ( '@app/mail/recuperarPassword', '@app/mail/layouts/text', Yii::$app->params ['usuarios'] ['email'] ['emailRecuperarPass'], $email, Yii::$app->params ['usuarios'] ['email'] ['subjectRecuperarPass'], $parametrosEmail );
	}
	
	/**
	 * Envia mensaje de correo electronico
	 *
	 * @param string $templateHtml        	
	 * @param string $templateText        	
	 * @param string $from        	
	 * @param string $to        	
	 * @param string $subject        	
	 * @param array $params        	
	 * @return boolean
	 */
	private function sendEmail($templateHtml, $templateText, $from, $to, $subject, $params) {
		return Yii::$app->mailer->compose ( [
				// 'html' => '@app/mail/layouts/example',
				// 'text' => '@app/mail/layouts/text'
				'html' => $templateHtml,
				//'text' => $templateText 
		], $params )->setFrom ( $from )->setTo ( $to )->setSubject ( $subject )->send ();
	}
}