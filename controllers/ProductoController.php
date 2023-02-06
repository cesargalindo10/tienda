<?php

namespace app\controllers;

use app\models\Producto;
use Yii;
use yii\data\Pagination;

class ProductoController extends \yii\web\Controller
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
        $productos = Producto::find();
        $paginacion = new Pagination([
            'defaultPageSize' => 7,
            'totalCount' => $productos->count(),
        ]);
        $listaProducto = $productos
            ->offset($paginacion->offset)
            ->limit($paginacion->limit)
            ->all();
        $resultado = [
            "success" => true,
            "message" => "Laa acion se realizo correctamente",
            "data" => $listaProducto
        ];
        return $resultado;
    }

}
