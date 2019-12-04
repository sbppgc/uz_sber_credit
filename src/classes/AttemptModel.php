<?php

namespace UzSberCredit;

/**
 * Save and read order registration attempts
 *
 * @copyright Ugol zreniya. All rights reserved.
 * @author Sergey Prisyazhnyuk <sbpmail@ya.ru>
 * @package UzSberCredit
 * @license MIT; see LICENSE.txt
 */
class AttemptModel
{

    /**
     * Table name to store order registration attempts
     */
    const TABLE_NAME = 'cms_uz_sber_credit_attempts';

    /**
     * MySQL date format to use in DB queries
     */
    const DFMT = 'Y-m-d H:i:s';

    /**
     * According to sberbank documentation, they use numbers 0-8.
     * We use also errors 101-102
     * Anyway, mysql unsigned tinyint field supports values 0-255, therefore max value is 255
     */
    const MAX_ERROR_CODE_VAL = 255;

    /**
     * Sberbank internal order identifier max length, according to sberbank documentation.
     */
    const ID_FIELD_LENGTH = 36;

    /**
     * Order text fields (url, error message) max length, according to sberbank documentation.
     */
    const TEXT_FIELDS_LENGTH = 512;

    /**
     * @var AMI_DB object (singletone) to work with DB.
     */
    private $oDB = null;

    /**
     * Constructor
     * 
     * @param AMI_DB $oDB Variable arguments list with strings, contains an integers values to sum.
     * 
     * @return void
     */
    public function __construct($oDB)
    {
        $this->oDB = $oDB;
    }

    /**
     * Get last attempt DB record for specified order.
     * Return array with last attempt data.
     * Return null if have no attempts for specified order.
     *
     * @param int $idOrder Order identifier (site)
     * 
     * @return array Attempt data
     * @return null Null if attempt not found
     */
    public function getLastAttempt($idOrder)
    {
        $aRes = null;
        $oSnippet = \DB_Query::getSnippet('select id_order, try_number, id_in_sber, form_url, error_code, error_message from `'
            . static::TABLE_NAME . '` where id_order = ' . intval($idOrder) . ' order by try_number desc limit 1');
        $aItem = $this->oDB->fetchRow($oSnippet);
        if (is_array($aItem)) {
            $aRes = $aItem;
        }
        return $aRes;
    }

    /**
     * Get attempt DB record for specified order and attempt number.
     *
     * @param int $idOrder Order identifier (site)
     * @param int $tryNumber Try number
     * 
     * @return array Array with attempt data.
     * @return null Return null if specified attempt not exists.
     */
    public function getAttempt($idOrder, $tryNumber)
    {
        $aRes = null;
        $oSnippet = \DB_Query::getSnippet('select id_order, try_number, id_in_sber, form_url, error_code, error_message from `'
            . static::TABLE_NAME . '` where id_order = ' . intval($idOrder) . ' and try_number = ' . intval($tryNumber));
        $aItem = $this->oDB->fetchRow($oSnippet);
        if (is_array($aItem)) {
            $aRes = $aItem;
        }
        return $aRes;
    }

    /**
     * Write attempt data to DB. If 'attempt record' is not exists - create new record. If record already exists - update.
     *
     * @param int $idOrder Order identifier (site)
     * @param int $tryNumber Attempt number
     * @param array $aRequestResult Result of register order request
     * 
     * @return array Array with added/updated attempt
     * @return null Return null if error.
     */
    public function updateAttempt($idOrder, $tryNumber, $aRequestResult)
    {
        $aSql = $this->prepareData($idOrder, $tryNumber, $aRequestResult);
        $oSnippet = \DB_Query::getReplaceQuery(self::TABLE_NAME, $aSql);
        if ($this->oDB->query($oSnippet)) {
            return $this->getLastAttempt($idOrder);
        } else {
            return null;
        }
    }

    /**
     * Prepare record data.
     *
     * @param int $idOrder Order identifier (site)
     * @param int $tryNumber Attempt number
     * @param array $aRequestResult Result of register order request
     * 
     * @return array Array with data to save.
     */
    private function prepareData($idOrder, $tryNumber, $aRequestResult)
    {
        $errCode = intval($aRequestResult['errorCode']);
        if ($errCode > self::MAX_ERROR_CODE_VAL) {
            $errCode = self::MAX_ERROR_CODE_VAL;
        }
        $aRes = [
            'id_order' => (int) $idOrder,
            'try_number' => (int) $tryNumber,
            'date' => date(self::DFMT),
            'id_in_sber' => substr(trim($aRequestResult['orderId']), 0, self::ID_FIELD_LENGTH),
            'form_url' => substr(trim($aRequestResult['formUrl']), 0, self::TEXT_FIELDS_LENGTH),
            'error_code' => $errCode,
            'error_message' => substr(trim($aRequestResult['errorMessage']), 0, self::TEXT_FIELDS_LENGTH),
        ];
        return $aRes;
    }


}
