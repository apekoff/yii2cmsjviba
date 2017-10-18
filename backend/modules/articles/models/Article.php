<?php

namespace backend\modules\articles\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use common\components\PhotoBehavior;
use common\models\User;
use common\components\caching\ICacheableDataSource;
use common\components\caching\CacheAdapterFactory;
use common\components\TranslationBehavior;
use common\CMS;
use common\models\ObjectRecord;
use common\interfaces\IHasSefUrl;

/**
 * This is the model class for table "article".
 *
 * @property integer $id
 * @property string  $article_category_ids
 * @property integer $user_id
 * @property integer $object_id
 * @property string  $name
 * @property integer $status
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $published_at
 * @property string  $photo
 *
 * @property User $user
 * @property ArticleInfo[] $articleInfos
 */
class Article extends ObjectRecord implements IHasSefUrl, ICacheableDataSource
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_DELETED = 2;
    
    const DEFAULT_FETCH_BLOCK_SIZE = 256;
    const DEFAULT_CACHE_TIME_TO_LIVE = 5184000; //24 hours in seconds
    
    /**
     * The key value in {@link getCacheId()} gets prefixed by this or by it's class
     * name if no prefix given.
     * @var string
     */
    public $cacheIdPrefix;
    
    /**
     * Cache component used
     * @var string
     */
    public $cacheComponentName = 'memcache';
    
    /**
     * Fetch records block size (used in pull method)
     * @var integerhours
     */
    public $fetchBlockSize = self::DEFAULT_FETCH_BLOCK_SIZE;
    
    /**
     * Cache life time duration
     * @var integer
     */
    public $cacheLifeTime = self::DEFAULT_CACHE_TIME_TO_LIVE;
    
    /**
     * Unique key field name
     * @var string
     */
    public $uniqueKeyFieldName = array('url', 'lang_id');
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article';
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => function() {
                    return new \yii\db\Expression('NOW()');
                },
            ],
            'photos' => [
                'class' => PhotoBehavior::className(),
                'photoAttributes' => ['photo'],
                'storageBasePath' => Yii::getAlias('@backend/web') . '/upload/photos',
                'storageBaseUrl' => '/upload/photos',
                'formats' => [
                    'small' => [
                        'width' => 120
                    ],
                    'medium' => [
                        'width' => 250
                    ],
                    'big' => [
                        'width' => 400
                    ]
                ]
            ],
            'translation' => array(
                'class' => TranslationBehavior::className(),
                'modelClassName' => CMS::modelClass('\backend\modules\articles\models\ArticleInfo'),
                'foreignKey' => 'article_id'
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article_category_ids', 'photo'], 'string'],
            [['user_id', 'name'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['created_at', 'updated_at', 'published_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::className(),
                'targetAttribute' => ['user_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_category_ids' => 'Article Category Ids',
            'user_id' => 'User ID',
            'object_id' => 'Object ID',
            'name' => 'Name',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'published_at' => 'Published At',
            'photo' => 'Photo',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * List of assigned article categories
     * @return ArticleCategory[]
     */
    public function getCategories()
    {
        $categoryIds = trim($this->article_category_ids, '{}');
        if (empty($categoryIds)) {
            return [];
        }
        return ArticleCategory::findAll(['id' => explode(',', $categoryIds)]);
    }
    
    /**
     * {@inheritDoc}
     * @see \yii\db\BaseActiveRecord::__set()
     */
    public function __set($name, $value)
    {
        if ($name == 'photo') {
            $this->setPhoto($value);
        } else {
            parent::__set($name, $value);
        }
    }
    
    /**
     * Sets photo attribute
     * @param string|array $value photo data or photo data encoded via base64
     * @return void
     */
    public function setPhoto($value)
    {
        if (is_string($value)) {
            $data = base64_encode(base64_decode($value));
            if ($data === $value) {
                $this->setAttribute(
                    'photo',
                    empty($value) ? '{}' : base64_decode($value)
                );
            } else {
                $this->setAttribute(
                    'photo',
                    $value
                );
            }
        } elseif (is_array($value)) {
            $this->setAttribute('photo', json_encode($value));
        } else if ($value === null) {
            $this->setAttribute('photo', '{}');
        } else {
            throw new \InvalidArgumentException('Value has bad format.');
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \common\interfaces\IHasSefUrl::getUrlRuleClassName()
     */
    public function getUrlRuleClassName()
    {
        return 'backend\modules\articles\components\ArticleUrlRule';
    }
    
    /**
     * {@inheritDoc}
     * @see \common\interfaces\IHasSefUrl::getShouldRebuildSefUrl()
     */
    public function getShouldRebuildSefUrl()
    {
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheComponent()
     */
    public function getCacheComponent()
    {
        return Yii::$app->get($this->cacheComponentName);
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheId()
     */
    public function getCacheId($keyValue)
    {
        if (is_array($keyValue)) {
            ksort($keyValue);
            $keyValue = serialize($keyValue);
        }
        return isset($this->cacheIdPrefix)
            ? $this->cacheIdPrefix . $keyValue
            : get_class($this) . '_' . $keyValue;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getFetchBlockSize()
     */
    public function getFetchBlockSize()
    {
        return $this->fetchBlockSize;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheLifeTime()
     */
    public function getCacheLifeTime()
    {
        return $this->cacheLifeTime;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getCacheTableName()
     */
    public function getCacheTableName()
    {
        return self::tableName();
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getUniqueKeyField()
     */
    public function getUniqueKeyField()
    {
        return $this->uniqueKeyFieldName;
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::createAdapter()
     */
    public function createAdapter(CacheAdapterFactory $factory)
    {
        return $factory->create(CacheAdapterFactory::SQL_CACHE_ADAPTER);
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::buildCriteria()
     */
    public function buildCriteria(\yii\db\Query $query, $keyValue = null)
    {
        $uniqueKeyField = $this->getUniqueKeyField();
        $query->select = array_merge(
            $query->select,
            [
                '\'article\' as "type"',
                '"t"."id"',
                '"os".' . $uniqueKeyField[0],
                '"os".' . $uniqueKeyField[1]
            ]
        );
        $query->leftJoin('object_seo "os"', '"os".to_object_id = "t".object_id');
        if ($keyValue !== null) {
            foreach ($keyValue as $key => $value) {
                $query->andFilterWhere(['"os"."' . $key . '"' => $value]);
            }
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \common\components\caching\ICacheableDataSource::getDbConnection()
     */
    public function getDbConnection()
    {
        return $this->getDb();
    }
    
    /**
     * Returns map of available statuses
     * @return string[]
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_DRAFT => Yii::t('app', 'Draft'),
            self::STATUS_PUBLISHED => Yii::t('app', 'Published'),
            self::STATUS_DELETED => Yii::t('app', 'Deleted'),
        ];
    }
}
