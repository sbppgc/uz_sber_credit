<?php

namespace UzSberCredt\Tests;

require_once 'vendor/autoload.php';

use UzSberCredit\Config;
use UzSberCredit\Exception\BadConfigException;
use PHPUnit\Framework\TestCase;



class ConfigTest extends TestCase
{

    public function setUp()
    {
        //$mAmiroFacade = $this->getMock('\UzSberCredit\AmiroFacade');
        //$mAmiroFacade->expects($this->any())->method('getRootPath')->will($this->returnValue($temperature));

    }

    public function tearDown()
    {

    }

    public function initConfigDataProvider()
    {
        return array(
            // Full valid config
            'full_valid_config' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 1),

            // Config without onFailSetOrderStatus
            'config_without_onFailSetOrderStatus' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onOkSetOrderStatus = confirmed
            ', 1),

            // Config without onOkSetOrderStatus
            'config_without_onOkSetOrderStatus' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
            ', 1),

            // Config without debugEnabled
            'config_without_debugEnabled' => array('apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

            // Config without apiUrlRegisterDeb
            'config_without_apiUrlRegisterDeb' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

            // Config without apiUrlRegisterProd
            'config_without_apiUrlRegisterProd' => array('debugEnabled = 0
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

            // Config without login
            'config_without_login' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
password = some_password
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

            // Config without password
            'config_without_password' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

            // Config without productType
            'config_without_productType' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productID = 10
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

            // Config without productID
            'config_without_productID' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
includeShipping = 1
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

            // Config without includeShipping
            'config_without_includeShipping' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
productID = 10
shippingProductName = Доставка
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

            // Config without shippingProductName
            'config_without_shippingProductName' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
productID = 10
includeShipping = 1
measure = шт.
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

            // Config without measure
            'config_without_measure' => array('debugEnabled = 1
apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
login = some_login
password = some_password
productType = INSTALLMENT
productID = 10
includeShipping = 1
shippingProductName = Доставка
onFailSetOrderStatus = rejected
onOkSetOrderStatus = confirmed
            ', 0),

        );
    }

    /**
     * @dataProvider initConfigDataProvider
     */
    public function testConfig($strConfig, $isValid)
    {
        //$mTpl = $this->createMock('\AMI_TemplateSystem');
        //$mTpl->method('parse')->willReturn($strConfig);

        if(!$isValid){
            $this->expectException(BadConfigException::class);
        }
        //$this->assertInstanceOf('\AMI_TemplateSystem', $mTpl);
        try {
            $oConfig = new Config($strConfig);
            $this->assertInstanceOf('\UzSberCredit\Config', $oConfig);
        } catch (InvalidArgumentException $notExpected) {
          $this->fail();
        }
    }


    public function testGetScope()
    {
        $configString = 'debugEnabled = 1
            apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
            apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
            apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
            apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
            login = some_login
            password = some_password
            productType = INSTALLMENT
            productID = 10
            includeShipping = 1
            shippingProductName = Доставка
            measure = шт.
            onFailSetOrderStatus = rejected
            onOkSetOrderStatus = confirmed';

        $oConfig = new Config($configString);
        $aData = $oConfig->getScope();

        //$this->assertIsArray($aData); - not found
        if(!is_array($aData)){
            $this->assertEquals(true, false, 'oConfig->getScope() result is not an array.');
        }

        $this->assertArrayHasKey('debugEnabled', $aData);
        $this->assertArrayHasKey('password', $aData);
        $this->assertArrayHasKey('onOkSetOrderStatus', $aData);

        $this->assertEquals(1, $aData['debugEnabled']);
        $this->assertEquals('some_password', $aData['password']);
        $this->assertEquals('confirmed', $aData['onOkSetOrderStatus']);
    }

    public function testGet()
    {
        $configString = 'debugEnabled = 1
            apiUrlRegisterProd = https://3dsec.sberbank.ru/sbercredit/register.do
            apiUrlRegisterDeb = https://3dsec.sberbank.ru/sbercredit/register.do
            apiUrlGetStatusProd = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
            apiUrlGetStatusDeb = https://3dsec.sberbank.ru/payment/rest/getOrderStatusExtended.do
            login = some_login
            password = some_password
            productType = INSTALLMENT
            productID = 10
            includeShipping = 1
            shippingProductName = Доставка
            measure = шт.
            onFailSetOrderStatus = rejected
            onOkSetOrderStatus = confirmed';

        $oConfig = new Config($configString);

        $this->assertEquals(1, $oConfig->get('debugEnabled'));
        $this->assertEquals('some_password', $oConfig->get('password'));
        $this->assertEquals('confirmed', $oConfig->get('onOkSetOrderStatus'));
    }

    /*
    public function testGetDb()
    {
        \AMI_Service::log('testGetDb', '_admin/_logs/_test_err.log');
        $oDB = \AMI::getSingleton("db");
        $this->assertInstanceOf('\AMI_DB', $oDB);
        $this->assertEquals(1, 1);
    }
    */

}
