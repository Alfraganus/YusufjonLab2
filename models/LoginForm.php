<?php

namespace app\models;

use Carbon\Carbon;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $authMethod;
    CONST EMAIL_AUTH = 0;
    CONST GOOGLE_AUTH = 1;

    private $_user = false;
    private $session;

    /**
     * @return array the validation rules.
     */


    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password','authMethod'], 'required'],
            [['authMethod'], 'integer'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }
    public static function saveUserCredentials($username,$password,$authMethod) : void
    {
        $session = Yii::$app->session;
        $session['credentials'] = [
            'username' => $username,
            'password' => $password,
            'authMethod' => $authMethod,
            'lifetime' => 1800,
        ];

    }
    public function makeLogin($redirectUrl)
    {
        $session = Yii::$app->session;
        $userCredentials = $session['credentials'];
        $loginModel = new LoginForm();
        $loginModel->username = $userCredentials['username'];
        $loginModel->password = $userCredentials['password'];
        $loginModel->authMethod = $userCredentials['authMethod'];
        if ($loginModel->login()) {
            $session->remove('credentials');
            $session->remove('randomNum');
            return Yii::$app->controller->redirect([$redirectUrl]);
        } else {
           var_dump($loginModel->errors);
        }
    }

    public static function sendEmailCode()
    {
        $session = Yii::$app->session;
        $session['randomNum'] = [
            'number' => mt_rand(2000, 9000),
            'sessionEndTime' => Carbon::now()->addMinutes(3),
        ];

        Yii::$app->mailer->compose()
            ->setTo('d5b027cda8-c42927@inbox.mailtrap.io')
            ->setFrom(['noreply@uzguitarist.com' => 'Yusufjon'])
            ->setSubject('Access code for Yusufjon\'s project')
            ->setTextBody('Welcome to Yusufjon Lab project, this is your 4 digit number to access the project: ' . $session['randomNum']['number'])
            ->send();

        return Yii::$app->controller->redirect('varification');
    }

    public static function checkSessionLife()
    {
        if(date(Yii::$app->session['randomNum']['sessionEndTime']) < Carbon::now()) {
            Yii::$app->session->remove('randomNum');
            Yii::$app->session->remove('credentials');
            return Yii::$app->controller->redirect('login');
        }
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
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
