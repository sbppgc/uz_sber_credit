<?php
namespace UzSberCredt\Tests;

use UzSberCredit\Config;
use UzSberCredit\SberClient;
use UzSberCredit\Transport\Curl;


use PHPUnit\Framework\TestCase;

class SberClientTest extends TestCase
{

    const ROOT_PATH_WWW = '/test/root/path/';

    const CONFIG_DATA_COMMON_PART = '
        apiUrlRegisterProd = stub_reg_url_prod
        apiUrlRegisterDeb = stub_reg_url_deb
        apiUrlGetStatusProd = stub_status_url_prod
        apiUrlGetStatusDeb = stub_status_url_deb
        login = some_login
        password = some_password
        productType = INSTALLMENT
        productID = 10
        includeShipping = 1
        shippingProductName = name_shipping
        measure = meas
        onFailSetOrderStatus = rejected
        onOkSetOrderStatus = confirmed
    ';

    const CONFIG_DATA_DEB = '
        debugEnabled = 1
    ';

    const CONFIG_DATA_PROD = '
        debugEnabled = 0
    ';


    protected $oSberClient = null;

    /*
    public function setUp()
    {
        $oConfig = new Config(static::CONFIG_DATA);
        $this->oSberClient = new SberClient($oConfig, static::ROOT_PATH_WWW);
    }

    public function tearDown()
    {
        $this->oSberClient = null;
    }
    */

    /**
     * Call pretected/private methods.
     *
     * @param object &$object    SberClient Instance
     * @param string $methodName Method name to call
     * @param array  $parameters Arguments array
     *
     * @return mixed Called method result
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }    

    public function testGetTransport()
    {
        $oConfig = new Config(static::CONFIG_DATA_PROD.static::CONFIG_DATA_COMMON_PART);
        $this->oSberClient = new SberClient($oConfig, static::ROOT_PATH_WWW);
        $oTransport = $this->oSberClient->getTransport();
        $this->assertInstanceOf('UzSberCredit\Transport\Curl', $oTransport);
    }


    // Test prepare right API url's by config

    public function prepareGetStatusUrlDataProvider()
    {
        return array(
            'deb' => array(static::CONFIG_DATA_DEB.static::CONFIG_DATA_COMMON_PART, 'stub_status_url_deb'),
            'prod' => array(static::CONFIG_DATA_PROD.static::CONFIG_DATA_COMMON_PART, 'stub_status_url_prod'),
        );
    }

    /**
     * @dataProvider prepareGetStatusUrlDataProvider
     */
    public function testPrepareGetStatusUrl($configString, $expectedUrl)
    {
        $oSberClient = $this->getSberClient($configString);
        $url = $this->invokeMethod($oSberClient, 'prepareRequestUrlGetStatus', array());
        $this->assertEquals($expectedUrl, $url);
    }



    /**
     * Test prepare right curl options by config/url
     */
    public function testPrepareRequestOptions()
    {
        $url = 'test_url';
        $aExpected = array(
            \CURLOPT_URL => $url,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_POST => true,
            \CURLOPT_TIMEOUT => SberClient::TIMEOUT_SECONDS,
        );
        $oSberClient = $this->getSberClient(static::CONFIG_DATA_PROD.static::CONFIG_DATA_COMMON_PART);
        //$aOptions = $oSberClient->prepareRequestOptions($url);
        $aParams = array($url);
        $aOptions = $this->invokeMethod($oSberClient, 'prepareRequestOptions', $aParams);
        $this->assertEquals($aExpected, $aOptions);
    }

    /**
     * Test prepare right order number for sber
     */
    public function testGetOrderNumber()
    {
        $oSberClient = $this->getSberClient(static::CONFIG_DATA_DEB.static::CONFIG_DATA_COMMON_PART);

        //$res = $oSberClient->getOrderNumber(1, 1);
        $aParams = array(1, 1);
        $res = $this->invokeMethod($oSberClient, 'getOrderNumber', $aParams);
        $this->assertEquals('1_1', $res);

        //$res = $oSberClient->getOrderNumber(0, 0);
        $aParams = array(0, 0);
        $res = $this->invokeMethod($oSberClient, 'getOrderNumber', $aParams);
        $this->assertEquals('0_0', $res);

        //$res = $oSberClient->getOrderNumber('', '');
        $aParams = array('', '');
        $res = $this->invokeMethod($oSberClient, 'getOrderNumber', $aParams);
        $this->assertEquals('0_0', $res);

        //$res = $oSberClient->getOrderNumber(null, null);
        $aParams = array(null, null);
        $res = $this->invokeMethod($oSberClient, 'getOrderNumber', $aParams);
        $this->assertEquals('0_0', $res);

        //$res = $oSberClient->getOrderNumber(array(), array());
        $aParams = array(array(), array());
        $res = $this->invokeMethod($oSberClient, 'getOrderNumber', $aParams);
        $this->assertEquals('0_0', $res);
    }



