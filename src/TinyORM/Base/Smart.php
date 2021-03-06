<?php

namespace TinyORM\Base;

class Smart
{
    protected $aData = [];
    protected $aDataDiff = [];

    /**
     * @param array $aData
     */
    public function __construct($aData = [])
    {
        $this->setData($aData);
    }

    /**
     * @param $key
     * @return bool
     */
    public function __get($key)
    {
        if (isset($this->aDataDiff[$key])) {
            return $this->aDataDiff[$key];
        }
        if (isset($this->aData[$key])) {
            return $this->aData[$key];
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->aDataDiff[$key] = $value;
    }

    /**
     * @TODO
     * public function __isset( $name ){
     * if(isset($this->aDataDiff[$name])){
     * return true;
     * }
     * return isset($this->aData[$name]);
     * }
     *
     * public function __unset( $name ){
     * unset($this->aDataDiff[$name]);
     * unset($this->aData[$name]);
     * }
     */

    /**
     * @return array
     */
    public function getData()
    {
        return !$this->aData
            ? []
            : $this->aData;
    }

    /**
     * @param $aData
     * @return $this
     */
    public function setData($aData)
    {
        $this->aData = $aData;

        return $this;
    }

    /**
     * @return array
     */
    public function getDataDiff()
    {
        return $this->aDataDiff;
    }

    /**
     * @param $aData
     * @return $this
     */
    public function setDataDiff($aData)
    {
        $this->aDataDiff = $aData;

        return $this;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return $this->exportArray();
    }

    /**
     * @return array
     */
    public function exportArray()
    {
        $data = $this->getData();
        $dataDiff = $this->getDataDiff();

        return array_merge(
            is_array($data)
                ? $data
                : [], is_array($dataDiff)
            ? $dataDiff
            : []
        );
    }

    /**
     * @return $this
     */
    public function flushDataDiff()
    {
        $this->aDataDiff = [];

        return $this;
    }

    /**
     * @return $this
     */
    public function flushData()
    {
        $this->aData = [];

        return $this;
    }

    /**
     * @return $this
     */
    public function mergeData()
    {
        $this
            ->setData($this->exportArray())
            ->flushDataDiff();

        return $this;
    }

    /**
     * @return bool
     */
    public function getID()
    {
        return isset($this->aData['id'])
            ? $this->aData['id']
            : false;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setID($id)
    {
        $this->aData['id'] = $id;

        return $this;
    }
}
