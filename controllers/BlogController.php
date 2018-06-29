<?php

//namespace backend\controllers;
//namespace redbom\blog\controllers;
namespace redbom\blog\controllers;

use Yii;
//use common\models\Blog;
use redbom\blog\models\Blog;
//use common\models\BlogSearch;
use redbom\blog\models\BlogSearch;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\ImageManager;

/**
 * BlogController implements the CRUD actions for Blog model.
 */
class BlogController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'delete-image' => ['POST'],
                    'sort-image' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Blog models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BlogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //http://admin.yii.local/blog/redbom
    public function actionRedbom()
    {
        for($i=0; $i<0; $i++){
            $model = new Blog();
            $model->title = "Заголовок №".$i;
            $model->text = "Текст Текст ТекстТекст Текст Текст";
            $model->url = "url_".$i;
            $model->status_id = 1;
            $model->sort = 50;
            $model->save();
        }
        return "Сгенерировали";
    }

    /**
     * Displays a single Blog model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Blog model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Blog();
        $model->sort = 50;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Blog model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Blog model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    //RedBom это для удаления картинок
    public function actionDeleteImage()
    {
        if(($model = ImageManager::findOne(Yii::$app->request->post('key'))
        ) and $model->delete()){
            return true;
        } else {
            throw new NotFoundHttpException('The requested IMAGE does not exist.');
        }
    }

    //RedBom это для перетаскивания картинок
    public function actionSortImage($id)
    {
        if(Yii::$app->request->isAjax){
            $post = Yii::$app->request->post('sort');
            if($post['oldIndex'] > $post['newIndex']){
                $param = ['and', ['>=', 'sort', $post['newIndex']], ['<', 'sort', $post['oldIndex']]];
                $counter = 1;
            } else {
                $param = ['and', ['<=', 'sort', $post['newIndex']], ['>', 'sort', $post['oldIndex']]];
                $counter = -1;
            }

            ImageManager::updateAllCounters(['sort' => $counter], ['and', ['class'=>'blog', 'item_id'=>$id], $param]);
            ImageManager::updateAll(['sort' => $post['newIndex']], ['id' => $post['stack'][$post['newIndex']]['key']]);
            return true;
        }
        throw new MethodNotAllowedHttpException('Ajax only');
    }


    /**
     * Finds the Blog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Blog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        //if (($model = Blog::findOne($id)) !== null) {
        //было просто. Но бобавили примочки, чтобы уменьшить число запросов к базе по тегам.
        //но у нас ничего не получилось и не стали разбираться почему.
        if (($model = Blog::find()->with('tags')->andWhere(['id'=>$id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested BLOG does not exist.');
    }
}
