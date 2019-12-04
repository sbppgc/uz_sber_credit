<?php

namespace UzSberCredit\Transport;

/**
 * Sending requests via CURL
 * 
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
class Curl implements TransportInterface
{

    /**
     * Process CURL request
     * @param array $aOptions Associative array with all CURL options, except \CURLOPT_POSTFIELDS
     * @param array $aData Associative array with data.
     * All keys and values must be already scalar and urlencoded, because
     * sberbank API requires unstandart json encoding.
     *
     * @return array Request result
     */
    public function request($aOptions, $aData)
    {

        $method = $this->detectMethod($aOptions);

        $queryString = $this->prepareQueryString($aData);

        $ch = curl_init();

        if(is_array($aOptions) && count($aOptions)){
            foreach($aOptions as $key => $val){

                if($key == \CURLOPT_URL){
                    if($method == "GET"){
                        $val .= '?'.$queryString;
                    }
                }
                //$this->deb('curl_setopt: '.$key.' = '.$val);

                curl_setopt($ch, $key, $val);
            }
        }

        if($method == "POST"){
            curl_setopt($ch, \CURLOPT_POSTFIELDS, $queryString);
        }

        $res = curl_exec($ch);
        //$this->deb('curl plain res: '.$res);

        //$aInfo = curl_getinfo($ch);

        //$aRes = json_decode($res, true);

        $aRes = [
            'aInfo' => curl_getinfo($ch),
            'aRes' => json_decode($res, true),
        ];

        curl_close($ch);

        return $aRes;
    }

    /**
     * @param array $aData - associative array dith data.
     * All keys and values must be already scalar and urlencoded, because
     * sberbank API requires unstandart json encoding.
     *
     * @return string Plain query string
     */
    private function prepareQueryString($aData)
    {
        $res = "";
        if(is_array($aData) && count($aData)){
            $prefix = "";
            foreach ($aData as $key => $val) {
                $res .= $prefix . $key . "=" . $val;
                $prefix = '&';
            }
        }
        return $res;
   }

    /**
     * @param array $aData - associative array dith data. All keys and values must be scalar and urlencoded.
     * @return string Plain query string
     */
    private function detectMethod($aOptions)
    {
        $res = "GET";
        if(isset($aOptions[\CURLOPT_POST])){
            if($aOptions[\CURLOPT_POST]){
                $res = "POST";
            }
        }
        return $res;
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