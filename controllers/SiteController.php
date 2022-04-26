<?php

namespace app\controllers;

use app\components\AuthHandler;
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
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    public function onAuthSuccess($client)
    {
        (new AuthHandler($client))->handle();
    }



    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
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
            $model::saveUserCredentials($model->username,$model->password,$model->authMethod);

             if ($model->authMethod == $model::EMAIL_AUTH) {
                    $model::sendEmailCode();
            }
               if ($model->authMethod == $model::GOOGLE_AUTH) {
                   return $this->redirect(['google-auth']);
               }
        }
        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionGoogleAuth()
    {
        $googleAuth = Yii::createObject(\Sonata\GoogleAuthenticator\GoogleAuthenticator::class);

        $model = new DynamicModel(['code']);
        $model->addRule(['code'], 'safe')->validate();

        $model->addRule('code', function ($attribute, $params) use ($model,$googleAuth) {
            if (!$googleAuth->checkCode(\app\models\GoogleAuthenticator::SECRET,$model->code)) {
                $model->addError($attribute, 'Incorrect result! '.$googleAuth->getCode(\app\models\GoogleAuthenticator::SECRET));
            }
        });
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            (new LoginForm())->makeLogin('about');
        }
        return $this->render('google_varification',[
            'model'=>$model,
            'googleAuth'=>$googleAuth,
        ]);
    }

    public function actionVarification()
    {
        LoginForm::checkSessionLife();
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
            (new LoginForm())->makeLogin('about');
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
