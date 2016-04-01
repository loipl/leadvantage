<?php

/**
 * @desc Classes that inherit from this one have support for DB caching, but only for functions
 * that have @Cached in phpDoc for the function. It overrides a number of "read" functions from CrudModel
 * and adds @Cached phpDoc so their results will be cached.
 *
 * <p>Code for this is in DbCache_Wrapper class and in SingletonRegistry::getSingleInstance() in startup.php
 */
class CrudModelCaching extends CrudModel {

    /**
     * @Cached
     */
    public function get($pk, $mysql_mode = MYSQL_BOTH) {
        return parent::get($pk, $mysql_mode);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function getWhere(array $where, $mysql_mode = MYSQL_BOTH) {
        return parent::getWhere($where, $mysql_mode);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAll($orderBy = '') {
        return parent::listAll($orderBy);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listPage($page, $perPage, $orderBy = '') {
        return parent::listPage($page, $perPage, $orderBy);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listAllWhere(array $where, $orderBy = '') {
        return parent::listAllWhere($where, $orderBy);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listPageWhere(array $where, $page, $perPage, $orderBy = '') {
        return parent::listPageWhere($where, $page, $perPage, $orderBy);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function listGroup(array $pks, $orderBy = '', $isAssoc = true) {
        return parent::listGroup($pks, $orderBy, $isAssoc);
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function count() {
        return parent::count();
    }
    //--------------------------------------------------------------------------


    /**
     * @Cached
     */
    public function countWhere(array $where) {
        return parent::countWhere($where);
    }
    //--------------------------------------------------------------------------
}
