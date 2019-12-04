<?php

namespace UzSberCredit;

use UzSberCredit\Exception\BadConfigException;

/**
 * Provide configuration data
 *
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
class Config
{

    /**
     * Default empty config data
     * For example, here is all available config keys.
     *
     * @var array
     */
    private $aEmptyData = [
        'debugEnabled' => '',
        'apiUrlRegisterProd' => '',
        'apiUrlRegisterDeb' => '',
        'apiUrlGetStatusProd' => '',
        'apiUrlGetStatusDeb' => '',
        'login' => '',
        'password' => '',
        'productType' => '',
        'productID' => '',
        'includeShipping' => '',
        'shippingProductName' => '',
        'measure' => '',
        'onFailSetOrderStatus' => '',
        'onOkSetOrderStatus' => '',
        ];

    /**
     * Prepared and merged config data
     *
     * @var array
     */
    private $aData = null;

    /**
     * Constructor.
     * Receive plain config data string, parse and check config.
     *
     * @param string $configStr
     * 
     * @return void
     */
    public function __construct($configStr)
    {
        $this->parseData($configStr);
        $this->checkConfig();
    }

    /**
     * Parse config string, merge it to default empty config, and fill internal data bank.
     *
     * @param string $str Plain string with config data
     * 
     * @return void
     */
    protected function parseData($str)
    {
        $this->aData = parse_ini_string($str);

        // Fill missing items by empty values
        $this->aData += $this->aEmptyData;
    }

    /**
     * Simple check is config data contains required values.
     *
     * @throws UzSberCredit\Exception\BadConfigException
     * 
     * @return bool Returns true if required values not empty. Returns false if any required value is empty.
     */
    protected function checkConfig()
    {
        if (!is_array($this->aData)) {
            throw new BadConfigException("Config error. Config data is not an array.");
        } else {
            if (trim($this->aData['debugEnabled']) == "") {
                throw new BadConfigException("Config error. Missing required item 'debugEnabled'");
            }
            if (intval($this->aData['debugEnabled'])) {
                if (empty(trim($this->aData['apiUrlRegisterDeb']))) {
                    throw new BadConfigException("Config error. Missing required item 'apiUrlRegisterDeb'");
                }
            } else {
                if (empty(trim($this->aData['apiUrlRegisterProd']))) {
                    throw new BadConfigException("Config error. Missing required item 'apiUrlRegisterProd'");
                }
            }
            /*
            if (empty(trim($this->aData['api_url_get_status']))) {
            $this->deb("Config isValidConfig fail api_url_get_status", $this->aData);
            $res = 0;
            }
             */
            if (empty(trim($this->aData['login']))) {
                throw new BadConfigException("Config error. Missing required item 'login'");
            }
            if (empty(trim($this->aData['password']))) {
                throw new BadConfigException("Config error. Missing required item 'password'");
            }
            if (empty(trim($this->aData['productType']))) {
                throw new BadConfigException("Config error. Missing required item 'productType'");
            }
            if (empty(trim($this->aData['productID']))) {
                throw new BadConfigException("Config error. Missing required item 'productID'");
            }
            if (empty(trim($this->aData['includeShipping']))) {
                throw new BadConfigException("Config error. Missing required item 'includeShipping'");
            }
            if (intval($this->aData['includeShipping'])) {
                if (empty(trim($this->aData['shippingProductName']))) {
                    throw new BadConfigException("Config error. Missing required item 'shippingProductName'");
                }
            }
            if (empty(trim($this->aData['measure']))) {
                throw new BadConfigException("Config error. Missing required item 'measure'");
            }
        }
    }

    /**
     * Get array with config data.
     *
     * @return array
     */
    public function getScope()
    {
        return $this->aData;
    }

    /**
     * Get config value
     *
     * @param type $key
     * @param type $defaultVal
     * @return string
     */
    public function get($key, $defaultVal = '')
    {
        if (isset($this->aData[$key])) {
            return $this->aData[$key];
        } else {
            return $defaultVal;
        }
    }

}
