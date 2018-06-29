<?php

//namespace common\models;
namespace redbom\blog\models;


use common\components\behaviors\StatusBehavior;
use common\models\ImageManager;
use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;


/**
 * This is the model class for table "blog".
 *
 * @property int $id
 * @property string $title
 * @property string $text
 * @property string $url
 * @property int $status_id
 * @property int $sort
 * @property string $date_create
 * @property string $date_update
 * @property string $image
 */
class Blog extends ActiveRecord
{
    const STATUS_LIST = ['1'=>'On', '0'=>'Off', '2'=>'XX'];///в php5.6 так можно
    const IMAGES_SIZE = [
        ['50', '50'],
        ['800', NULL],
    ];///в php5.6 так можно

    public $tags_array;
    public $file;//это сам файл,  он будет валидироваться как image

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog';
    }

    public function behaviors()
    {
        return [
            'timestampBehavior' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date_create',
                'updatedAtAttribute' => 'date_update',
                'value' => new Expression('NOW()'),
            ],
            'statusBehavior' => [
                'class' => StatusBehavior::className(),
                'statusList' => self::STATUS_LIST,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'required'],
            [['url'], 'unique'],
            [['title'], 'required'],
            [['text'], 'string'],
            [['status_id'], 'integer'],
            [['sort'], 'integer', 'min' => 1, 'max' => 99],
            [['title', 'url'], 'string', 'max' => 150],
            [['tags_array', 'date_create', 'date_update'], 'safe'],
            [['image'], 'string', 'max' => 100],//это путь к файлу
            [['file'], 'image'],//сам файл,  для проверки что это картинка
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'text' => 'Текст',
            'url' => 'Url',
            'status_id' => 'Статус',
            'sort' => 'Сортировка',
            'tags_array' => 'Тэги',
            'tagsAsString' => 'Тэги',
            'author.email' => 'Автор',
            'date_create' => 'Создано',
            'date_update' => 'Обновлено',
            'image' => 'Картинка',
            'file' => 'Картинка',  //А это ХЗ зачем
        ];
    }

//Переделали под Behavior
//    //RedBom
//    public static function getStatusList()
//    {
//        //return ['0'=>'Off', '1'=>'On', '2'=>'XX'];
//        return ['1'=>'On', '0'=>'Off', '2'=>'XX'];
//    }
//
//    //RedBom
//    public function getStatusName()
//    {
//        $list = self::getStatusList();
//        return $list[$this->status_id];
//    }

    //RedBom  для связи таблицы users и blogs  по 'id' => 'user_id'
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
        //return $this->hasOne(User::className(), ['user_id' => 'id']);//а так нельзя
    }

    //RedBom  для связи таблицы blogs и image_manager
    public function getImages()
    {
        return $this->hasMany(ImageManager::className(), ['item_id' => 'id'])->andWhere(['class' => self::tableName()])->orderBy('sort');
    }

    //RedBom  для возможности удаления и сортировок картинок
    public function getImagesLinks()
    {
        return ArrayHelper::getColumn($this->images, 'imageUrl');
    }

    //RedBom  для возможности удаления и сортировок картинок
    public function getImagesLinksData()
    {
        return ArrayHelper::toArray($this->images, [
                ImageManager::className() => ['caption' => 'name', 'key' => 'id']
            ]
        );
    }

    //RedBom  для связи таблицы tags и blogs
    public function getBlogTag()
    {
        return $this->hasMany(BlogTag::className(), ['blog_id' => 'id']);
        //return $this->hasOne(BlogTag::className(), ['blog_id' => 'id']);//если взять One,  то будет не массив строк, строка.
    }

    //RedBom  для связи таблицы tags и blogs второй вариант,  типа проще
    //Но я нихрена не понял.
    public function getTags(){
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->via('blogTag');
    }

    public function getTagsAsString()
    {
        $arr = ArrayHelper::map($this->tags, 'id', 'name');
        return implode(', ', $arr);
    }

    public function getSmallImage()
    {
        if($this->image){
            $path= str_replace('admin.', '', Url::home(true)).'uploads/images/blog/50x50/'.$this->image;
        }
        else{
            $path= str_replace('admin.', '', Url::home(true)).'uploads/images/nophoto.svg';

        }
        return $path;
    }

    public function afterFind()
    {
        parent::afterFind(); // TODO: Change the autogenerated stub
        $this->tags_array = $this->tags;
    }

    public function beforeSave($insert)
    {
        //UploadedFile::getInstanceByName()//тут по имени файла идет выборка
        //UploadedFile::getInstance()//тут из модели идет выборка с указанием походу POST  поля
        if ($file = UploadedFile::getInstance($this, 'file')){
            $dir = Yii::getAlias('@images').'/blog/';

            //удаляем старый файл. ДОРАБОТАТЬ т.к. файла может не быть, и будем удалять директорию.
            if($this->image){
                if(file_exists(($dir.$this->image))){
                    unlink($dir.$this->image);
                }
                if(file_exists(($dir.'50x50/'.$this->image))){
                    unlink($dir.'50x50/'.$this->image);
                }
                if(file_exists(($dir.'800x/'.$this->image))){
                    unlink($dir.'800x/'.$this->image);
                }
            }

            $this->image = strtotime('now') . '_' . Yii::$app->getSecurity()->generateRandomString(6) . '.' . $file->extension;
            $file->saveAs($dir.$this->image);

            $imag = Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff', 0);
            $imag->resize('50', '50', Yii\image\drivers\Image::INVERSE);//попробовать тут написать просто INVERSE
            $imag->crop('50', '50');
            $imag->save($dir.'50x50/'.$this->image, 90);

            $imag = Yii::$app->image->load($dir.$this->image);
            $imag->background('#fff', 0);
            $imag->resize('50', NULL, Yii\image\drivers\Image::INVERSE);//попробовать тут написать просто INVERSE
            $imag->save($dir.'800x/'.$this->image, 90);
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub

        $arr = ArrayHelper::map($this->tags, 'id', 'id');
        //var_dump($arr);
        foreach ($this->tags_array as $one){
            if(!in_array($one, $arr)){
                $model = new BlogTag();
                $model->blog_id = $this->id;
                $model->tag_id = $one;
                $model->save();
            }
            if(isset($arr[$one])){
                unset($arr[$one]);
            }
        }

        BlogTag::deleteAll(['tag_id'=>$arr]);
    }

    public function beforeDelete()
    {
        if(parent::beforeDelete()){
            $dir = Yii::getAlias('@images').'/blog';

            if(file_exists($dir . $this->image) && is_file($dir . $this->image)){
                unlink($dir . $this->image);
            }

            foreach (self::IMAGES_SIZE as $size){
                $size_dir = $size[0].'x';
                if($size[1] !== NULL) $size_dir.=$size[1];

                if(file_exists($dir . '/' . $size_dir .'/' . $this->image) && is_file($dir . '/' . $size_dir .'/' . $this->image)){
                    unlink($dir . '/' . $size_dir .'/' . $this->image);
                }
            }

            //BlogTag::deleteAll(['blog_id' => $this->id]);//первый вариант,  не вызывает событие удаления в классе BlogTag

            //втрой вариант, удаляет по одному тегу.
            foreach ($this->blogTag as $one){
                $one->delete();
            }

            return true;
        } else {
            return false;
        }
    }

}
