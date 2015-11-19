<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 12.11.15
 * Time: 16:44
 */

namespace tv88dn\crud\controllers;

use common\models\ActiveRecord;
use tv88dn\crud\models\SearchInterface;
use tv88dn\reflection\BaseReflection;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

trait BaseCrud
{
    use BaseReflection;

    protected $count;
    protected $_model, $_searchModel;

    /**
     * Базовый метод для получения модели
     *
     *
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected function getModel()
    {
        if(isset($this->_model)){
            return $this->_model;
        }

        $modelName = $this->getModelName();

        if(\Yii::$app->request->get('id'))
        {
            $model = $this->findOneQuery(\Yii::$app->request->get('id'))->one();
            if(!$model){
                throw new NotFoundHttpException;
            }
        }
        else{
            $model = new $modelName;
        }
        if($model->load(\Yii::$app->request->post()))
        {
            if($model->save()){
                return $this->afterSaveAction($model);
            }
        }

        return $this->_model = $model;
    }

    protected function search()
    {
        return $this->searchModel->load(\Yii::$app->request->get());
    }

    protected function afterSaveAction($model)
    {
        return $this->redirect(Url::toRoute([strtolower($this->getModuleName()) . '/view', 'id' => $model->id]));
    }

    protected function getJsonItems($items = null)
    {
        $result = [];
        if($items === null){
            $items = $this->items;
        }
        if(method_exists($this->getHelperName(), 'getJsonItem')) {
            foreach($items as $model)
            {
                $result[] = call_user_func_array([$this->getHelperName(), 'getJsonItem'], [$model]);

            }
        } else {
            throw new Exception('Your helper must implements vendor\tv88dn\crud\helpers\Interface');
        }
        return $result;
    }

    protected function getItems()
    {
        $page = \Yii::$app->request->get('page');
        $query = $this->findAllQuery();
        $this->count = $query->count();
        return $query->offset(($page-1)*self::$pageSize)->limit(self::$pageSize)->all();
    }

    protected function getSearchModel()
    {
        if($this->_searchModel){
            return $this->_searchModel;
        }
        if(class_exists($this->getSearchModelName())){
            $modelName = $this->getSearchModelName();
        } else {
            $modelName = $this->getModelName();
        }
        return $this->_searchModel = new $modelName(['scenario' => 'search']);
    }

    protected function findOneQuery($id){
        $model = $this->getSearchModel();
        if($model instanceof SearchInterface){
            return $model->searchOne($id);
        } else if ($model instanceof ActiveRecord) {
           return $model->find()->where(['id'=>$id]);
        } else {
            throw new Exception('Class is not suitable');
        }
    }

    protected function findAllQuery()
    {
        $model = $this->getSearchModel();
        if($model instanceof SearchInterface){
            return $model->search();
        } elseif($model instanceof ActiveRecord) {
            return $model->find();
        } else {
            throw new Exception('Class is not suitable');
        }
    }

}