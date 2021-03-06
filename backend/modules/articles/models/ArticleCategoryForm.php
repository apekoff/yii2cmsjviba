<?php

namespace backend\modules\articles\models;

use Yii;
use yii\base\Model;
use common\models\Language;
use common\components\TranslationBehavior;
use common\components\PhotoBehavior;

class ArticleCategoryForm extends Model
{
    public $id;
    public $parent_id;
    public $name;
    public $photo;
    public $status;
    public $infos = [];
    public $meta = [];
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Object::init()
     */
    public function init()
    {
        parent::init();
        $info = new ArticleCategoryInfo();
        foreach (Language::getList() as $id => $name) {
            $this->infos[$name] = $info->getAttributes(null, ['article_category_id', 'lang_id']);
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            'translation' => [
                'class' => TranslationBehavior::className(),
                'modelClassName' => ArticleCategoryInfo::className(),
                'foreignKey' => 'article_category_id'
            ],
            'photos' => [
                'class' => PhotoBehavior::className(),
                'photoAttributes' => ['photo'],
                'storageBasePath' => Yii::getAlias('@backend/web') . '/upload/photos',
                'storageBaseUrl' => '/upload/photos'
            ]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'trim'],
            [
                'name', 'unique',
                'targetClass' => ArticleCategory::className(),
                'targetAttribute' => 'name'
            ],
            [['name', 'parent_id'], 'required'],
            ['name', 'string', 'max' => 255],
            [
                'name', 'unique',
                'targetClass' => '\backend\modules\articles\models\Article',
                'message' => Yii::t('app', 'This article name has already been taken.')
            ],
            
            ['parent_id', 'integer'],
            
            ['status', 'in', 'range' => array_keys(ArticleCategory::getAvailableStatuses())],
            [['infos', 'meta'] , 'each', 'rule' => ['safe']],
            ['photo', 'safe'],
        ];
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Model::scenarios()
     */
    public function scenarios()
    {
        return [
            'insert' => ['name', 'status', 'infos', 'photo', 'meta', 'parent_id'],
            'update' => ['name', 'status', 'infos', 'photo', 'meta', 'parent_id']
        ];
    }
}