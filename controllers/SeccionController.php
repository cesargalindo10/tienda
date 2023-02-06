<?php

namespace app\controllers;

use app\models\Producto;
use app\models\Seccion;
use Yii;
class SeccionController extends \yii\web\Controller
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
        
 
        $secciones = Seccion::find();
        $listaSecciones=$secciones->createCommand()->queryAll();
        $aux1=[];
        for( $i=0; $i<count($listaSecciones);$i++){
            $aux=$listaSecciones[$i];
            $id=$aux['id'];
            $secciones = Producto::find()->where(["seccion_id"=>$id]);
            $respuesta = $secciones->createCommand()->queryAll();
            $key="productos";
            $value=$respuesta;
            $aux[$key]=$value;
            $aux1[$i]=$aux;
            
        }
        return $aux1;
      
  
    }
        


}
