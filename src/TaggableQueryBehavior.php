<?php
namespace silentlun\taggable;
/**
 * TaggableQueryBehavior.php
 * @author: allen
 * @date  2021年2月23日下午2:50:16
 * @copyright  Copyright igkcms
 */

use yii\base\Behavior;
use yii\db\Expression;

/**
 * TaggableQueryBehavior
 *
 * @property \yii\db\ActiveQuery $owner
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class TaggableQueryBehavior extends Behavior
{
    /**
     * Gets entities by any tags.
     * @param string|string[] $values
     * @param string|null $attribute
     * @return \yii\db\ActiveQuery the owner
     */
    public function anyTagValues($values, $attribute = null)
    {
        $model = new $this->owner->modelClass();
        $tagClass = $model->getRelation($model->tagRelation)->modelClass;

        $this->owner
            ->innerJoinWith($model->tagRelation, false)
            ->andWhere([$tagClass::tableName() . '.' . ($attribute ?: $model->tagValueAttribute) => $model->filterTagValues($values)])
            ->addGroupBy(array_map(function ($pk) use ($model) { return $model->tableName() . '.' . $pk; }, $model->primaryKey()));

        return $this->owner;
    }

    /**
     * Gets entities by all tags.
     * @param string|string[] $values
     * @param string|null $attribute
     * @return \yii\db\ActiveQuery the owner
     */
    public function allTagValues($values, $attribute = null)
    {
        $model = new $this->owner->modelClass();

        return $this->anyTagValues($values, $attribute)->andHaving(new Expression('COUNT(*) = ' . count($model->filterTagValues($values))));
    }

    /**
     * Gets entities related by tags.
     * @param string|string[] $values
     * @param string|null $attribute
     * @return \yii\db\ActiveQuery the owner
     */
    public function relatedByTagValues($values, $attribute = null)
    {
        return $this->anyTagValues($values, $attribute)->addOrderBy(new Expression('COUNT(*) DESC'));
    }
}