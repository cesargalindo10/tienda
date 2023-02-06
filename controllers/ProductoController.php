<?php

namespace app\controllers;

use app\models\Categoria;
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

    public function sumaStock($idMarca){
        $sumaStock = (new \yii\db\Query())
            ->select(['sum(stock)'])
            ->from('producto')
            ->where(['marca_id' => $idMarca])
            ->limit(10)
            ->all();
            return $sumaStock;
    }
    public function maxStock(){

        $maxStock= (new \yii\db\Query())
        ->select(['nombre'])
        ->from('producto')
        ->where(['stock' => (new \yii\db\Query())->select('max(stock )')->from('producto')])
        //->limit(10)
        ->all();
    
    return $maxStock;
    }
    public function stock(){

        $existencia = Producto::find()->where('stock>30')->all();      

        return $existencia;       

    }
  
    public function actionIndex()
    {
        /*$productos = Producto::find();
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
        return $productos;*/
     
        $idCategoria = Categoria::findOne('1');
        return $idCategoria;
          
    }
    

}
