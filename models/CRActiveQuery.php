<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 27.6.15
 * Time: 4.06
 */
namespace app\models;

use yii\base\Component;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;
use yii\db\ActiveRelationTrait;
use yii\db\QueryTrait;


class CRActiveQuery extends Component implements ActiveQueryInterface
{
    use QueryTrait;
    use ActiveQueryTrait;
    use ActiveRelationTrait;

    const EVENT_INIT = 'init';


    public function __construct($modelClass, $config = [])
    {
        $this->modelClass = $modelClass;
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        $this->trigger(self::EVENT_INIT);
    }

    public function all($db = null)
    {
        // TODO add support for orderBy
        $data = $this->executeScript($db, 'All');
        $rows = [];
        foreach ($data as $dataRow) {
            $row = [];
            $c = count($dataRow);
            for ($i = 0; $i < $c;) {
                $row[$dataRow[$i++]] = $dataRow[$i++];
            }

            $rows[] = $row;
        }
        if (!empty($rows)) {
            $models = $this->createModels($rows);
            if (!empty($this->with)) {
                $this->findWith($this->with, $models);
            }
            if (!$this->asArray) {
                foreach ($models as $model) {
                    $model->afterFind();
                }
            }

            return $models;
        } else {
            return [];
        }
    }

    public function one($db = null)
    {
        // TODO add support for orderBy
        $data = $this->executeScript($db, 'One');
        if (empty($data)) {
            return null;
        }
        $row = [];
        $c = count($data);
        for ($i = 0; $i < $c;) {
            $row[$data[$i++]] = $data[$i++];
        }
        if ($this->asArray) {
            $model = $row;
        } else {
            /* @var $class ActiveRecord */
            $class = $this->modelClass;
            $model = $class::instantiate($row);
            $class = get_class($model);
            $class::populateRecord($model, $row);
        }
        if (!empty($this->with)) {
            $models = [$model];
            $this->findWith($this->with, $models);
            $model = $models[0];
        }
        if (!$this->asArray) {
            $model->afterFind();
        }

        return $model;
    }

    public function count($q = '*', $db = null)
    {
        if ($this->where === null) {
            /* @var $modelClass ActiveRecord */
            $modelClass = $this->modelClass;
            if ($db === null) {
                $db = $modelClass::getDb();
            }

            return $db->executeCommand('LLEN', [$modelClass::keyPrefix()]);
        } else {
            return $this->executeScript($db, 'Count');
        }
    }

    public function exists($db = null)
    {
        return $this->one($db) !== null;
    }

    public function column($column, $db = null)
    {
        // TODO add support for orderBy
        return $this->executeScript($db, 'Column', $column);
    }

    public function sum($column, $db = null)
    {
        return $this->executeScript($db, 'Sum', $column);
    }

    public function average($column, $db = null)
    {
        return $this->executeScript($db, 'Average', $column);
    }

    public function min($column, $db = null)
    {
        return $this->executeScript($db, 'Min', $column);
    }

    public function max($column, $db = null)
    {
        return $this->executeScript($db, 'Max', $column);
    }

    public function scalar($attribute, $db = null)
    {
        $record = $this->one($db);
        if ($record !== null) {
            return $record->hasAttribute($attribute) ? $record->$attribute : null;
        } else {
            return null;
        }
    }

