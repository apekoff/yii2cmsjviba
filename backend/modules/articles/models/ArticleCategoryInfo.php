<?php

namespace backend\modules\articles\models;

use Yii;

/**
 * This is the model class for table "article_category_info".
 *
 * @property integer $id
 * @property integer $article_category_id
 * @property string $url
 *
 * @property ArticleCategory $articleCategory
 */
class ArticleCategoryInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article_category_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article_category_id', 'url'], 'required'],
            [['article_category_id'], 'integer'],
            [['url'], 'string', 'max' => 256],
            [['article_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ArticleCategory::className(), 'targetAttribute' => ['article_category_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_category_id' => 'Article Category ID',
            'url' => 'Url',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleCategory()
    {
        return $this->hasOne(ArticleCategory::className(), ['id' => 'article_category_id']);
    }
}
