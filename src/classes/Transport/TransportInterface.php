<?php

namespace UzSberCredit\Transport;

/**
 * Sending HTTP requests
 * 
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
interface TransportInterface
{

    /**
     * Process request
     * @param array $aOptions Associative array with all CURL options, except \CURLOPT_POSTFIELDS
     * @param array $aData Associative array with data.
     * All keys and values must be already scalar and urlencoded, because
     * sberbank API requires unstandart json encoding.
     *
     * @return array Request result
     */
    public function request($aOptions, $aData);

}