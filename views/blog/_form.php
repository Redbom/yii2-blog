<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget;
use \kartik\select2\Select2;
use \redbom\blog\models\Blog;
use \redbom\blog\models\Tag;

/* @var $this yii\web\View */
/* @var $model redbom\blog\models\Blog */
/* @var $form yii\widgets\ActiveForm */
/*
<?= $form->field($model, 'status_id')->textInput() ?>
*/
?>

<div class="blog-form">

    <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <div class='row'>

        <?= $form->field($model, 'file', ['options'=>['class'=>'col-xs-6']])->widget(\kartik\file\FileInput::classname(), [
            'options' => ['accept' => 'image/*'],
            'pluginOptions' => [
                'showCaption' => false,
                'showRemove' => false,
                'showUpload' => false,
                'browseClass' => 'btn btn-primary btn-block',
                'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                'browseLabel' =>  'Select Photo'
            ],
        ]);
        ?>

        <?= $form->field($model, 'title', ['options'=>['class'=>'col-xs-6']])->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'url', ['options'=>['class'=>'col-xs-6']])->textInput(['maxlength' => true]) ?>

        <?//= $form->field($model, 'status_id')->dropDownList(['1'=>'On', '0'=>'Off']) ?>
        <?= $form->field($model, 'status_id', ['options'=>['class'=>'col-xs-6']])->dropDownList(Blog::STATUS_LIST) ?>

        <?= $form->field($model, 'sort', ['options'=>['class'=>'col-xs-6']])->textInput() ?>

        <?= $form->field($model, 'tags_array', ['options'=>['class'=>'col-xs-6']])->widget(Select2::classname(), [
            'data' => \yii\helpers\ArrayHelper::map(Tag::find()->all(), 'id', 'name'),
            //    'value' => ['aaa', 'bbb', 'Пети'], //не работает
            'language' => 'ru',
            'options' => ['placeholder' => 'Select TAGS ...', 'multiple' => true],
            'pluginOptions' => [
                'allowClear' => true,
                'tags' => true,
                'maximumInputLength' => 10,
            ],
        ]); ?>


    </div>

    <?//= $form->field($model, 'text')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'text')->widget(Widget::className(), [
        'settings' => [
            'lang' => 'ru',
            'minHeight' => 200,
            'imageUpload' => \yii\helpers\Url::to(['/site/save-redactor-img', 'sub'=>'blog']),
            'plugins' => [
                'clips',
                'fullscreen',
                'imagemanager',
            ],
            'formatting' => ['p', 'blockquote', 'h1', 'h2'],
        ],
    ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?= \kartik\file\FileInput::widget([
        'name' => 'ImageManager[attachment]',
        'options' => [
             'accept' => 'image/*',
             'multiple' => true,
        ],
        'pluginOptions' => [
            'deleteUrl' => \yii\helpers\Url::toRoute(['blog/delete-image']),
            'initialPreview' => $model->imagesLinks,
            'initialPreviewAsData' => true,
            'overwriteInitial' => false,
            'initialPreviewConfig' => $model->imagesLinksData,
            'uploadUrl' => \yii\helpers\Url::to(['/site/save-img']),
            'uploadExtraData' => [
                'ImageManager[class]' => $model->formName(),
                'ImageManager[item_id]' => $model->id,
            ],
            'maxFileCount' => 10
        ],
        'pluginEvents' => [
            'filesorted' => new \yii\web\JsExpression('function(event, params){
                $.post("'.\yii\helpers\Url::toRoute(["blog/sort-image", "id"=>$model->id]).'", {sort: params});
            }')
        ],

    ]);
    ?>

</div>
