<?php

namespace Mapper;

/**
 * abstract model mapper.
 *
 * @package Mapper
 */
use \Zend\Db\Sql\Sql;
use \Zend\Db\TableGateway\TableGateway;
use \Zend\Db\Sql\Select;
use Zend\Form\Element\DateSelect;

abstract class AbstractModel {

    /**
     * 表名
     */
    protected $_tableName = null;

    /**
     * 表结构模型
     */
    protected $_dbModelClass = null;

    /**
     * 返回 Zend 的适配器
     * @return \Zend\Db\Adapter\Adapter
     */
    static public function getAdapter() {
        static $dbAdapter = null;

        if (!$dbAdapter) {
            $config = \Yaf\Application::app()->getConfig();
            $conf = $config->get('resources.database.params');
            if (!$conf)
                return false;

            $dbAdapter = new \Zend\Db\Adapter\Adapter($conf->toArray());
            \Yaf\Registry::set('dbAdapter', $dbAdapter);
        }

        return $dbAdapter;
    }

    /**
     * 返回 Zend 的 TableGateway
     * @return \Zend\Db\TableGateway\TableGateway
     */
    public function getDbTableGateway() {
        static $tableGateway = null;

        if (!$tableGateway)
            $tableGateway = new TableGateway($this->_tableName, self::getAdapter());

        return $tableGateway;
    }

    /**
     * 返回 Zend 的 TableGateway
     * @return \Zend\Db\TableGateway\TableGateway
     */
    public function getDbSelect() {
        static $select = null;

        if (!$select) {
            $sql = new Sql(self::getAdapter(), $this->_tableName);
            $select = $sql->select();
        }

        return $select;
    }

    /**
     * @param int $id,主键
     * @return \SiteModel | null
     */
    public function find($id = null) {
        $resultSet = $this->getDbTableGateway()->select(array('id' => $id));
        if (!$resultSet->count())
            return null;
        return new $this->_dbModelClass($resultSet->current());
    }

    /**
     * 获取所有的行.
     * @param  \Closure|string|array|\Zend\Db\Sql\Predicate\PredicateInterface $where
     * @param string|array $order
     * @param int $count
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null) {
        $adapter = self::getAdapter();

        $sql = new Sql($adapter, $this->_tableName);
        $select = $sql->select();
        $select->where($where);

        if ($count)
            $select->limit($count);

        if ($order)
            $select->order($order);
        $selectString = $sql->getSqlStringForSqlObject($select);
        $rows = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);

        $entries = array();
        foreach ($rows as $row)
            array_push($entries, new $this->_dbModelClass($row));
        return $entries;
    }

    /**
     * 插入数据
     * @param \SiteModel $model
     * @return int
     */
    public function insert($model) {
        $data = $model->toArray();
        unset($data['id']);
        return $this->getDbTableGateway()->insert($data);
    }

    /**
     * Updates existing model.
     *
     * @param \SiteModel $model  Sql table data model
     * @return int The number of rows updated.
     */
    public function update($model) {
        $data = $model->toArray();
        unset($data['id']);
        $where = array("`id` = ?" => $model->getId());
        return $this->getDbTableGateway()->update($data, $where);
    }

    /**
     * remove existing model.
     *
     * @param \SiteModel $model  Sql table data model
     * @return int The number of rows deleted.
     */
    public function remove($model) {
        $where = array("`id` = ?" => $model->getId());
        return $this->getDbTableGateway()->delete($where);
    }

}