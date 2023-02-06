<?php

namespace app\controllers;

use Yii;

class MarcaController extends \yii\web\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors["verbs"] = [
            "class" => \yii\filters\VerbFilter::class,
            "actions" => [
                "index" => ["get"],
                "create" => ["post"],
                "update" =>["put"],
            ]
        ];
        return $behaviors;
    }
    public function beforeAction($action)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
    public function actionIndex()
    {
        return $this->render('index');
    }

}
