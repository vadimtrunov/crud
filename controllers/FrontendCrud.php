<?php

namespace tv88dn\crud\controllers;

use tv88dn\crud\CrudAssets;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Inflector;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class Crud
 * @package frontend\traits
 *
 * @property Controller $this
 * @property string $moduleName
 * @property string $assetName
 */
trait FrontendCrud
{
    use BaseCrud;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['edit', 'create'],
                'rules' => [
                    [
                        'actions' => ['edit', 'create'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

        ];
    }

    protected function getSlugField()
    {
        return Inflector::slug($this->model->title);
    }

    public function slug()
    {
        $slug = Yii::$app->request->get(strtolower($this->getModuleName()).'Tr');
        if(!$slug){
            $this->redirect(\yii\helpers\Url::toRoute(['/'.strtolower($this->getModuleName()).'/view',strtolower($this->getModuleName()).'Tr' => $this->slugField,'id'=>$this->model->id]));
        }
        if($this->slugField != strtolower($slug))
        {
            throw new NotFoundHttpException;
        }
    }

    public function render($view, $params = [])
    {
        CrudAssets::register($this->view);
        if(class_exists($this->getAssetName())){
            call_user_func_array([$this->getAssetName(), 'register'], [$this->view]);
        }

        return parent::render($view, $params);
    }

    public function actionCreate()
    {
        return \Yii::$app->request->isAjax ? $this->renderAjax('create', ['model'=>$this->model]) : $this->render('create', ['model'=>$this->model]);
    }

    public function actionIndex()
    {
        $this->search();
        if(\Yii::$app->request->isAjax)
        {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return ['items'=>$this->jsonItems, 'count'=>$this->count];
        }
        else{
            return $this->render('index', [
                'items'=>$this->items,
                'count'=>$this->count,
            ]);
        }
    }

    public function actionEdit($id)
    {
        $model = $this->model;
        if($model->canEdit()){
            return $this->render('edit', ['model'=>$model]);
        }
        throw new BadRequestHttpException;
    }

    public function actionView($id)
    {
        $this->slug();
        return $this->render('view', ['model'=>$this->model]);
    }

    public function actionWorkpiece()
    {
        $modelName = $this->getModelName();
        return $this->renderPartial('_item', ['model'=>new $modelName(['scenario'=>'nullObject'])]);
    }

    protected function afterDeleteAction()
    {
        return $this->redirect(Url::toRoute([strtolower($this->getModuleName()) . '/index', 'id' => $model->id]));
    }

    public function actionDelete($id)
    {
        $model = call_user_func([$this->getModelName(), 'find'])
            ->where(['id' => $id])->one();
        if($model && $model->user_id == \Yii::$app->user->id){
            if (\Yii::$app->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => $model->delete() ? 'success' : 'error'];
            } else if($model->delete()) {
                return $this->redirect(Url::toRoute([strtolower($this->getModuleName()) . '/index', 'id' => $model->id]));
            }
        }
        throw new BadRequestHttpException;
    }

}