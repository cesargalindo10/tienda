<?php

namespace app\controllers;

use app\models\Categoria;
use app\models\Producto;
use app\models\Seccion;
use Yii;
use yii\data\Pagination;
use Exception;
use yii\db\IntegrityException;

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
                "update" => ["put"],
                "delete" => ["post","delete"],
                "seccion-producto" => ["get"],
                "suma-stock" => ["get"],
                "max-stock"  => ["get"],
                "existencia-stock" => ["get"],
                "asignar-categoria" => ["get"],
                "quitar-categoria" => ["get"]

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
            'defaultPageSize' => 10,
            'totalCount' => $productos->count(),
        ]);
        $listaProducto = $productos
            ->offset($paginacion->offset)
            ->limit($paginacion->limit)
            ->all();
        $paginaActual = $paginacion->getPage() + 1;
        $totalPaginas = $paginacion->getPageCount();
        $resultado = [
            'success' => true,
            'data' => $listaProducto,
            'pagination' => [
                'paginaAnterior' => $paginaActual > 1 ? $paginaActual - 1 : null,
                'paginaActual' => $paginaActual,
                'PaginaSiguiente' => $paginaActual < $totalPaginas ? $paginaActual + 1 : null,
                'totalPaginas' => $totalPaginas,
                'totalCount' => $paginacion->totalCount
            ]
        ];


        return $resultado;
    }
    /**CRUD */
    public function actionCreate()
    {
        $parametros = Yii::$app->getRequest()->getBodyParams();
        $producto = new Producto();
        $producto->load($parametros, '');
        $producto->fecha_creacion = date('Y-m-s H:i:s');
        if ($producto->save()) {
            $resultado = [
                'success' => true,
                'message' => 'el producto de creo de manera exitosa',
                'data' => $producto

            ];
        } else {
            $resultado = [
                'success' => false,
                'message' => 'fallo al crear producto',
                'data' => $producto->errors

            ];
        }
        return $resultado;
    }

    public function actionUpdate($idProducto)
    {
        $parametros = Yii::$app->getRequest()->getBodyParams();
        $producto = Producto::findOne($idProducto);
        if ($producto) {
            $producto->load($parametros, '');
            if ($producto->save()) {
                $resultado = [
                    'success' => true,
                    'message' => 'se actualizo de manera correcta',
                    'data' => $producto
                ];
            } else {
                Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
                $resultado = [
                    'success' => false,
                    'message' => 'falle al actualizar',
                    'data' => $producto->errors
                ];
            }
        }else{
            $resultado = [
                'success' => false,
                'message' => 'Producto no encontrado',
                
            ];
        }

        return $resultado;
    }
    public function actionDelete($idProducto){
        $producto = Producto::findOne($idProducto);
        if($producto){
            try{
                $producto->delete();
                $resultado = [
                    'success' => true,
                    'message' => 'El producto fue eliminado '
                ];

            }catch(IntegrityException $ie){
                Yii::$app->getResponse()->setStatusCode(500);
                $resultado = [
                    'message' =>'El producto esta siendo usado',
                    'code' => $ie->getCode() 
                ];

            }catch(Exception $e){
                    Yii::$app->getResponse()->setStatusCode(500);
                    $resultado = [
                        'message'=>$e->getMessage(),
                        'code' => $e->getCode()
                    ];
            }

        }else{
            $resultado = [
                'success' => false,
                'message' => 'Producto no encontrado',
                
            ];
        }
        return $resultado;
    }


    /*Un servicio que devuelva una sección según su ID con todos los productos
        pertenecientes a la sección*/
    public function actionSeccionProducto($idSeccion)
    {
        $seccion = Seccion::findOne($idSeccion);
        if ($seccion) {
            $productos = $seccion->getProductos()->all();

            $resultado = [
                'success' => true,
                'seccion' => $seccion,
                'productos' => $productos
            ];
        } else {
            // Si no existe lanzar error 404
            throw new \yii\web\NotFoundHttpException('Sección no encontrada.');
        }
        return $resultado;
    }
    /**Un servicio que sume la cantidad de productos de una marca (suma de
            stocks) */
    public function actionSumaStock($idMarca)
    {

        $sumaStock = (new \yii\db\Query())
            ->select(['nombre', 'sum(stock)'])
            ->from('producto')
            ->where(['marca_id' => $idMarca])
            ->groupBy('nombre')
            ->all();
        if ($sumaStock) {


            $resultado = [
                'success' => true,
                'nombre' => '',
                'suma' => '',
                'message' => "Cantidad total de productos ",
                'total' => $sumaStock
            ];
        } else {
            throw new \yii\web\NotFoundHttpException('Marca no encontrada.');
        }
        return $resultado;
    }
    /**Un servicio que devuelva el producto con el mayor stock */
    public function actionMaxStock()
    {

        $maxStock = (new \yii\db\Query())
            ->select(['*'])
            ->from('producto')
            ->where(['stock' => (new \yii\db\Query())->select('max(stock )')->from('producto')])
            ->all();

        $resultado = [
            'success' => true,
            'maxima' => '',
            'message' => "Lista de productos con el mayor stock .",
            'productos' => $maxStock
        ];
        return $resultado;
    }
    /**Un servicio que verifique si un producto tiene stock (stock > 0) mandar id como parametro*/
    public function actionExistenciaStock($idProducto)
    {

        $producto = Producto::findOne($idProducto);
        if($producto){
            $existencia = $producto->stock > 0;
            if($existencia){
                $resultado = [
                    'success' => true,
                    'message' => "El producto cuenta con stock.",
                    'data' => [
                        'existencia'=> $existencia,
                        'stock' => $producto->stock
                    ]
                ];
            }else{
                $resultado = [
                    'success' => false,
                    'message' => "El producto no cuenta con stock.",
                    'data' => [
                        'existencia'=> $existencia,
                        'stock' => $producto->stock
                    ]
                ];
            }
            
        }else{
            $resultado = [
                'success' => false,
                'message' => "El producto fue encontrado",

            ];
        }
       
        return $resultado;
    }
    public function actionAsignarCategoria($producto_id, $categoria_id)
    {

        $producto = Producto::findOne($producto_id);
        if ($producto) {

            $categoria = Categoria::findOne($categoria_id);
            if ($categoria) {

                if (!$producto->getCategorias()->where("id={$categoria_id}")->one()) {
                    // Si no existe el enlace entre el producto y la categoría

                    try {
                        // Enlaza el producto con la categoría
                        // Usa la relación muchos a muchos del modelo Producto linea 108
                        $producto->link('categorias', $categoria);
                        $resultado = [
                            'success' => true,
                            'message' => 'Se asigno la categoría al producto correctamente.'
                        ];
                    } catch (Exception $e) {
                        // Establece el código de estado como 500 para error de servidor
                        Yii::$app->getResponse()->setStatusCode(500);
                        $resultado = [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                        ];
                    }
                } else {
                    // Establece el código de estado como 422 para Existing link.
                    Yii::$app->getResponse()->setStatusCode(422, 'Existing link.');
                    // Si el enlace entre producto y categoría existe
                    $resultado = [
                        'success' => false,
                        'message' => 'El producto ya posee la categoría.'
                    ];
                }
            } else {
                // Si no existe lanzar error 404
                throw new \yii\web\NotFoundHttpException('Categoría no encontrada.');
            }
        } else {
            // Si no existe lanzar error 404
            throw new \yii\web\NotFoundHttpException('Producto no encontrado.');
        }
        return $resultado;
    }
    public function actionQuitarCategoria($producto_id, $categoria_id)
    {

        $producto = Producto::findOne($producto_id);
        if ($producto) {

            $categoria = Categoria::findOne($categoria_id);
            if ($categoria) {

                if ($producto->getCategorias()->where("id={$categoria_id}")->one()) {

                    try {

                        $producto->unlink('categorias', $categoria, true);
                        $resultado = [
                            'success' => true,
                            'message' => 'Se quito la ategoria.'
                        ];
                    } catch (Exception $e) {

                        Yii::$app->getResponse()->setStatusCode(500);
                        $resultado = [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                        ];
                    }
                } else {
                    Yii::$app->getResponse()->setStatusCode(422, 'Existing link.');
                    $resultado = [
                        'success' => false,
                        'message' => 'El porducto no posee la categoria.'
                    ];
                }
            } else {

                throw new \yii\web\NotFoundHttpException('Categoría no encontrada.');
            }
        } else {

            throw new \yii\web\NotFoundHttpException('Producto no encontrado.');
        }
        return $resultado;
    }
}
