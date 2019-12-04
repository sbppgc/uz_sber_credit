<?php
/**
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package   Config_AmiClean_UzTargetsms
 * @license MIT; see LICENSE.txt
 */


error_reporting(E_ALL ^ E_NOTICE);

//
// Init ami_env
//
$AMI_ENV_SETTINGS = array(
    'mode' => 'full',
    'disable_cache' => true,
    'response_mode' => "HTML",
    "response_buffered" => true,
);
//print_r($AMI_ENV_SETTINGS);
require '/ami_env.php';

//
// Init response object
//
$oResponse = AMI::getSingleton('response');
$oResponse->start();

$res = "";

//require 'classes/LogTrait.php';
//require 'classes/AmiroFacade.php';
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

$salt = "1V95RNZI";
$idOrder = intval($_GET['id']);
$sign = trim($_GET['sign']);
$validSign = md5($idOrder."_".$salt);


if(strcmp($validSign, $sign) == 0){

    try {

        $oSberFactory = new \UzSberCredit\Factory();

        $oPayController = $oSberFactory->getPayController();
        $aRes = $oPayController->payOrder($idOrder);

        $oView = $oSberFactory->getView();

        $res .= $oView->get($aRes);

    } catch (Exception $e) {
        $res = "Error: ".$e->getMessage();
    }


} else {
    $res = "Invalid request";
}

$oResponse->write($res);
$oResponse->send();
