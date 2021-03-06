<?php
namespace OnPay\API;


use function GuzzleHttp\Psr7\str;

class PaymentWindow
{
    const METHOD_CARD = 'card';
    const METHOD_MOBILEPAY = 'mobilepay';
    const METHOD_VIABILL = 'viabill';

    private $gatewayId;
    private $currency;
    private $amount;
    private $reference;
    private $acceptUrl;
    private $type;
    private $method;
    private $_3dsecure;
    private $language;
    private $declineUrl;
    private $callbackUrl;
    private $design;
    private $testMode;
    private $secret;
    private $availableFields;
    private $requiredFields;
    private $actionUrl = "https://onpay.io/window/v3/";

    /**
     * PaymentWindow constructor.
     */
    public function __construct()
    {
        $this->availableFields = [
            "gatewayId",
            "currency",
            "amount",
            "reference",
            "acceptUrl",
            "type",
            "_3dsecure",
            "language",
            "declineUrl",
            "callbackUrl",
            "design",
            "testMode",
            "method"
        ];

        $this->requiredFields = [
            "gatewayId",
            "currency",
            "amount",
            "reference",
            "acceptUrl",
        ];
    }

    /**
     * @param string $gatewayId
     */
    public function setGatewayId($gatewayId)
    {
        $this->gatewayId = $gatewayId;
    }

    /**
     * @return string
     */
    public function getGatewayId() {
        return $this->gatewayId;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getReference() {
        return $this->reference;
    }

    /**
     * @param string $acceptUrl
     */
    public function setAcceptUrl($acceptUrl)
    {
        $this->acceptUrl = $acceptUrl;
    }

    /**
     * @return string
     */
    public function getAcceptUrl() {
        return $this->acceptUrl;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param bool $secureEnabled
     * @deprecated
     */
    public function setSecureEnabled($secureEnabled)
    {
        $this->set3DSecure($secureEnabled);
    }

    /**
     * @return bool
     * @deprecated
     */
    public function hasSecureEnabled() {
        return $this->is3DSecure();
    }

    /**
     * @param bool $threeDs
     */
    public function set3DSecure($threeDs) {
        if ($threeDs) {
            $this->_3dsecure = 'forced';
        } else {
            $this->_3dsecure = null;
        }
    }

    /**
     * @return bool
     */
    public function is3DSecure() {
        return 'forced' === $this->_3dsecure;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @param mixed $declineUrl
     */
    public function setDeclineUrl($declineUrl)
    {
        $this->declineUrl = $declineUrl;
    }

    /**
     * @return string
     */
    public function getDeclineUrl() {
        return $this->declineUrl;
    }

    /**
     * @param mixed $callbackUrl
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
    }

    public function getCallbackUrl() {
        return $this->callbackUrl;
    }

    /**
     * @param mixed $design
     */
    public function setDesign($design)
    {
        $this->design = $design;
    }

    /**
     * @return string
     */
    public function getDesign() {
        return $this->design;
    }

    /**
     * @param mixed $testMode
     */
    public function setTestMode($testMode)
    {
        $this->testMode = $testMode;
    }

    /**
     * @return mixed
     */
    public function getTestMode() {
        return $this->testMode;
    }

    /**
     * @param mixed $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getSecret() {
        return $this->secret;
    }

    /**
     * Generates hmac secret
     * @return string
     */
    public function generateSecret() {

        $fields = $this->getAvailableFields();
        $queryString = strtolower(http_build_query($fields));
        $hmac = hash_hmac('sha1', $queryString, $this->secret);
        return $hmac;
    }

    /**
     * Gets all filled fields
     * @return array
     */
    private function getAvailableFields() {

        $fields = [];

        foreach ($this->availableFields as $field) {
            if(property_exists($this, $field) && null !== $this->{$field}) {
                if (0 === strpos($field, '_')) {
                    $key = 'onpay_' . strtolower(substr($field, 1));
                } else {
                    $key = 'onpay_' . strtolower($field);
                }
                $fields[$key] = $this->{$field};
            }
        }

        ksort($fields);
        return $fields;
    }

    /**
     * Get fields for form
     * @return array
     */
    public function getFormFields() {

        $fields = $this->getAvailableFields();
        $fields['onpay_hmac_sha1'] = $this->generateSecret();
        return $fields;
    }

    /**
     * Checks if the PaymentWindow has the required fields to do a payment
     */
    public function isValid() {

        foreach ($this->requiredFields as $field) {
            if(property_exists($this, $field) && null === $this->{$field}) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns URL to post to
     * @return string
     */
    public function getActionUrl() {
        return $this->actionUrl;
    }


    /**
     * Validate payment
     * @param array $fields
     * @return bool
     */
    public function validatePayment(array $fields) {

        $validFields = [];

        foreach ($fields as $key => $value) {
            if(strpos($key, 'onpay') !== false) {
                $validFields[$key] = $value;
            }
        }

        $verify = $validFields['onpay_hmac_sha1'];

        unset($validFields['onpay_hmac_sha1']);

        ksort($validFields);

        $queryString = http_build_query($validFields);
        $hmac = hash_hmac('sha1', $queryString, $this->secret);

        if($verify === $hmac) {
            return true;
        }

        return false;
    }
}
