<?php

namespace UzSberCredit;

use \UzSberCredit\Transport\Curl;

/**
 * Client for Sberbank credit API
 * 
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
class SberClient implements SberClientInterface
{

    /**
     * Define language
     */
    const LANG_DATA = 'ru';

    /**
     * Define requests timeout
     */
    const TIMEOUT_SECONDS = 15;

    /**
     * Define default currency
     */
    const CURRENCY = '643'; // RUB

    /**
     * Define script name to return from Sberbank
     */
    const RETURN_SCRIPT = 'sber_credit_return.php';

    /**
     * Define country
     */
    const DEFAULT_COUTRY_PHONE_CODE = 7;

    /**
     * Site url
     * 
     * @var string
     */
    private $rootPathWww = null;

    /**
     * Configuration object
     * 
     * @var \UzSberCredit\Config
     */
    private $oConfig = null;

    /**
     * Constructor
     * 
     * @param \UzSberCredit\Config $oConfig
     * @param string $rootPathWww
     */
    public function __construct($oConfig, $rootPathWww)
    {
        $this->rootPathWww = trim($rootPathWww, '/') . '/';;
        $this->oConfig = $oConfig;
    }

    /**
     * Get default transport object
     * 
     * @return \UzSberCredit\Transport\Curl Object to use for requests
     */
    public function getTransport()
    {
        return new Curl;
    }

    /**
     * @param \UzSberCredit\Transport\Curl $oTransport
     * @param int $orderNumber
     *
     * @return array Request result
     * @return null If error
     */
    public function getOrderStatus($oTransport, $orderNumber)
    {
        $aRes = null;

        $url = $this->prepareRequestUrlGetStatus();
        //$this->deb('SberApi getOrderStatus url', $url);

        $aOptions = $this->prepareRequestOptions($url);

        $aParams = $this->prepareRequestDataGetStatus($orderNumber);

        //$aRequestRes = $this->curlRequest($url, $aParams);
        $aRequestRes = $oTransport->request($aOptions, $aParams);

        if (is_array($aRequestRes['aRes'])) {
            $aRes = $aRequestRes['aRes'];
        }

        return $aRes;
    }

    /**
     * Prepare url for 'get status' request
     * Url depends from debug/production mode (in config).
     * 
     * @return string Url
     */
    protected function prepareRequestUrlGetStatus()
    {
        return (intval($this->oConfig->get('debugEnabled'))) ?
            $this->oConfig->get('apiUrlGetStatusDeb') : $this->oConfig->get('apiUrlGetStatusProd');
    }

    /**
     * Get common CURL options for request.
     * 
     * @param string $url API url
     *
     * @return array CURL options
     */
    protected function prepareRequestOptions($url)
    {
        return array(
            \CURLOPT_URL => $url,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_POST => true,
            \CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
        );
    }

    /**
     * Prepare values for 'get status' request
     * 
     * @param int $orderNumber Order number as it registered in sberbank
     * 
     * @return array Request values
     */
    protected function prepareRequestDataGetStatus($orderNumber)
    {
        $aParams = [
            'userName' => $this->oConfig->get('login'),
            'password' => $this->oConfig->get('password'),
            'orderNumber' => $orderNumber,
        ];

        if(intval($this->oConfig->get('debugEnabled'))){
            $aParams['dummy'] = 'true';
        }
        return $aParams;
    }


    /**
     * Register order in Sberbank credits system
     * 
     * @param \UzSberCredit\Transport\Curl $oTransport
     * @param int $idOrder
     * @param int $tryNumber
     * @param array $aOrder
     * @param array $aOrderItems
     *
     * @return array Request result
     * @return null If error
     */
    public function registerOrder($oTransport, $idOrder, $tryNumber, $aOrder, $aOrderItems)
    {
        $aRes = null;

        //$this->deb('SberApi registerOrder aOrder', $aOrder);
        //$this->deb('SberApi registerOrder aOrderItems', $aOrderItems);

        if (is_array($aOrder) && is_array($aOrderItems) && count($aOrderItems)) {

            $url = $this->prepareRequestUrlRegister();

            //$this->deb('SberApi registerOrder url', $url);

            $aOptions = $this->prepareRequestOptions($url);

            $orderNumber = $this->getOrderNumber($idOrder, $tryNumber);
            //$this->deb('SberApi registerOrder orderNumber ' . $orderNumber);

            $aParams = $this->getRegisterOrderRequestParams($orderNumber, $aOrder, $aOrderItems);
            //$this->deb('aParams', $aParams);

            //$aRequestRes = $this->curlRequest($url, $aParams);
            if(is_array($aParams)){
                $aRequestRes = $oTransport->request($aOptions, $aParams);
                //$this->deb('aRequestRes', $aRequestRes);
                if (is_array($aRequestRes['aRes'])) {
                    $aRes = $aRequestRes['aRes'];
                }
            }
        }

        return $aRes;
    }

    /**
     * Prepare unique order number for Sberbank API
     * 
     * @param \UzSberCredit\Transport\Curl $oTransport
     * @param int $idOrder
     * @param int $tryNumber
     *
     * @return string Order number to use in request to Sberbnk API
     */
    protected function getOrderNumber($idOrder, $tryNumber)
    {
        return intval($idOrder) . '_' . intval($tryNumber);
    }

    /**
     * Prepare url for 'register order' request
     * Url depends from debug/production mode (in config).
     * 
     * @return string Url
     */
    protected function prepareRequestUrlRegister()
    {
        return (intval($this->oConfig->get('debugEnabled'))) ?
            $this->oConfig->get('apiUrlRegisterDeb') : $this->oConfig->get('apiUrlRegisterProd');
    }

    /**
     * Prepare values for 'register order' request
     * 
     * @param string $orderNumber Order number for Sberbank (order id + try number)
     * @param array $aOrder Order data
     * @param array $aOrderItems Order items data
     * 
     * @return array Request values
     */
    protected function getRegisterOrderRequestParams($orderNumber, $aOrder, $aOrderItems)
    {
        $aRes = null;

        $amount = $aOrder['total'];
        if (intval($this->oConfig->get('includeShipping'))) {
            $amount += $aOrder['shipping'];
        }
        $amount = $amount * 100;

        //$rootUrl = trim($this->oConfig->get('init_data_ROOT_PATH_WWW'), '/') . '/';
        $returnUrl = $this->rootPathWww . self::RETURN_SCRIPT . '?act=ok&orderNumber=' . $orderNumber;
        $failUrl = $this->rootPathWww . self::RETURN_SCRIPT . '?act=fail&orderNumber=' . $orderNumber;

        $aJsonParams = $this->getJsonParams($aOrder);

        //$this->deb('getRegisterOrderRequestParams aJsonParams', $aJsonParams);

        $aBundle = $this->getBundle($aOrder, $aOrderItems);
        //$this->deb('getRegisterOrderRequestParams aBundle', $aBundle);

        if (isset($aJsonParams['phone'])) {
            $aRes = [
                'userName' => urlencode($this->oConfig->get('login')),
                'password' => urlencode($this->oConfig->get('password')),
                'orderNumber' => urlencode($orderNumber),
                'amount' => $amount,
                'currency' => urlencode(self::CURRENCY),
                'returnUrl' => urlencode($returnUrl),
                'failUrl' => urlencode($failUrl),
                'description' => urlencode($aOrder['name']),
                'language' => urlencode(self::LANG_DATA),
                'jsonParams' => json_encode($aJsonParams),
                'orderBundle' => json_encode($aBundle), //JSON_UNESCAPED_UNICODE
                //'sami_vy_dummy' => true,
            ];

            if(intval($this->oConfig->get('debugEnabled'))){
                $aRes['dummy'] = 'true';
            }

        }
        return $aRes;
    }

    /**
     * Prepare values for 'orderBundle' section (cart contents) of 'register order' request
     * 
     * @param array $aOrder Order data
     * @param array $aOrderItems Order items data
     * 
     * @return array Request values part
     */
    private function getBundle($aOrder, $aOrderItems)
    {
        $aRes = [];

        /*
        $aCustomerDetails = $this->getCustomerDetails($aOrder);
        if (count($aCustomerDetails)) {
        $aRes['customerDetails'] = $aCustomerDetails;
        }
         */

        $aRes['cartItems'] = $this->getCartItems($aOrder, $aOrderItems);

        $aRes['installments'] = $this->getInstallments();

        return $aRes;
    }

    /**
     * Prepare values for 'jsonParams' section (user phone, email, etc) of 'register order' request
     * 
     * @param array $aOrder Order data
     * 
     * @return array Request values part
     */
    private function getJsonParams($aOrder)
    {
        $aRes = [];
        if (trim($aOrder['email']) != '') {
            $aRes['email'] = urlencode(trim($aOrder['email']));
        }
        $phone = $this->preparePhone($aOrder['custinfo']['contact']);
        if ($phone != '') {
            // Force default country code
            if (strlen($phone) == 10) {
                $phone = static::DEFAULT_COUTRY_PHONE_CODE . $phone;
            }
            $aRes['phone'] = urlencode($phone);
        }

        /*
        $aRes['deliveryInfo'] = [
        'deliveryType' => substr($aOrder['custinfo']['get_type_name'], 0, 20),  //ANS..20
        //'country' => 'ru', A..2
        //'city' => '', ANS..40
        'postAddress' => substr($aOrder['custinfo']['pickup_addr'], 0, 255) //ANS..255
        ];
         */

        return $aRes;
    }

    /**
     * Prepare cart items and shipping data for 'register order' request
     * 
     * @param array $aOrder Order data
     * @param array $aOrderItems Order items data
     * 
     * @return array Cart items
     */
    private function getCartItems($aOrder, $aOrderItems)
    {
        $aItems = [];
        for ($i = 0, $iMax = count($aOrderItems); $i < $iMax; $i++) {
            $aItems[] = $this->getItem($i, $aOrderItems[$i]);
        }

        if (intval($this->oConfig->get('includeShipping')) && (float) $aOrder['shipping'] > 0) {

            $amount = $aOrder['shipping'] * 100;

            $aItems[] = [
                'positionId' => count($aOrderItems),
                'name' => urlencode($this->prepareString($this->oConfig->get('shippingProductName'), 100)),
                'quantity' => [
                    'value' => 1,
                    'measure' => urlencode($this->oConfig->get('measure')),
                ],
                'itemAmount' => $amount,
                'itemPrice' => $amount,
                'itemCode' => 'shipping',
            ];
        }

        $aRes = [
            'items' => $aItems,
        ];
        return $aRes;
    }

    /**
     * Prepare one cart item for 'register order' request
     * 
     * @param int $positionId Numeric item position in order (from 0)
     * @param array $aItem Order item data
     * 
     * @return array Item data for request to Sberbank
     */
    private function getItem($positionId, $aItem)
    {
        $price = $aItem['price'] * 100;
        $amount = $price * $aItem['qty'];
        $aRes = [
            'positionId' => $positionId,
            'name' => urlencode($this->prepareString($aItem['ext_data']['name'], 100)),
            'quantity' => [
                'value' => $aItem['qty'],
                'measure' => urlencode($this->oConfig->get('measure')),
            ],
            'itemAmount' => $amount,
            'itemPrice' => $price,
            'itemCode' => $this->getItemCode($aItem),
        ];
        if (floatval($aItem['ext_data']['percentage_discount']) > 0) {
            $aRes['discount'] = [
                'discountType' => 'percent', //ANS..20
                'discountValue' => floatval($aItem['ext_data']['percentage_discount']),
            ];
        }

        return $aRes;
    }

    /**
     * Generate cart item 'itemCode' field for 'register order' request
     * 
     * @param array $aItem Order item data
     * 
     * @return string Item code for Sberbank
     */
    private function getItemCode($aItem)
    {
        return $aItem['id_product'] . '_' . $aItem['id_prop'] . '_' . $aItem['price_number'];
    }

    /**
     * Prepare string value for request, according to Sberbank API requirements
     * 
     * @param string $val Source value to prepare
     * @param int $maxLen Maximal available string length, according to Sberbank API requirements.
     * 
     * @return string Prepared string
     */
    private function prepareString($val, $maxLen)
    {
        // Replace quotes and parenthesis
        $res = preg_replace('/\(\)\'\"/', '_', $val);
        $res = substr($res, 0, $maxLen);
        return $res;
    }

    /**
     * Prepare phone value for request, according to Sberbank API requirements
     * 
     * @param string $val Source phone value to prepare
     * 
     * @return string Prepared string
     */
    private function preparePhone($val)
    {
        /**
         * Only numbers, 7-15 digits.
         */
        $res = preg_replace('/[^0-9]+/', '', $val);
        if (strlen($res) < 7 || strlen($res) > 15) {
            $res = '';
        }
        return $res;
    }

    /**
     * Get installment type (by config)
     * 
     * @return array Installment options
     */
    private function getInstallments()
    {
        $aRes = [
            'productType' => $this->oConfig->get('productType'),
            'productID' => $this->oConfig->get('productID'),
        ];
        return $aRes;
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
