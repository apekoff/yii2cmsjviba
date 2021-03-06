<?php

namespace backend\modules\articles\components;

use Yii;
use backend\modules\articles\models\Article;
use backend\modules\articles\models\ArticleForm;
use backend\modules\articles\models\ArticleInfo;
use common\models\Language;
use yii\data\ActiveDataProvider;
use yii\base\InvalidParamException;

class ArticleManager extends \common\components\Component
{
    const PERM_CREATE = 'articleCreate';
    const PERM_UPDATE = 'articleUpdate';
    const PERM_DELETE = 'articleDelete';
    const PERM_LIST = 'articleList';
    const PERM_VIEW = 'articleView';
    
    /**
     * Returns built data provider to fetch list of articles
     * @param array $params request parameters
     * @return \yii\data\ActiveDataProvider
     */
    public function getDataProvider(array $params = [])
    {
        return new ActiveDataProvider([
            'query' => Article::find(),
        ]);
    }
    
    /**
     * Loads article by it's id
     * @param int $articleId the article id
     * @throws \InvalidArgumentException if article id is invalid
     * @return \backend\modules\articles\models\Article loaded article record
     */
    public function loadArticleById($articleId)
    {
        try {
            $articleId = self::toPositiveInt($articleId);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Article id is invalid.');
        }
        try {
            return Article::findOne($articleId);
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to find the article because of db error: ' . $e->getMessage());
        }
    }
    
    /**
     * Loads data from the source article into article data form
     * @param ArticleForm $model   the target to load into
     * @param int         $article the article id to load from
     * @return void
     * @throws \InvalidArgumentException if the article id is invalid
     * @throws InvalidParamException     if the article has not found
     */
    public function loadArticleFormById(ArticleForm $model, $articleId)
    {
        if (! $article = $this->loadArticleById($articleId)) {
            throw new InvalidParamException('Article has not found.');
        }
        $model->setAttributes($article->attributes);
        $model->id = $articleId;
        $model->categories = $article->getJsonAttributeAsArray('article_category_ids');
        
        $langs = Language::getList();
        $existingInfos = [];
        foreach ($article->infos as $info) {
            $existingInfos[$langs[$info->lang_id]] = $info;
        }
        foreach ($langs as $id => $name) {
            $infoAttributes = isset($existingInfos[$name])
                ? $existingInfos[$name]->getAttributes(
                      ['title', 'teaser', 'text', 'url']
                  )
                : [];
            $model->infos[$name] = $infoAttributes;
            
            $model->meta[$name] = isset($existingInfos[$name])
                ? $existingInfos[$name]->getMetaAsArray('meta')
                : [
                    'title' => '',
                    'description' => '',
                    'keywords'
                ];
        }
    }
    
