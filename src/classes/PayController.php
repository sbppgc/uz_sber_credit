<?php

namespace UzSberCredit;

/**
 * Top-level logic for sending order to Sberbank, and status synchronization.
 *
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
class PayController
{

    /**
     * Only russian consumers support
     */
    const LANG_DATA = 'ru';

    /**
     * Configuration object
     * 
     * @var \UzSberCredit\Config
     */
    protected $oConfig = null;

    /**
     * AttemptModel object
     * 
     * @var \UzSberCredit\AttemptModel
     */
    protected $oAttemptModel = null;

    /**
     * Transport object
     * 
     * @var \UzSberCredit\OrderDataModel
     */
    protected $oOrderDataModel = null;

    /**
     * Transport object
     * 
     * @var \UzSberCredit\TransportInterface
     */
    protected $oTransport = null;

    /**
     * Client object
     * 
     * @var \UzSberCredit\SberClient
     */
    protected $oSberClient = null;
    

    /**
     * Constructor
     * 
     * @param \UzSberCredit\Config $oConfig
     * @param \UzSberCredit\AttemptModel $oAttemptModel
     * @param \UzSberCredit\OrderDataModel $oOrderDataModel
     * @param \UzSberCredit\TransportInterface $oTransport TransportInterface implementation
     * @param \UzSberCredit\SberClient $oSberClient
     */
    public function __construct($oConfig, $oAttemptModel, $oOrderDataModel, $oTransport, $oSberClient)
    {
        $this->oConfig = $oConfig;
        $this->oAttemptModel = $oAttemptModel;
        $this->oOrderDataModel = $oOrderDataModel;
        $this->oTransport = $oTransport;
        $this->oSberClient = $oSberClient;
    }

    /**
     * Create new attempt to pay order, register order in Sberbank, get url to redirect.
     * Values in result array:
     * errCode int Error code. 0 if no errors.
     * errMsg string Error message. Empty if no errors.
     * url string URL to redirect (to Sberbank credit form). Empty if any error.
     * 
     * @param int $idOrder
     * 
     * @return array Result
     */
    public function payOrder($idOrder)
    {
        // Result structure
        $aRes = [
            'errCode' => 0,
            'errMsg' => '',
            'url' => '',
        ];

        //$this->deb('idOrder = ' . $idOrder);
        //$this->deb("oConfig", $this->oConfig->getScope());

        // Sber requires unique order ID to register each time.
        // nextTryNumber is a postfix for real order id. Needs if user fails to get credit first time, and tries again.
        $nextTryNumber = 1;

        // Get last order registration attempt data
        $aLastAttempt = $this->oAttemptModel->getLastAttempt($idOrder);
        //$this->deb("prev attempt data", $aOrderRegAttempt);

        if (is_array($aLastAttempt)) {
            // Set up valid next try number
            $nextTryNumber = (int) $aLastAttempt['try_number'] + 1;
        }

        // If actual registered order not found, register new order
        $aOrderRegAttempt = $this->registerOrder($idOrder, $nextTryNumber);
        //$this->deb("new attempt data", $aOrderRegAttempt);

        if (is_array($aOrderRegAttempt)) {
            // Order registered, redirect to payment url
            $aRes = [
                'errCode' => (int) $aOrderRegAttempt['error_code'],
                'errMsg' => (string) $aOrderRegAttempt['error_message'],
                'url' => (string) $aOrderRegAttempt['form_url'],
            ];
        } else {

            $aRes = [
                'errCode' => 101,
                'errMsg' => 'Attempt to register fail',
                'url' => '',
            ];
        }

        //$this->deb('payOrder ' . $idOrder, $aRes);
        return $aRes;
    }

    /**
     * Register order in Sberbak and save attempt data.
     * 
     * @param int $idOrder
     * @param int $tryNumber
     * 
     * @return array Result
     */
    protected function registerOrder($idOrder, $tryNumber)
    {
        $aRes = null;
        //$this->deb('registerOrder' . $idOrder . ' - ' . $tryNumber);
        $aOrder = $this->oOrderDataModel->getOrder($idOrder);
        //$this->deb('aOrder', $aOrder);
        $aOrderItems = $this->oOrderDataModel->getItems($idOrder);

        //$this->deb('aOrderItems', $aOrderItems);
        $aRequestResult = $this->oSberClient->registerOrder($this->oTransport, $idOrder, $tryNumber, $aOrder, $aOrderItems);
        //$this->deb('aRequestResult', $aRequestResult);
        if (!is_null($aRequestResult)) {
            $aRes = $this->oAttemptModel->updateAttempt($idOrder, $tryNumber, $aRequestResult);
        }
        return $aRes;
    }

    /**
     * Process action on return from Sberbank site after fill credit form.
     * Check order status in Sberbank, and modify order status on site.
     * 
     * @param array $aAttempt Attempt data
     * 
     * @return array Result
     */
    public function onReturnProcessAction($aAttempt)
    {
        // Result structure
        
        $orderNumber = $aAttempt['id_order'].'_'.$aAttempt['try_number'];

        $aOrderStatus = $this->oSberClient->getOrderStatus($this->oTransport, $orderNumber);
        //$this->deb("aOrderStatus", $aOrderStatus);

        if(is_array($aOrderStatus)){
            if(intval($aOrderStatus['errorCode']) > 0){
                // Order error, set rejected status
                $this->oOrderDataModel->setOrderStatus($aAttempt['id_order'], $this->oConfig->get('onFailSetOrderStatus'));
            } else {
                // Order ok, set ok status
                $this->oOrderDataModel->setOrderStatus($aAttempt['id_order'], $this->oConfig->get('onOkSetOrderStatus'));
            }

            $aRes = [
                'errCode' => $aOrderStatus['errorCode'],
                'errMsg' => $aOrderStatus['errorMessage'],
            ];
        } else {
            $aRes = [
                'errCode' => 102,
                'errMsg' => 'Get status request fail',
            ];
        }

        return $aRes;
    }


    /**
     * Write debug info to file
     *
     * @param string $str Message to save
     * @param array $data (optional) Any additional data to save with print_r.
     * @return void
     */
    protected function deb($str, $data = null)
    {
        if (is_null($data)) {
            \AMI_Service::log($str, \AMI_Registry::get('path/root') . '_admin/_logs/_uz_sber_credit.log');
        } else {
            \AMI_Service::log($str . ": " . print_r($data, true), \AMI_Registry::get('path/root') . '_admin/_logs/_uz_sber_credit.log');
        }
    }

}
