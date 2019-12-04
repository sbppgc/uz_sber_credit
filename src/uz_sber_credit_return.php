<?php
/**
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package   UzSberCredit
 * @license MIT; see LICENSE.txt
 */

error_reporting(E_ALL ^ E_NOTICE);

//
// Init ami_env
//
$AMI_ENV_SETTINGS = array(
    'mode'              => 'full',
    'disable_cache'     => true,
    'response_mode'     => "HTML",
    "response_buffered" => true,
);
//print_r($AMI_ENV_SETTINGS);
require 'ami_env.php';

//
// Init response object
//
$oResponse = AMI::getSingleton('response');
$oResponse->start();

require 'classes/Exception/BadConfigException.php';
require 'classes/Factory.php';
require 'classes/Config.php';
require 'classes/AttemptModel.php';
require 'classes/OrderDataModel.php';
require 'classes/PayController.php';
require 'classes/Transport/TransportInterface.php';
require 'classes/Transport/Curl.php';
require 'classes/SberClientInterface.php';
require 'classes/SberClient.php';
require 'classes/View.php';

$aRes = [
    'errCode' => -1,
    'errMsg' => 'Skip process',
];

try {

    $oRequest = AMI::getSingleton('env/request');

    $idSberOrder = trim($oRequest->get("orderNumber"));
    AMI_Service::log('sber_credit_return idSberOrder = '.$idSberOrder, AMI_Registry::get('path/root')."_admin/_logs/_uz_sber_credit.log");

    if(preg_match("/^([0-9]+)_([0-9]+)$/", $idSberOrder, $aMatch)){

        $idOrder = intval($aMatch[1]);
        $tryNumber = intval($aMatch[2]);
        //AMI_Service::log("sber_credit_return act = $act, idOrder = $idOrder, tryNumber = $tryNumber", AMI_Registry::get('path/root')."_admin/_logs/_uz_sber_credit.log");
        if($idOrder > 0 && $tryNumber > 0){

            $oSberFactory = new \UzSberCredit\Factory();

            $oAttemptModel = $oSberFactory->getAttemptModel();

            //$aAttempt = $oAttemptModel->getLastAttempt($idOrder);
            //AMI_Service::log("last aAttempt: ".print_r($aAttempt, true), AMI_Registry::get('path/root')."_admin/_logs/_uz_sber_credit.log");

            $aAttempt = $oAttemptModel->getAttempt($idOrder, $tryNumber);
            //AMI_Service::log("last aAttempt: ".print_r($aAttempt, true), AMI_Registry::get('path/root')."_admin/_logs/_uz_sber_credit.log");

            $oPayController = $oSberFactory->getPayController();
            $aRes = $oPayController->onReturnProcessAction($aAttempt);

        }

    } else {
        $aRes = [
            'errCode' => 103,
            'errMsg' => 'Invalid sber order id',
        ];
    }


} catch (Exception $e) {
    $aRes = [
        'errCode' => 102,
        'errMsg' => $e->getMessage(),
    ];
}

AMI_Service::log("sber_credit_return aRes: ".print_r($aRes, true), AMI_Registry::get('path/root')."_admin/_logs/_uz_sber_credit.log");

$res = time();
//$aIniData = parse_ini_file(AMI_Registry::get('path/root') . "_local/config.ini.php");
//$rootUrl = trim($aIniData['ROOT_PATH_WWW'], '/') . '/';
//AMI::getSingleton('response')->HTTP->setRedirect($rootUrl);



$oResponse->write($res);
$oResponse->send();


