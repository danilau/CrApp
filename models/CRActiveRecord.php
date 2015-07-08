<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 27.6.15
 * Time: 3.08
 */
namespace app\models;

use app\models\CRActiveQuery;
use Yii;
use yii\base\NotSupportedException;
use yii\redis\ActiveRecord;

class CRActiveRecord extends ActiveRecord{

    public static function find()
    {
        return Yii::createObject(CRActiveQuery::className(), [get_called_class()]);
    }


    public static function keyPrefix()
    {
        return 'global:classroom:users';
    }

    public static function tsKey(){
        return 'global:classroom:updateTS';
    }

    /*
     * Implements an insert scheme for global:classroom:(key_value):{values}
     */
    public function insert($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            return false;
        }
        if (!$this->beforeSave(true)) {
            return false;
        }
        $db = static::getDb();
        $values = $this->getDirtyAttributes($attributes);
        $primaryKeys = $this->primaryKey();
        $primaryKeyValue = $this->getAttribute($primaryKeys[0]);
        if ($primaryKeyValue === null)
            throw new NotSupportedException('PK can not be null.');
        $pk = [];
        foreach ($primaryKeys as $key) {
            $values[$key] = $this->getAttribute($key);
        }

        $key = static::keyPrefix() . ':' . $primaryKeyValue;
        // save attributes
        $setArgs = [$key];
        foreach ($values as $attribute => $value) {
            // only insert attributes that are not null
            if ($value !== null && $attribute != $primaryKeys[0]) {
                if (is_bool($value)) {
                    $value = (int) $value;
                }
                $setArgs[] = $attribute;
                $setArgs[] = $value;
            }
        }

        if (count($setArgs) > 1) {
            $db->executeCommand('HMSET', $setArgs);
            static::updateTSKey();
            $db->executeCommand('EXPIRE', [$setArgs[0],Yii::$app->userSettings->expireTime]);
        }

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    public static function updateAll($attributes, $condition = null)
    {
        if (empty($attributes)) {
            return 0;
        }
        $db = static::getDb();
        $n = 0;

        foreach (self::fetchPks($condition) as $pk) {
            $newPk = $pk;
            $pk = static::buildKey($pk);
            $key = static::keyPrefix() . ':' . $pk;

            // save attributes
            $delArgs = [$key];
            $setArgs = [$key];
            foreach ($attributes as $attribute => $value) {
                if (isset($newPk[$attribute])) {
                    $newPk[$attribute] = $value;
                }
                if ($value !== null) {
                    if (is_bool($value)) {
                        $value = (int) $value;
                    }
                    $setArgs[] = $attribute;
                    $setArgs[] = $value;
                } else {
                    $delArgs[] = $attribute;
                }
            }

            $newPk = static::buildKey($newPk);
            $newKey = static::keyPrefix() . ':' . $newPk;

            // rename index if pk changed
            if ($newPk != $pk) {
                $db->executeCommand('MULTI');
                if (count($setArgs) > 1) {
                    $db->executeCommand('HMSET', $setArgs);
                }
                if (count($delArgs) > 1) {
                    $db->executeCommand('HDEL', $delArgs);
                }
                $db->executeCommand('LINSERT', [static::keyPrefix(), 'AFTER', $pk, $newPk]);
                $db->executeCommand('LREM', [static::keyPrefix(), 0, $pk]);
                $db->executeCommand('RENAME', [$key, $newKey]);
                $db->executeCommand('EXEC');
            } else {
                if (count($setArgs) > 1) {
                    $db->executeCommand('HMSET', $setArgs);
                }
                if (count($delArgs) > 1) {
                    $db->executeCommand('HDEL', $delArgs);
                }
            }
            static::updateTSKey();
            $db->executeCommand('EXPIRE', [$setArgs[0],Yii::$app->userSettings->expireTime]);
            $n++;
        }

        return $n;
    }

    public static function deleteAll($condition = null)
    {
        $pks = self::fetchPks($condition);

        if (empty($pks)) {
            return 0;
        }

        $db = static::getDb();
        $attributeKeys = [];
        $db->executeCommand('MULTI');
        foreach ($pks as $pk) {
            $pk = static::buildKey($pk);
            $attributeKeys[] = static::keyPrefix() . ':' . $pk;
        }

        $db->executeCommand('DEL', $attributeKeys);
        static::updateTSKey();
        $result = $db->executeCommand('EXEC');

        return end($result);
    }

    private static function fetchPks($condition)
    {
        $query = static::find();
        $query->where($condition);
        $records = $query->asArray()->all(); // TODO limit fetched columns to pk

        $primaryKey = static::primaryKey();

        $pks = [];
        foreach ($records as $record) {
            $pk = [];
            foreach ($primaryKey as $key) {
                $pk[$key] = $record[$key];
            }
            $pks[] = $pk;
        }

        return $pks;
    }

    private static function updateTSKey(){
        $db = static::getDb();
        $db->executeCommand('SET', [static::tsKey(),date("Y-m-d H:i:s")]);
    }
}