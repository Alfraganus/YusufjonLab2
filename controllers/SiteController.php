<?php

namespace app\controllers;

use Carbon\Carbon;
use Yii;
use yii\base\DynamicModel;
use yii\components\googleAuthenticator;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout','about'],
                'rules' => [
                    [
                        'actions' => ['logout','about'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $auth = Yii::createObject(\app\models\GoogleAuthenticator::class)->getQRCodeGoogleUrl('Blog', $secret);
        var_dump($auth);
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        if(!is_null(Yii::$app->session['randomNum'])) {
            return $this->redirect('varification');
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model::saveUserCredentials($model->username,$model->password);
             if ($model->authMethod == $model::EMAIL_AUTH) {
                    $model::sendEmailCode();
                 return $this->redirect(['varification']);
            }
        }
        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionVarification()
    {
        LoginForm::checkSessionLife();
        $session = Yii::$app->session;

        if(is_null(Yii::$app->session['randomNum'])) {
            return $this->redirect('login');
        }

        $model = new DynamicModel(['code']);
        $model->addRule(['code'], 'integer',['message'=>'Latters are not allowed']);
        $model->addRule(['code'], 'required')->validate();
        $model->addRule('code', function ($attribute, $params) use ($model) {
            if ($model->code != Yii::$app->session['randomNum']['number']) {
                $model->addError($attribute, 'Incorrect digits provided');
            }
        });

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $userCredentials = $session['credentials'];
            $loginModel = new LoginForm();
            $loginModel->username = $userCredentials['username'];
            $loginModel->password = $userCredentials['password'];
            if ($loginModel->login()) {
                $session->remove('credentials');
                $session->remove('randomNum');
                return $this->redirect(['about']);
            } else {
                $model->addError('code', implode(' | ',$loginModel->errors));
            }
        }

        return $this->render('varification',[
            'model'=>$model
        ]);
    }




    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
