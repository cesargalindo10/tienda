<?php

namespace app\controllers;

use app\models\User;
use Exception;
use Yii;
class UserController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        //…    

        // add Bearer authentication filter     	
        $behaviors["verbs"] = [
            "class" => \yii\filters\VerbFilter::class,
            "actions" => [
                "login" => ["post"],
                
            ]
        ];

        //…
        return $behaviors;
    }

    public function beforeAction($action)
    {
        if (Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            Yii::$app->getResponse()->getHeaders()->set('Allow', 'POST GET PUT');
            Yii::$app->end();
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
    public function actionCreateUser()
    {
        $parametros = Yii::$app->getRequest()->getBodyParams();
        try{
            $usuario = new User();
            $usuario->nombres = $parametros["nombres"];
            $usuario->username = $parametros["username"];
            $usuario->password = $parametros["password"];
            $usuario->password_hash = Yii::$app->getSecurity()->generatePasswordHash($parametros["password"]);
            $usuario->access_token = Yii::$app->security->generateRandomString();
            //$usuario->auth_key = $parametros["auth_key"];

            if($usuario->save()){
                Yii::$app->getResponse()->getStatusCode(201);
                $resultado = [
                    'success'=>true,
                    'message'=>'se registro de manera correcta',
                    'usuario'=>$usuario
                ];
            }else{
                Yii::$app->getResponse()->setStatusCode(422,'Data Validation Failed.');
                $resultado = [
                    'success' => false,
                    'message' => 'Parametros incorrectos',
                    'usuario' => $usuario->errors,
                ];
            }


        }catch( Exception $e){
            Yii::$app->getResponse()->setStatusCode(500);
            $resultado = [
                'success' => false,
                'message' => 'ocurrio un error al registrar usuario',
                'errors' => $e->getMessage(),
            ];
        }
     
        return $resultado;

    }
    public function actionLogin()
    {  
        $params = Yii::$app->getRequest()->getBodyParams();
        try{
            $username = isset($params['username']) ? $params['username'] : null;
            $password = isset($params['password']) ? $params['password'] : null;
            
            $user = User::findOne(['username' => $username]);
            if( $user ){
                if(Yii::$app->security->validatePassword($password, $user->password_hash)){
                    $auth = Yii::$app->authManager;
                   // $permissions = $auth->getPermissionsByUser($user->id);
                    $response = [
                        "success" => true,
                        "message" => "Inicio de sesión exitoso",
                        "accessToken" => $user->access_token,
                       // "permissions" => $permissions
                    ];
                    return $response;
                }
            }
            Yii::$app->getResponse()->setStatusCode(400);
            $response = [
                "succes" => false,
                "message" => "Usuario y/o Contraseña incorrecto."
            ];

        }catch(Exception $e){
            Yii::$app->getResponse()->setStatusCode(500);    
            $response = [             
                'success' => false,             	
            'message' => $e->getMessage(),             	
            'code' => $e->getCode(),         	
            ];     
                     
        }
        return $response;
    }
    public function actionIndex()
    {
        return $this->render('index');
    }
}
