<?php
namespace UzSberCredt\Tests;

use UzSberCredit\Transport\Curl;
use UzSberCredit\Config;
use UzSberCredit\AttemptModel;
use UzSberCredit\OrderDataModel;
use UzSberCredit\SberClient;
use UzSberCredit\PayController;




use PHPUnit\Framework\TestCase;

class PayControllerTest extends TestCase
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


    
    public function payOrderDataProvider()
    {
        return [
            'deb_mode_on__no_attempts__reg_ok' => [
                // Params
                [
                    'debMode' => true,
                    'idOrder' => 1,
                    'lastAttempt' => null,
                    'updateAttemptCall' => 'once', // once|never|any
                    'updateAttemptRes' => [
                        'error_code' => 0,
                        'error_message' => '',
                        'form_url' => 'url...',
                    ],
                    'orderData' => [],
                    'orderItemsData' => [],
                    'regTryNumber' => 1,
                    'sberClientRegisterResult' => [
                        'formUrl' => 'url...',
                        'orderId' => 'sberid...',
                    ],
                ],
                // Expected
                [
                    'errCode' => 0,
                    'errMsg' => '',
                    'url' => 'url...',
                ],
            ],

            'deb_mode_on__no_attempts__reg_fail' => [
                // Params
                [
                    'debMode' => true,
                    'idOrder' => 1,
                    'lastAttempt' => null,
                    'updateAttemptCall' => 'never', // once|never|any
                    'updateAttemptRes' => null,
                    'orderData' => [],
                    'orderItemsData' => [],
                    'regTryNumber' => 1,
                    'sberClientRegisterResult' => null,
                ],
                // Expected
                [
                    'errCode' => 101,
                    'errMsg' => 'Attempt to register fail',
                    'url' => '',
                ],
            ],

            'deb_mode_on__have_attempts__reg_ok' => [
                // Params
                [
                    'debMode' => true,
                    'idOrder' => 2,
                    'lastAttempt' => [
                        'id_order' => 2,
                        'try_number' => 5,
                        'id_in_sber' => 'sberid...',
                        'form_url' => '',
                        'error_code' => 0,
                        'error_message' => '',
                    ],
                    'updateAttemptCall' => 'once', // once|never|any
                    'updateAttemptRes' => [
                        'error_code' => 0,
                        'error_message' => '',
                        'form_url' => 'url...',
                    ],
                    'orderData' => [],
                    'orderItemsData' => [],
                    'regTryNumber' => 6,
                    'sberClientRegisterResult' => [
                        'formUrl' => 'url...',
                        'orderId' => 'sberid...',
                    ],
                ],
                // Expected
                [
                    'errCode' => 0,
                    'errMsg' => '',
                    'url' => 'url...',
                ],
            ],

            'deb_mode_on__have_attempts__reg_fail' => [
                // Params
                [
                    'debMode' => true,
                    'idOrder' => 2,
                    'lastAttempt' => [
                        'id_order' => 2,
                        'try_number' => 5,
                        'id_in_sber' => 'sberid...',
                        'form_url' => '',
                        'error_code' => 0,
                        'error_message' => '',
                        
                    ],
                    'updateAttemptCall' => 'never', // once|never|any
                    'updateAttemptRes' => null,
                    'orderData' => [],
                    'orderItemsData' => [],
                    'regTryNumber' => 6,
                    'sberClientRegisterResult' => null,
                ],
                // Expected
                [
                    'errCode' => 101,
                    'errMsg' => 'Attempt to register fail',
                    'url' => '',
                ],
            ],

        ];
    }

    /**
     * @dataProvider payOrderDataProvider
     */
    public function testPayOrder($aParams, $aExpected)
    {
        $oConfig = $this->getConfig($aParams['debMode']);
        $oAttemptModel = $this->getAttemptModelMockForReg($aParams['lastAttempt'], $aParams['updateAttemptCall'], $aParams['updateAttemptRes']);
        $oOrderDataModel = $this->getOrderDataModelMockForReg($aParams['orderData'], $aParams['orderItemsData'], $aParams['setOrderStatusRes']);
        $oTransport = null;
        $oSberClient = $this->getSberClientMockForReg($aParams['sberClientRegisterResult'], $aParams['idOrder'],
            $aParams['regTryNumber'], $aParams['orderData'], $aParams['orderItemsData']);

        $oPayController = new PayController($oConfig, $oAttemptModel, $oOrderDataModel, $oTransport, $oSberClient);

        $aRes = $oPayController->payOrder($aParams['idOrder']);

        $this->assertEquals($aExpected, $aRes);
    }

    public function getConfig($deb = false)
    {
        $configString = ($deb) ? static::CONFIG_DATA_DEB.static::CONFIG_DATA_COMMON_PART : static::CONFIG_DATA_PROD.static::CONFIG_DATA_COMMON_PART;
        $oConfig = new Config($configString);
        return $oConfig;
    }

    public function getAttemptModelMockForReg($lastAttempt, $updateAttemptCall, $updateAttemptRes)
    {
        $oAttemptModel = $this->getMockBuilder('AttemptModel')
            ->disableOriginalConstructor()
            ->setMethods(['getLastAttempt', 'updateAttempt'])
            ->getMock();
        
        $oAttemptModel->expects($this->once())
            ->method('getLastAttempt')
            ->will($this->returnValue($lastAttempt));
        
        $oAttemptModel->expects($this->getExpectsVal($updateAttemptCall))
            ->method('updateAttempt')
            ->will($this->returnValue($updateAttemptRes));
        
        return $oAttemptModel;
    }

    protected function getExpectsVal($val)
    {
        switch($val){
            case 'once':
                $res = $this->once();
                break;
            case 'never':
                $res = $this->never();
                break;
            default:
                $res = $this->any();
            break;
        }
        return $res;
    }

    public function getOrderDataModelMockForReg($orderData, $orderItemsData, $setOrderStatusRes)
    {
        $oOrderDataModel = $this->getMockBuilder('OrderDataModel')
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'getItems'])
            ->getMock();

        $oOrderDataModel->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($orderData));

        $oOrderDataModel->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue($orderItemsData));

        return $oOrderDataModel;

    }

    public function getSberClientMockForReg($sberClientRegisterResult, $idOrder, $tryNumber, $aOrder, $aOrderItems)
    {
        $oSberClient = $this->getMockBuilder('SberClient')
            //->setConstructorArgs(array(null))
            ->disableOriginalConstructor()
            ->setMethods(['registerOrder'])
            ->getMock();

        $oSberClient->expects($this->once())
            ->method('registerOrder')
            ->with(
                $this->anything(),
                $idOrder,
                $tryNumber,
                $aOrder,
                $aOrderItems
            )
            ->will($this->returnValue($sberClientRegisterResult));

        return $oSberClient;
    }





    public function onReturnProcessActionDataProvider()
    {
        return [
            'get_ok_noerror__set_status_ok' => [
                // Params
                [
                    'debMode' => true,
                    'aAttempt' => [
                        'id_order' => 1,
                        'try_number' => 5,
                        'id_in_sber' => 'sberid',
                        'form_url' => '',
                        'error_code' => 0,
                        'error_message' => '',
                    ],
                    'orderModelExpectedCalls' => 'once',
                    'orderModelStatusVal' => 'confirmed',
                    'orderModelSetStatusRes' => 1,
                    'sberClientGetStatusOrderId' => '1_5',
                    'sberClientGetStatusResult' => [
                        'errorCode' => 0,
                        'errorMessage' => '',
                    ],
                ],
                // Expected
                [
                    'errCode' => 0,
                    'errMsg' => '',
                ],
            ],

            'get_ok_error__set_status_ok' => [
                // Params
                [
                    'debMode' => true,
                    'aAttempt' => [
                        'id_order' => 1,
                        'try_number' => 5,
                        'id_in_sber' => 'sberid',
                        'form_url' => '',
                        'error_code' => 0,
                        'error_message' => '',
                    ],
                    'orderModelExpectedCalls' => 'once',
                    'orderModelStatusVal' => 'rejected',
                    'orderModelSetStatusRes' => 1,
                    'sberClientGetStatusOrderId' => '1_5',
                    'sberClientGetStatusResult' => [
                        'errorCode' => 1,
                        'errorMessage' => 'some error',
                    ],
                ],
                // Expected
                [
                    'errCode' => 1,
                    'errMsg' => 'some error',
                ],
            ],

            'get_fail__set_status_ok' => [
                // Params
                [
                    'debMode' => true,
                    'aAttempt' => [
                        'id_order' => 1,
                        'try_number' => 5,
                        'id_in_sber' => 'sberid',
                        'form_url' => '',
                        'error_code' => 0,
                        'error_message' => '',
                    ],
                    'orderModelExpectedCalls' => 'never',
                    'orderModelStatusVal' => null,
                    'orderModelSetStatusRes' => null,
                    'sberClientGetStatusOrderId' => '1_5',
                    'sberClientGetStatusResult' => null,
                ],
                // Expected
                [
                    'errCode' => 102,
                    'errMsg' => 'Get status request fail',
                ],
            ],

        ];
    }

    
    /**
     * @dataProvider onReturnProcessActionDataProvider
     */
    public function testOnReturnProcessAction($aParams, $aExpected)
    {
        $oConfig = $this->getConfig($aParams['debMode']);
        $oAttemptModel = null;
        $oOrderDataModel = $this->getOrderDataModelMockForGetStatus($aParams['aAttempt']['id_order'], $aParams['orderModelStatusVal'], $aParams['orderModelSetStatusRes'], $aParams['orderModelExpectedCalls']);
        $oTransport = null;
        $oSberClient = $this->getSberClientMockForGetStatus($aParams['sberClientGetStatusOrderId'], $aParams['sberClientGetStatusResult']);

        $oPayController = new PayController($oConfig, $oAttemptModel, $oOrderDataModel, $oTransport, $oSberClient);

        $aRes = $oPayController->onReturnProcessAction($aParams['aAttempt']);

        $this->assertEquals($aExpected, $aRes);
    }


    public function getOrderDataModelMockForGetStatus($idOrder, $statusVal, $setStatusRes, $expectedCalls)
    {
        $oOrderDataModel = $this->getMockBuilder('OrderDataModel')
            ->disableOriginalConstructor()
            ->setMethods(['setOrderStatus'])
            ->getMock();

        if($expectedCalls == 'never'){
            $oOrderDataModel->expects($this->getExpectsVal($expectedCalls))
            ->method('setOrderStatus');
        } else {
            $oOrderDataModel->expects($this->getExpectsVal($expectedCalls))
            ->method('setOrderStatus')
            ->with(
                $idOrder,
                $statusVal
            )
            ->will($this->returnValue($setStatusRes));
        }

        return $oOrderDataModel;
    }


    public function getSberClientMockForGetStatus($getStatusOrderId, $getStatusResult)
    {
        $oSberClient = $this->getMockBuilder('SberClient')
            ->disableOriginalConstructor()
            ->setMethods(['getOrderStatus'])
            ->getMock();

        $oSberClient->expects($this->once())
            ->method('getOrderStatus')
            ->with(
                $this->anything(),
                $getStatusOrderId
            )
            ->will($this->returnValue($getStatusResult));

        return $oSberClient;
    }


}
