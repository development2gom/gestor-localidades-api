<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [ 
                    [ 
                            'username',
                            'password' 
                    ],
                    'required',
                    'on' => 'login',
                    'message'=>'Campo requerido' 
            ],
            // username es requerido para recuperar la contraseña
            [ 
                    [ 
                            'username' 
                    ],
                    'required',
                    'on' => 'recovery',
                    'message'=>'Campo requerido' 
            ],
            [ 
                    [ 
                            'username' 
                    ],
                    'validateUsuario',
                    'on' => 'recovery',
            ],
            [ 
                    [ 
                            'username',
                            'password' 
                    ],
                    'trim' 
            ],
            ['username','email', 'message'=>'Debe agregar un email válido'],
            ['username','validateBlocked'],
            
            // rememberMe must be a boolean value
            [ 
                    'rememberMe',
                    'boolean' 
            ],
            // password is validated by validatePassword()
            [ 
                    'password',
                    'validatePassword',
                    'on' => 'login' 
            ] 
    ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
	 * Validates the password.
	 * This method serves as the inline validation for password.
	 *
	 * @param string $attribute
	 *        	the attribute currently being validated
	 * @param array $params
	 *        	the additional name-value pairs given in the rule
	 */
	public function validateBlocked($attribute, $params) {
		if (! $this->hasErrors ()) {
			$user = $this->getUser ();
			
			if ($user && $user->id_status == ModUsuariosEntUsuarios::STATUS_BLOCKED) {
				$this->addError ( $attribute, 'El usuario ha sido bloqueado.' );
			}
		}
	}
	
	/**
	 * Valida que el usuario exista
	 */
	public function validateUsuario($attribute, $params) {
		$this->userEncontrado = $this->getUser ();
		
		if (empty($this->userEncontrado)) {
			$this->addError ( $attribute, 'No existe una cuenta asociada al corro electronico ingresado.' );
		}
	}

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = ModUsuariosEntUsuarios::findByEmail($this->username);
        }

        return $this->_user;
    }
}
