<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 12.11.15
 * Time: 17:09
 */

namespace tv88dn\crud\models;


interface SearchInterface
{

    /**
     * @return int ActiveQuery
     */
    public function search();

    /**
     * @param int $id
     * @return ActiveQuery
     */
    public function searchOne($id);

}