    /**
     * Deletes the article by it's id
     * @param int $articleId the article id
     * @throws InvalidParamException if article has not found
     * @return boolean whether operation has successfully completed
     */
    public function deleteArticleById($articleId)
    {
        if (! $article = $this->loadArticleById($articleId)) {
            throw new InvalidParamException('Article has not found.');
        }
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            if ($article->delete()) {
                $urlManager = Yii::$app->urlManager;
                $urlManager->clearUrlCache($article);
                $urlManager->deleteObjectSeoByObjectId($article->object_id);
                ArticleInfo::deleteAll(['article_id' => $article->id]);
                $article->deleteAllAttachedFiles();
                $transaction->commit();
                return true;
            }
        } catch (\yii\db\Exception $e) {
            Yii::error(
                'Unable to delete the article. ID: ' . $article->id .
                '. Cause is a db error: ' . $e->getMessage()
            );
        }
        $transaction->rollBack();
        return false;
    }
    
    /**
     * Creates a new article.
     * @param ArticleForm $from article data form
     * @return Article|array article record on success,
     * array of errors otherwise
     */
    public function createArticle(ArticleForm $form)
    {
        if ($form->hasErrors()) {
            return $form->getErrors();
        }
        $langs = Language::getList();
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $article = new Article();
            $article->attributes = $form->attributes;
            $article->setJsonAttributeFromArray(
                'article_category_ids',
                $form->categories
            );
            $article->user_id = Yii::$app->user->getId();
            if (! $article->save()) {
                $transaction->rollBack();
                return [
                    'Article' => $article->getErrors()
                ];
            }
            
            foreach ($langs as $id => $name) {
                if (! isset($form->infos[$name])) {
                    continue;
                }
                $infoAttributes = $form->infos[$name];
                $articleInfo = new ArticleInfo();
                $articleInfo->attributes = $infoAttributes;
                $articleInfo->article_id = $article->id;
                $articleInfo->setMetaFromArray(
                    'meta',
                    isset($form->meta[$name])
                        ? $form->meta[$name]
                        : [
                            'title' => '',
                            'description' => '',
                            'keywords' => ''
                        ]
                );
                if (($langId = array_search($name, $langs)) === false) {
                    $transaction->rollBack();
                    return [
                        'infos[' . $name . '][lang_id]' => ['Unknown language name.']
                    ];
                }
                $articleInfo->lang_id = $langId;
                if (! $articleInfo->save()) {
                    $transaction->rollBack();
                    $ret = [];
                    foreach ($articleInfo->getErrors() as $attr => $errors) {
                        $ret['infos[' . $name . '][' . $attr . ']'] = $errors;
                    }
                    return $ret;
                }
            }
            
            $article->refresh();
            Yii::$app->urlManager->buildSefUrl($article);
            
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to create an article because of db error: ' . $e->getMessage());
            $transaction->rollBack();
            return [
                'name' => Yii::t('app', 'Unable to create an article because of database error.')
            ];
        }
        
        $transaction->commit();
        return $article;
    }
    
    /**
     * Updates the article and it's related data regarding
     * to the data set in the article form object
     * @param int         $articleId the article id
     * @param ArticleForm $model     the article model
     * @return Article the updated record on success, otherwise
     * list of errors
     * @throws \InvalidArgumentException if the article id is invalid
     * @throws InvalidParamException     if the article has not found
     */
    public function updateArticleById($articleId, ArticleForm $model)
    {
        if ($model->hasErrors()) {
            return $form->getErrors();
        }
        $langs = Language::getList();
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        $article = $this->loadArticleById($articleId);
        if (! $article) {
            throw new InvalidParamException('Article has not found.');
        }
        
        try {
            $article->attributes = $model->attributes;
            $article->setJsonAttributeFromArray(
                'article_category_ids',
                $model->categories
            );
            if (! $article->save()) {
                $transaction->rollBack();
                return [
                    'Article' => $article->getErrors()
                ];
            }
            
            $existingInfos = [];
            foreach ($article->infos as $articleInfo) {
                $existingInfos[$langs[$articleInfo->lang_id]] = $articleInfo;
            }
            
            foreach ($langs as $id => $name) {
                if (! isset($model->infos[$name])) {
                    continue;
                }
                if (isset($existingInfos[$name])) {
                    $articleInfo = $existingInfos[$name];
                } else {
                    $articleInfo = new ArticleInfo();
                    $articleInfo->lang_id = array_search($name, $langs);
                    $articleInfo->article_id = $article->id;
                }
                $articleInfo->attributes = $model->infos[$name];
                $articleInfo->setMetaFromArray(
                    'meta',
                    isset($model->meta[$name])
                        ? $model->meta[$name]
                        : [
                            'title' => '',
                            'description' => '',
                            'keywords' => ''
                        ]
                );
                if (! $articleInfo->save()) {
                    $transaction->rollBack();
                    $ret = [];
                    foreach ($articleInfo->getErrors() as $attr => $errors) {
                        $ret['infos[' . $name . '][' . $attr . ']'] = $errors;
                    }
                    return $ret;
                }
            }
            
            Yii::$app->urlManager->buildSefUrl($article);
            
        } catch (\yii\db\Exception $e) {
            Yii::error('Unable to update the article because of db error: ' . $e->getMessage());
            $transaction->rollBack();
            return [
                'name' => Yii::t('app', 'Unable to update the article because of database error.')
            ];
        }
        
        $transaction->commit();
        return $article;
    }
}