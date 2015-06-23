<?php

namespace Omnipay\BarclaysEpdq\Message;

use Omnipay\Common\Exception\InvalidResponseException;

/**
 * BarclaysEpdq Complete Purchase Request
 */
class EssentialCompletePurchaseRequest extends EssentialPurchaseRequest
{

	protected static $validShaOutKeys = [
		'AAVADDRESS', 'AAVCHECK', 'AAVMAIL', 'AAVNAME', 'AAVPHONE', 'AAVZIP', 'ACCEPTANCE', 'ALIAS', 'AMOUNT',
		'BIC', 'BIN', 'BRAND', 'CARDNO', 'CCCTY', 'CN', 'COLLECTOR_BIC', 'COLLECTOR_IBAN', 'COMPLUS',
		'CREATION_STATUS', 'CREDITDEBIT', 'CURRENCY', 'CVCCHECK', 'DCC_COMMPERCENTAGE', 'DCC_CONVAMOUNT',
		'DCC_CONVCCY', 'DCC_EXCHRATE', 'DCC_EXCHRATESOURCE', 'DCC_EXCHRATETS', 'DCC_INDICATOR',
		'DCC_MARGINPERCENTAGE', 'DCC_VALIDHOURS', 'DEVICEID', 'DIGESTCARDNO', 'ECI', 'ED', 'EMAIL', 'ENCCARDNO',
		'FXAMOUNT', 'FXCURRENCY', 'IP', 'IPCTY', 'MANDATEID', 'MOBILEMODE', 'NBREMAILUSAGE', 'NBRIPUSAGE',
		'NBRIPUSAGE_ALLTX', 'NBRUSAGE', 'NCERROR', 'ORDERID', 'PAYID', 'PAYMENT_REFERENCE', 'PM', 'SCO_CATEGORY',
		'SCORING', 'SEQUENCETYPE', 'SIGNDATE', 'STATUS', 'SUBBRAND', 'SUBSCRIPTION_ID', 'TRXDATE', 'VC'
	];

    public function getData()
    {
        // Barclays allows GET or POST methods for the sending of parameters..
        $requestData = $this->getRequestData();

        // Calculate the SHA and verify if it is a legitimate request
        if ($this->getShaOut() && array_key_exists('SHASIGN', $requestData)) {
            $barclaysSha = (string)$requestData['SHASIGN'];
            unset($requestData['SHASIGN']);

	        // Only test against the allowed parameters
	        $shaData = [];
	        foreach ($requestData as $key => $value) {
		        if (in_array(strtoupper($key), static::$validShaOutKeys, true)) {
			        $shaData[$key] = $value;
		        }
	        }

            $ourSha = $this->calculateSha($this->cleanParameters($shaData), $this->getShaOut());

            if ($ourSha !== $barclaysSha) {
                throw new InvalidResponseException("Hashes do not match, request is faulty or has been tampered with.");
            }
        }

        return $requestData;
    }

    public function getRequestData()
    {
        $data = ($this->getCallbackMethod() == 'POST') ?
            $this->httpRequest->request->all() :
            $this->httpRequest->query->all();
        if (empty($data)) {
            throw new InvalidResponseException(sprintf(
                "No callback data was passed in the %s request",
                $this->getCallbackMethod()
            ));
        }

        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new EssentialCompletePurchaseResponse($this, $data);
    }
}