    /**
     * Test prepare right request data for deb mode
     */
    public function testPrepareRequestDataGetStatusDeb()
    {
        $orderNumber = 1;
        $oSberClient = $this->getSberClient(static::CONFIG_DATA_DEB.static::CONFIG_DATA_COMMON_PART);
        //$aData = $oSberClient->prepareRequestDataGetStatus($orderNumber);

        $aParams = array($orderNumber);
        $aData = $this->invokeMethod($oSberClient, 'prepareRequestDataGetStatus', $aParams);
        
        $this->assertEquals('some_login', $aData['userName']);
        $this->assertEquals('some_password', $aData['password']);
        $this->assertEquals($orderNumber, $aData['orderNumber']);
        $this->assertEquals(true, isset($aData['dummy']));
    }

    /**
     * Test prepare right request data for prod mode
     */
    public function testPrepareRequestDataGetStatusProd()
    {
        $orderNumber = 1;
        $oSberClient = $this->getSberClient(static::CONFIG_DATA_PROD.static::CONFIG_DATA_COMMON_PART);
        //$aData = $oSberClient->prepareRequestDataGetStatus($orderNumber);
        $aParams = array($orderNumber);
        $aData = $this->invokeMethod($oSberClient, 'prepareRequestDataGetStatus', $aParams);

        $this->assertEquals('some_login', $aData['userName']);
        $this->assertEquals('some_password', $aData['password']);
        $this->assertEquals($orderNumber, $aData['orderNumber']);
        $this->assertEquals(false, isset($aData['dummy']));
    }




    public function prepareRegisterUrlDataProvider()
    {
        return array(
            'deb' => array(static::CONFIG_DATA_DEB.static::CONFIG_DATA_COMMON_PART, 'stub_reg_url_deb'),
            'prod' => array(static::CONFIG_DATA_PROD.static::CONFIG_DATA_COMMON_PART, 'stub_reg_url_prod'),
        );
    }

    /**
     * @dataProvider prepareRegisterUrlDataProvider
     */
    public function testPrepareRegisterUrl($configString, $expectedUrl)
    {
        $oSberClient = $this->getSberClient($configString);
        //$url = $oSberClient->prepareRequestUrlRegister();

        $aParams = array();
        $url = $this->invokeMethod($oSberClient, 'prepareRequestUrlRegister', $aParams);

        $this->assertEquals($expectedUrl, $url);
    }



    public function getRegisterOrderRequestParamsDataProvider()
    {
        $aRes = array();

        $orderNumber = '1_2';

        $aOrder = array(
            'total' => 100,
            'includeShipping' => 1,
            'shipping' => 50,
            'email' => 'qwe@test.ru',
            'custinfo' => array(
                'contact' => '9008007060',
            ),
        );

        $aOrderItems = array(
            array(
                'id_product' => 1,
                'id_prop' => 0,
                'price_number' => 0,
                'price' => 100,
                'qty' => 1,
                'ext_data' => array(
                    'name' => 'item1',
                    'percentage_discount' => 0,
                ),
            ),
        );

        $aResult = array(
            'userName' => 'some_login',
            'password' => 'some_password',
            'orderNumber' => $orderNumber,
            'amount' => 15000,
            'currency' => '643',
            'returnUrl' => 'test%2Froot%2Fpath%2Fsber_credit_return.php%3Fact%3Dok%26orderNumber%3D1_2',
            'failUrl' => 'test%2Froot%2Fpath%2Fsber_credit_return.php%3Fact%3Dfail%26orderNumber%3D1_2',
            'description' =>'',
            'language' => 'ru',
            'jsonParams' => '{"email":"qwe%40test.ru","phone":"79008007060"}',
            'orderBundle' => '{"cartItems":{"items":[{"positionId":0,"name":"item1","quantity":{"value":1,"measure":"meas"},'
                . '"itemAmount":10000,"itemPrice":10000,"itemCode":"1_0_0"},{"positionId":1,"name":"name_shipping",'
                . '"quantity":{"value":1,"measure":"meas"},"itemAmount":5000,"itemPrice":5000,"itemCode":"shipping"}]},'
                . '"installments":{"productType":"INSTALLMENT","productID":"10"}}',
        );

        $aTestResultAdd = array(
            'dummy' => 'true',
        );


        $aRes['deb'] = array(static::CONFIG_DATA_DEB.static::CONFIG_DATA_COMMON_PART, $orderNumber, $aOrder, $aOrderItems, $aResult+$aTestResultAdd);
        $aRes['prod'] = array(static::CONFIG_DATA_PROD.static::CONFIG_DATA_COMMON_PART, $orderNumber, $aOrder, $aOrderItems, $aResult);

        return $aRes;
    }

