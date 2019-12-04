<?php

namespace UzSberCredit;

/**
 * Get Amiro orders data and statuses
 * 
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
class OrderDataModel
{

    /**
     * CMS orders table name
     */
    const TABLE_NAME_ORDER = 'cms_es_orders';

    /**
     * CMS order items table name
     */
    const TABLE_NAME_ORDER_ITEMS = 'cms_es_order_items';

    /**
     * Instance of \AMI_DB
     * 
     * @var \AMI_DB
     */
    protected $oDB = null;

    /**
     * Constructor
     * 
     * @param \AMI_DB $oDB Instance of \AMI_DB
     */
    public function __construct($oDB)
    {
        $this->oDB = $oDB;
    }

    /**
     * Return site order data
     * 
     * @param int $idOrder 
     * 
     * @return array Order data
     * @return null Null if order not exists
     */
    public function getOrder($idOrder)
    {
        $aRes = null;
        $oSnippet = \DB_Query::getSnippet('select total, shipping, name, email, custinfo from `'
            . static::TABLE_NAME_ORDER . '` where id = ' . intval($idOrder));
        //$this->deb('getOrder oSnippet', $oSnippet->get());
        $aOrder = $this->oDB->fetchRow($oSnippet);
        //$this->deb('getOrder aOrder', $aOrder);
        if (is_array($aOrder)) {
            $aRes = $aOrder;
            $aRes['custinfo'] = unserialize($aRes['custinfo']);
        }
        //$this->deb('getOrder aRes', $aRes);
        return $aRes;
    }

    /**
     * Return site order items data
     * 
     * @param int $idOrder 
     * 
     * @return array Order items data
     */
    public function getItems($idOrder)
    {
        $aRes = [];
        $oSnippet = \DB_Query::getSnippet('select id_product, id_prop, price_number, price, qty, ext_data from `'
            . static::TABLE_NAME_ORDER_ITEMS . '` where id_order = ' . intval($idOrder) . ' order by id');
        $oRS = $this->oDB->select($oSnippet);
        foreach ($oRS as $aRecord) {
            $aRecord['ext_data'] = unserialize($aRecord['ext_data']);
            $aRes[] = $aRecord;
        }
        return $aRes;
    }


    /**
     * Set site order status, with process actions, defined to assigned status.
     * 
     * @param int $idOrder
     * @param string $status
     * 
     * @return bool True if status assigned, false if error
     */
    public function setOrderStatus($idOrder, $status)
    {
        $res = false;
        if(isset($GLOBALS['frn'])){
            require_once $GLOBALS['CLASSES_PATH'] . 'EshopOrder.php';
            $oOrder = new \EshopOrder();
            $oOrder->updateStatus($GLOBALS['frn'], $idOrder, 'auto', $status);
            $res = true;
        }
        return $res;
    }


    /**
     * Write debug info to file
     *
     * @param string $str Message to save
     * @param array $data (optional) Any additional data to save with print_r.
     * @return void
     */
    private function deb($str, $data = null)
    {
        if (is_null($data)) {
            \AMI_Service::log($str, \AMI_Registry::get('path/root') . '_admin/_logs/_uz_sber_credit.log');
        } else {
            \AMI_Service::log($str . ": " . print_r($data, true), \AMI_Registry::get('path/root') . '_admin/_logs/_uz_sber_credit.log');
        }
    }

}