    protected function executeScript($db, $type, $columnName = null)
    {
        if ($this->primaryModel !== null) {
            // lazy loading
            if ($this->via instanceof self) {
                // via junction table
                $viaModels = $this->via->findJunctionRows([$this->primaryModel]);
                $this->filterByModels($viaModels);
            } elseif (is_array($this->via)) {
                // via relation
                /* @var $viaQuery ActiveQuery */
                list($viaName, $viaQuery) = $this->via;
                if ($viaQuery->multiple) {
                    $viaModels = $viaQuery->all();
                    $this->primaryModel->populateRelation($viaName, $viaModels);
                } else {
                    $model = $viaQuery->one();
                    $this->primaryModel->populateRelation($viaName, $model);
                    $viaModels = $model === null ? [] : [$model];
                }
                $this->filterByModels($viaModels);
            } else {
                $this->filterByModels([$this->primaryModel]);
            }
        }

        if (!empty($this->orderBy)) {
            throw new NotSupportedException('orderBy is currently not supported by redis ActiveRecord.');
        }

        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;

        if ($db === null) {
            $db = $modelClass::getDb();
        }

        // find by primary key if possible. This is much faster than scanning all records
        if (is_array($this->where) && !isset($this->where[0]) && $modelClass::isPrimaryKey(array_keys($this->where))) {
            return $this->findByPk($db, $type, $columnName);
        }

        $method = 'build' . $type;
        $script = $db->getLuaScriptBuilder()->$method($this, $columnName);

        return $db->executeCommand('EVAL', [$script, 0]);
    }

    private function findByPk($db, $type, $columnName = null)
    {
        if (count($this->where) == 1) {
            $pks = (array) reset($this->where);
        } else {
            foreach ($this->where as $values) {
                if (is_array($values)) {
                    // TODO support composite IN for composite PK
                    throw new NotSupportedException('Find by composite PK is not supported by redis ActiveRecord.');
                }
            }
            $pks = [$this->where];
        }

        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;

        if ($type == 'All' && empty($pks)){
            $pks = [];
            $all_keys = $db->executeCommand('KEYS',[$modelClass::keyPrefix().':*']);
            foreach($all_keys as $key){
                $pks []= explode(':',$key)[3];
            }
        }

        if ($type == 'Count') {
            $start = 0;
            $limit = null;
        } else {
            $start = $this->offset === null ? 0 : $this->offset;
            $limit = $this->limit;
        }

        $i = 0;
        $data = [];
        foreach ($pks as $pk) {
            if (++$i > $start && ($limit === null || $i <= $start + $limit)) {
                $key = $modelClass::keyPrefix() . ':' . $modelClass::buildKey($pk);
                $result = $db->executeCommand('HGETALL', [$key]);
                if (!empty($result)) {
                    $result []= $modelClass::primaryKey()[0];
                    $result []= $pk;
                    $data[] = $result;
                    if ($type === 'One' && $this->orderBy === null) {
                        break;
                    }
                }
            }
        }
        // TODO support orderBy

        switch ($type) {
            case 'All':
                return $data;
            case 'One':
                return reset($data);
            case 'Count':
                return count($data);
            case 'Column':
                $column = [];
                foreach ($data as $dataRow) {
                    $row = [];
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        $row[$dataRow[$i++]] = $dataRow[$i++];
                    }
                    $column[] = $row[$columnName];
                }

                return $column;
            case 'Sum':
                $sum = 0;
                foreach ($data as $dataRow) {
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        if ($dataRow[$i++] == $columnName) {
                            $sum += $dataRow[$i];
                            break;
                        }
                    }
                }

                return $sum;
            case 'Average':
                $sum = 0;
                $count = 0;
                foreach ($data as $dataRow) {
                    $count++;
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        if ($dataRow[$i++] == $columnName) {
                            $sum += $dataRow[$i];
                            break;
                        }
                    }
                }

                return $sum / $count;
            case 'Min':
                $min = null;
                foreach ($data as $dataRow) {
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        if ($dataRow[$i++] == $columnName && ($min == null || $dataRow[$i] < $min)) {
                            $min = $dataRow[$i];
                            break;
                        }
                    }
                }

                return $min;
            case 'Max':
                $max = null;
                foreach ($data as $dataRow) {
                    $c = count($dataRow);
                    for ($i = 0; $i < $c;) {
                        if ($dataRow[$i++] == $columnName && ($max == null || $dataRow[$i] > $max)) {
                            $max = $dataRow[$i];
                            break;
                        }
                    }
                }

                return $max;
        }
        throw new InvalidParamException('Unknown fetch type: ' . $type);
    }
}
