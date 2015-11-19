<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 12.11.15
 * Time: 16:49
 */

namespace tv88dn\crud\controllers;

use common\interfaces\IStatus;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Response;

trait BeckendCrud
{
    use \common\traits\Crud;

    protected $searchModel;

    protected $dateFormat = 'php:m-d-Y';
    protected $dateTimeFormat = 'yyyy-m-dd hh:ii:00';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionSetStatus($id, $status = null, $visibility = null)
    {
        if (Yii::$app->request->isAjax) {
            $modelName = $this->getModelName();
            $model = new $modelName;
            $model = $model->findOne($id);
            if ($status !== null) {
                $model->status = $status;
                $view = AdminHelper::itemStatusLabel($model);
            } elseif ($visibility !== null) {
                $model->visibility = $visibility;
                $view = AdminHelper::itemVisibilityLabel($model);
            }

            if (isset($model->delete_at)) {
                if ($status == IStatus::STATUS_DELETED) {
                    $model->delete_at = date('Y-m-d H:i:s');
                } else {
                    $model->delete_at = null;
                }
            }


            return Json::encode([
                'result' => $model->save() ? 1 : 0,
                'errors' => $model->errors,
                'view' => $view,
            ]);
        }
        throw new BadRequestHttpException();
    }

    protected function columnStatus($additional = false)
    {
        return [
            'attribute' => 'status',
            'format' => 'raw',
            'filter' => ($additional) ? [
                User::STATUS_PENDING => 'Pending',
                User::STATUS_ACTIVE => 'Active',
                User::STATUS_BANED => 'Banned',
                User::STATUS_DELETED => 'Deleted',
            ] : [
                User::STATUS_ACTIVE => 'Active',
                User::STATUS_BANED => 'Banned',
                User::STATUS_DELETED => 'Deleted',
            ],
            'value' => function ($item) use ($additional) {
                return AdminHelper::itemStatusLabel($item, $additional);
            },
            'contentOptions' => ['class' => 'text-center'],
        ];
    }

    protected function rememberPageSize($pageSize)
    {
        Yii::$app->session->set('page_size', $pageSize);
    }

    protected function getPageSize()
    {
        $pageSize = Yii::$app->request->get('page_size');
        $_pageSize = Yii::$app->session->get('page_size', self::$pageSize);
        if ($pageSize) {
            $this->rememberPageSize($pageSize);
        } elseif (!$_pageSize) {
            $this->rememberPageSize(self::$pageSize);
        }
        return Yii::$app->session->get('page_size', self::$pageSize);
    }

    public function actionIndex()
    {
        /** @var $query \yii\db\ActiveQuery */
        $page_size = $this->getPageSize();
        Yii::$app->formatter->nullDisplay = null;
        $modelName = $this->getModelName();
        $this->searchModel = new $modelName(['scenario' => 'search']);
        $this->searchModel->load(Yii::$app->request->get());

        return $this->render('/site/items', [
            'title' => $this->getModuleName(),
            'dataProvider' => $this->provider,
            'model' => $this->searchModel,
            'columns' => $this->columns,
            'page_size' => $page_size,
        ]);

    }

    protected function getPeriod()
    {
        return [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'week' => 'This week',
            'lastweek' => 'Last week',
            'month' => 'This month',
            'lastmonth' => 'Last month',
            'year' => 'This year',
            'lastyear' => 'Last year',
        ];
    }

    protected function getProvider()
    {
        return new ActiveDataProvider([
            'query' => $this->findAllQuery(),
            'pagination' => [
                'pageSize' => self::$pageSize,
            ],
            'sort' => [

            ]
        ]);
    }

    protected function createdColumn()
    {
        return [
            'attribute' => 'created_at',
            'format' => ['date', $this->dateFormat],
            'filter' => $this->period,
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ];
    }

    protected function updatedColumn()
    {
        return [
            'attribute' => 'updated_at',
            'format' => ['date', $this->dateFormat],
            'filter' => $this->period,
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ];
    }

    abstract protected function getColumns();




}