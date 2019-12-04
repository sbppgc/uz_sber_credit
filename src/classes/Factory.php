<?php

namespace UzSberCredit;

use UzSberCredit\Transport\Curl;

/**
 * Create objects
 * 
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
class Factory
{

    /**
     * Amiro template file name.
     */
    const TPL_FILE = 'templates/uz_sber_credit.tpl';

    /**
     * Unique template block identifier.
     */
    const TPL_BLOCK = 'uz_sber_credit_tpl';

    /**
     * Template 'set' name where defined options.
     */
    const CONFIG_TPL_SET = 'config';

    /**
     * Template engine
     *
     * @var \AMI_TemplateSystem
     */
    private $oTpl = null;

    /**
     * Initialized config engine
     *
     * @var \UzSberCredit\Config
     */
    private $oConfig = null;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get Amiro singleton database query engine
     *
     * @return \AMI_DB
     */
    public function getSingletonDB()
    {
        return \AMI::getSingleton("db");
    }

    /**
     * Get initialized template engine (with loaded module template)
     *
     * @return \AMI_TemplateSystem
     */
    public function getSingletonTpl()
    {
        if (is_null($this->oTpl)) {
            $this->oTpl = \AMI::getResource('env/template_sys');
            $this->oTpl->addBlock(self::TPL_BLOCK, self::TPL_FILE);
        }
        return $this->oTpl;
    }

    /**
     * Get initialized config engine
     *
     * @return \UzSberCredit\Config
     */
    public function getSingletonConfig()
    {
        if (is_null($this->oConfig)) {
            $oTpl = $this->getSingletonTpl();
            $aStubTplData = [];
            $configStr = $oTpl->parse(self::TPL_BLOCK . ':' . static::CONFIG_TPL_SET, $aStubTplData);
            $this->oConfig = new Config($configStr);
        }
        return $this->oConfig;
    }

    /**
     * Get model for manage registration attempts
     *
     * @return \UzSberCredit\AttemptModel
     */
    public function getAttemptModel()
    {
        return new AttemptModel($this->getSingletonDB());
    }

    /**
     * Get model to receive Amiro order data
     *
     * @return \UzSberCredit\OrderDataModel
     */
    public function getOrderDataModel()
    {
        return new OrderDataModel($this->getSingletonDB());
    }

    /**
     * Get transport object
     *
     * @return \UzSberCredit\Transport\Curl
     */
    public function getTransportCurl()
    {
        return new Curl();
    }

    /**
     * Get Sberbank API client
     *
     * @return \UzSberCredit\SberClient
     */
    public function getSberClient()
    {
        
        $aIniData = parse_ini_file(\AMI_Registry::get('path/root')."_local/config.ini.php");
        $rootPathWww = $aIniData["ROOT_PATH_WWW"];
        // Force trailing slash
        $rootPathWww = rtrim($rootPathWww, '/').'/';

        return new SberClient($this->getSingletonConfig(), $rootPathWww);
    }

    /**
     * Get view object
     *
     * @return \UzSberCredit\View
     */
    public function getView()
    {
        return new View($this->getSingletonTpl(), self::TPL_BLOCK);
    }

    /**
     * Get controller
     *
     * @return \UzSberCredit\PayController
     */
    public function getPayController()
    {
        return new PayController($this->getSingletonConfig(), $this->getAttemptModel(), $this->getOrderDataModel(), $this->getTransportCurl(), $this->getSberClient());
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
