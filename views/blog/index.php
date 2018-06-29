<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use redbom\blog\models\Blog;


/* @var $this yii\web\View */
/* @var $searchModel redbom\blog\models\BlogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Blogs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blog-index">

    <h1><?//= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Blog', ['create'], ['class' => 'btn btn-success']) ?>
    </p>



    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} {check}',
                'buttons' => [
                    'check' => function ($url, $model, $key){
                        return Html::a('<i class="fa fa-check"></i>', $url);
                    }
                ],
                'visibleButtons' => [
                    'check' => function ($model, $key, $index){
                        return $model->status_id == 1 ? true : false;
                        //return false;
                    }
                ],
            ],

            'id',
            'title',
            //'text:ntext',
            //'url:url',
            ['attribute' => 'url', 'format' => 'url', 'headerOptions' => ['class' => 'XXXXX']],
            //'status_id',
//            ['attribute' => 'status_id', 'filter' => \common\models\Blog::getStatusList(), 'value'=>function($model){
//                //return $model->statusName; //почему так можно?
//                return $model->StatusName;
//            }],
            //переделываем на Bahaviors ['attribute' => 'status_id', 'filter' => \common\models\Blog::getStatusList(), 'value'=>'statusName'],
            ['attribute' => 'status_id', 'filter' => Blog::STATUS_LIST, 'value'=>'statusName'],
            'sort',
            'smallImage:image',
            'date_update',
            'date_create:datetime',
            ['attribute' => 'tags', 'value' => 'tagsAsString'],
        ],
    ]); ?>



    <?php Pjax::end(); ?>
</div>