    /**
     * @dataProvider getRegisterOrderRequestParamsDataProvider
     */
    public function testGetRegisterOrderRequestParams($configString, $orderNumber, $aOrder, $aOrderItems, $aExpected)
    {
        $oSberClient = $this->getSberClient($configString);
        //$aData = $oSberClient->getRegisterOrderRequestParams($orderNumber, $aOrder, $aOrderItems);

        $aParams = array($orderNumber, $aOrder, $aOrderItems);
        $aData = $this->invokeMethod($oSberClient, 'getRegisterOrderRequestParams', $aParams);

        //\AMI_Service::log("testGetRegisterOrderRequestParams aData: ".print_r($aData, true), \AMI_Registry::get('path/root')."_admin/_logs/_uz_err.log");

        $this->assertEquals($aExpected, $aData);
    }





    /**
     *
     */
    public function testRegisterOrder()
    {

        $configString = static::CONFIG_DATA_DEB.static::CONFIG_DATA_COMMON_PART;

        $aOrder = array(
            'total' => 100,
            'includeShipping' => 1,
            'shipping' => 50,
            'email' => 'qwe@test.ru',
            'custinfo' => array(
                'contact' => '9008007060',
            ),
        );

        $aOrderItems = array(
            array(
                'id_product' => 1,
                'id_prop' => 0,
                'price_number' => 0,
                'price' => 100,
                'qty' => 1,
                'ext_data' => array(
                    'name' => 'item1',
                    'percentage_discount' => 0,
                ),
            ),
        );

        $aOkResp = array(
            'aInfo' => array(),
            'aRes' => array('ok' => 1),
        );

        $aFailResp1 = array(
            'aInfo' => array(),
            'aRes' => null,
        );

        $aFailResp2 = array(
            'aInfo' => array(),
            'aRes' => '',
        );

        $oSberClient = $this->getSberClient($configString);

        $oTransport = $this->getTransportMock($aOkResp);

        // Test for invalid order
        //$res = $oSberClient->registerOrder($oTransport, 1, 2, null, $aOrderItems);
        $aParams = array($oTransport, 1, 2, null, $aOrderItems);
        $res = $this->invokeMethod($oSberClient, 'registerOrder', $aParams);
        $this->assertEquals(null, $res);

        // Test for invalid order
        //$res = $oSberClient->registerOrder($oTransport, 1, 2, array(), $aOrderItems);
        $aParams = array($oTransport, 1, 2, array(), $aOrderItems);
        $res = $this->invokeMethod($oSberClient, 'registerOrder', $aParams);
        $this->assertEquals(null, $res);

        // Test for invalid order items
        //$res = $oSberClient->registerOrder($oTransport, 1, 2, $aOrder, null);
        $aParams = array($oTransport, 1, 2, array(), $aOrderItems);
        $res = $this->invokeMethod($oSberClient, 'registerOrder', $aParams);
        $this->assertEquals(null, $res);

        // Test for empty order items
        //$res = $oSberClient->registerOrder($oTransport, 1, 2, $aOrder, array());
        $aParams = array($oTransport, 1, 2, $aOrder, array());
        $res = $this->invokeMethod($oSberClient, 'registerOrder', $aParams);
        $this->assertEquals(null, $res);

        // Test when transport returns 'ok' response
        //$res = $oSberClient->registerOrder($oTransport, 1, 2, $aOrder, $aOrderItems);
        $aParams = array($oTransport, 1, 2, $aOrder, $aOrderItems);
        $res = $this->invokeMethod($oSberClient, 'registerOrder', $aParams);
        $this->assertEquals($aOkResp['aRes'], $res);

        // Test when transport returns 'failed'
        $oTransport = $this->getTransportMock($aFailResp1);
        //$res = $oSberClient->registerOrder($oTransport, 1, 2, $aOrder, $aOrderItems);
        $aParams = array($oTransport, 1, 2, $aOrder, $aOrderItems);
        $res = $this->invokeMethod($oSberClient, 'registerOrder', $aParams);
        $this->assertEquals(null, $res);

        // Test when transport returns 'failed'
        $oTransport = $this->getTransportMock($aFailResp2);
        //$res = $oSberClient->registerOrder($oTransport, 1, 2, $aOrder, $aOrderItems);
        $aParams = array($oTransport, 1, 2, $aOrder, $aOrderItems);
        $res = $this->invokeMethod($oSberClient, 'registerOrder', $aParams);
        $this->assertEquals(null, $res);

        //\AMI_Service::log("testGetRegisterOrderRequestParams aData: ".print_r($aData, true), \AMI_Registry::get('path/root')."_admin/_logs/_uz_err.log");

    }


    protected function getSberClient($configString)
    {
        $oConfig = new Config($configString);
        return new SberClient($oConfig, static::ROOT_PATH_WWW);
    }


    protected function getTransportMock($aResp)
    {
        $oTransport = $this->createMock('UzSberCredit\Transport\Curl');

        $oTransport->method('request')
            ->willReturn($aResp);

        return $oTransport;
    }

}
