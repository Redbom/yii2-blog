<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model redbom\blog\models\Blog */

$this->title = 'Update Blog: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Blogs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="blog-update">

    <h1><?//= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

    <div class="well">
        <?php foreach ($model->blogTag as $one): ?>
            <?= $one->tag->name; ?>
        <?php endforeach; ?>
    </div>

    <div class="well">
        <?php foreach ($model->tags as $one): ?>
            <?= $one->name; ?>
        <?php endforeach; ?>
    </div>

    <pre><?php //print_r($model->getTags()->asArray()->all());?></pre>

    <div class="well">
        <?// это если вернуть не массив тэкгов, а один тэг = "+++" . $model->blogTag->tag->name; ?>
    </div>


</div>
