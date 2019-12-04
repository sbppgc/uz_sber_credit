<?php

namespace UzSberCredt\Tests;

require_once 'vendor/autoload.php';

use UzSberCredit\Factory;
use PHPUnit\Framework\TestCase;



class FactoryTest extends TestCase
{

    public function setUp()
    {

    }

    public function tearDown()
    {

    }


    /**
     * 
     */
    public function testGetSingletonDB()
    {
        $oFactory = new Factory();
        $oDB = $oFactory->getSingletonDB();
        $this->assertInstanceOf('\AMI_DB', $oDB);
    }


    /**
     * 
     */
    public function testGetSingletonTpl()
    {
        $oFactory = new Factory();
        $oTpl = $oFactory->getSingletonTpl();
        $this->assertInstanceOf('\AMI_TemplateSystem', $oTpl);
    }
    
    /**
     * 
     */
    public function testGetSingletonConfig()
    {
        $oFactory = new Factory();
        $oConfig = $oFactory->getSingletonConfig();
        $this->assertInstanceOf('\UzSberCredit\Config', $oConfig);
    }
    
    /**
     * 
     */
    public function testGetAttemptModel()
    {
        $oFactory = new Factory();
        $oModel = $oFactory->getAttemptModel();
        $this->assertInstanceOf('\UzSberCredit\AttemptModel', $oModel);
    }

    /**
     * 
     */
    public function testGetOrderDataModel()
    {
        $oFactory = new Factory();
        $oModel = $oFactory->getOrderDataModel();
        $this->assertInstanceOf('\UzSberCredit\OrderDataModel', $oModel);
    }
    
    /**
     * 
     */
    public function testGetTransportCurl()
    {
        $oFactory = new Factory();
        $oTransport = $oFactory->getTransportCurl();
        $this->assertInstanceOf('\UzSberCredit\Transport\Curl', $oTransport);
    }


    /**
     * 
     */
    public function testGetSberClient()
    {
        $oFactory = new Factory();
        $oClient = $oFactory->getSberClient();
        $this->assertInstanceOf('\UzSberCredit\SberClient', $oClient);
    }

    /**
     * 
     */
    public function testGetView()
    {
        $oFactory = new Factory();
        $oView = $oFactory->getView();
        $this->assertInstanceOf('\UzSberCredit\View', $oView);
    }

    /**
     * 
     */
    public function testGetPayController()
    {
        $oFactory = new Factory();
        $oCtrl = $oFactory->getPayController();
        $this->assertInstanceOf('\UzSberCredit\PayController', $oCtrl);
    }

}
