<?php
/**
 * Created by PhpStorm.
 * User: gerbe
 * Date: 20-6-2016
 * Time: 20:31
 */

class CategorieModel extends PersistenceModel {
    const orm = 'Categorie';
    protected static $instance;

    public function __construct()
    {
        parent::__construct("mededelingen/");
    }

    /**
     * @param $categorie
     * @return false|Categorie
     */
    public static function get($categorie)
    {
        return static::instance()->retrieveByPrimaryKey(array($categorie));
    }

    /**
     * @return Categorie[]
     */
    public static function getAll() {
        return static::instance()->find();
    }
}