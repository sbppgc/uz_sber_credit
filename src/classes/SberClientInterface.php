<?php

namespace UzSberCredit;

/**
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
interface SberClientInterface
{

    /**
     * @param \UzSberCredit\Config $oConfig
     * @param string $rootPathWww
     */
    public function __construct($oConfig, $rootPathWww);

    /**
     * @return \UzSberCredit\Transport\Curl Object to use for requests
     */
    public function getTransport();

    /**
     * @param \UzSberCredit\Transport\Curl $oTransport
     * @param int $orderNumber
     *
     * @return array Request result
     * @return null If error
     */
    public function getOrderStatus($oTransport, $orderNumber);

    /**
     * @param \UzSberCredit\Transport\Curl $oTransport
     * @param int $idOrder
     * @param int $tryNumber
     * @param array $aOrder
     * @param array $aOrderItems
     *
     * @return array Request result
     * @return null If error
     */
    public function registerOrder($oTransport, $idOrder, $tryNumber, $aOrder, $aOrderItems);

}
