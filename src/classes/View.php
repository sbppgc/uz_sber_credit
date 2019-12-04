<?php

namespace UzSberCredit;

/**
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
class View
{
    /**
     * Template 'set' name to draw result
     */
    const RESULT_TPL_SET = "result";

    /**
     * Template engine
     *
     * @var \AMI_TemplateSystem
     */
    private $oTpl = null;

    /**
     * Unique template block identifier
     *
     * @var string
     */
    private $tplBlockName = null;

    /**
     *
     * @param \AMI_TemplateSystem
     * @param string $tplBlockName
     */
    public function __construct($oTpl, $tplBlockName)
    {
        $this->oTpl = $oTpl;
        $this->tplBlockName = $tplBlockName;
    }

    /**
     * Draw result into the string
     *
     * @param array $aResult
     * @return string
     */
    public function get($aResult)
    {
        //$this->deb('View aResult', $aResult);
        return $this->oTpl->parse($this->tplBlockName . ':' . static::RESULT_TPL_SET, $aResult);
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
