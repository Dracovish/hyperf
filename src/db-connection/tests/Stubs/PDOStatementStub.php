<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\DbConnection\Stubs;

use PDO;

class PDOStatementStub extends \PDOStatement
{
    public function execute($input_parameters = null)
    {
        return [];
    }

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        parent::fetch($fetch_style, $cursor_orientation, $cursor_offset); // TODO: Change the autogenerated stub
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
    {
        parent::bindParam($parameter, $variable, $data_type, $length, $driver_options); // TODO: Change the autogenerated stub
    }

    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
        parent::bindColumn($column, $param, $type, $maxlen, $driverdata); // TODO: Change the autogenerated stub
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        parent::bindValue($parameter, $value, $data_type); // TODO: Change the autogenerated stub
    }

    public function rowCount()
    {
        parent::rowCount(); // TODO: Change the autogenerated stub
    }

    public function fetchColumn($column_number = 0)
    {
        parent::fetchColumn($column_number); // TODO: Change the autogenerated stub
    }

    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null)
    {
        return [];
    }

    public function fetchObject($class_name = 'stdClass', $ctor_args = null)
    {
        parent::fetchObject($class_name, $ctor_args); // TODO: Change the autogenerated stub
    }

    public function errorCode()
    {
        parent::errorCode(); // TODO: Change the autogenerated stub
    }

    public function errorInfo()
    {
        parent::errorInfo(); // TODO: Change the autogenerated stub
    }

    public function setAttribute($attribute, $value)
    {
        parent::setAttribute($attribute, $value); // TODO: Change the autogenerated stub
    }

    public function getAttribute($attribute)
    {
        parent::getAttribute($attribute); // TODO: Change the autogenerated stub
    }

    public function columnCount()
    {
        parent::columnCount(); // TODO: Change the autogenerated stub
    }

    public function getColumnMeta($column)
    {
        parent::getColumnMeta($column); // TODO: Change the autogenerated stub
    }

    public function setFetchMode($mode, $params = null)
    {
        parent::setFetchMode($mode, $params); // TODO: Change the autogenerated stub
    }

    public function nextRowset()
    {
        parent::nextRowset(); // TODO: Change the autogenerated stub
    }

    public function closeCursor()
    {
        parent::closeCursor(); // TODO: Change the autogenerated stub
    }

    public function debugDumpParams()
    {
        parent::debugDumpParams(); // TODO: Change the autogenerated stub
    }
}