<?php
/**
 * Web API Oauth Service.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Oauth_Service_OauthV1 implements Mage_Oauth_Service_OauthInterfaceV1
{
    /**
     * Error code to error messages pairs
     *
     * @var array
     */
    protected $_errors = array(
        self::ERR_VERSION_REJECTED          => 'version_rejected',
        self::ERR_PARAMETER_ABSENT          => 'parameter_absent',
        self::ERR_PARAMETER_REJECTED        => 'parameter_rejected',
        self::ERR_TIMESTAMP_REFUSED         => 'timestamp_refused',
        self::ERR_NONCE_USED                => 'nonce_used',
        self::ERR_SIGNATURE_METHOD_REJECTED => 'signature_method_rejected',
        self::ERR_SIGNATURE_INVALID         => 'signature_invalid',
        self::ERR_CONSUMER_KEY_REJECTED     => 'consumer_key_rejected',
        self::ERR_TOKEN_USED                => 'token_used',
        self::ERR_TOKEN_EXPIRED             => 'token_expired',
        self::ERR_TOKEN_REVOKED             => 'token_revoked',
        self::ERR_TOKEN_REJECTED            => 'token_rejected',
        self::ERR_VERIFIER_INVALID          => 'verifier_invalid',
        self::ERR_PERMISSION_UNKNOWN        => 'permission_unknown',
        self::ERR_PERMISSION_DENIED         => 'permission_denied'
    );

    /**
     * Error code to HTTP error code
     *
     * @var array
     */
    protected $_errorsToHttpCode = array(
        self::ERR_VERSION_REJECTED          => self::HTTP_BAD_REQUEST,
        self::ERR_PARAMETER_ABSENT          => self::HTTP_BAD_REQUEST,
        self::ERR_PARAMETER_REJECTED        => self::HTTP_BAD_REQUEST,
        self::ERR_TIMESTAMP_REFUSED         => self::HTTP_BAD_REQUEST,
        self::ERR_NONCE_USED                => self::HTTP_UNAUTHORIZED,
        self::ERR_SIGNATURE_METHOD_REJECTED => self::HTTP_BAD_REQUEST,
        self::ERR_SIGNATURE_INVALID         => self::HTTP_UNAUTHORIZED,
        self::ERR_CONSUMER_KEY_REJECTED     => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_USED                => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_EXPIRED             => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_REVOKED             => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_REJECTED            => self::HTTP_UNAUTHORIZED,
        self::ERR_VERIFIER_INVALID          => self::HTTP_UNAUTHORIZED,
        self::ERR_PERMISSION_UNKNOWN        => self::HTTP_UNAUTHORIZED,
        self::ERR_PERMISSION_DENIED         => self::HTTP_UNAUTHORIZED
    );

    /**
     * Possible time deviation for timestamp validation in sec.
     */
    const TIME_DEVIATION = 600;

    /** @var  Mage_Oauth_Model_Consumer_Factory */
    private $_consumerFactory;

    /** @var  Mage_Oauth_Model_Nonce_Factory */
    private $_nonceFactory;

    /** @var  Mage_Core_Model_Translate */
    private $_translator;

    /** @var  Mage_Oauth_Helper_Data */
    protected $_helper;

    /**
     * @param Zend_Controller_Request_Http $request
     * @param Mage_Oauth_Model_Consumer_Factory $consumerFactory
     * @param Mage_Oauth_Model_Nonce_Factory $nonceFactory
     * @param Mage_Oauth_Model_Token_Factory $tokenFactory
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_Translate $translator
     */
    public function __construct(
        Zend_Controller_Request_Http $request,
        Mage_Oauth_Model_Consumer_Factory $consumerFactory,
        Mage_Oauth_Model_Nonce_Factory $nonceFactory,
        Mage_Oauth_Model_Token_Factory $tokenFactory,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_Translate $translator
    ) {
        $this->_consumerFactory = $consumerFactory;
        $this->_nonceFactory = $nonceFactory;
        $this->_tokenFactory = $tokenFactory;
        $this->_helper = $helperFactory->get('Mage_Oauth_Helper_Data');
        $this->_translator = $translator;
    }

    /**
     * Validate (oauth_nonce) Nonce string.
     *
     * @param string $nonce - Nonce string
     * @param int $consumerId - Consumer Id (Entity Id)
     * @param string|int $timestamp - Unix timestamp
     * @throws Mage_Oauth_Exception
     */
    protected function _validateNonce($nonce, $consumerId, $timestamp)
    {
        try {
            $timestamp = (int) $timestamp;
            if ($timestamp <= 0 || $timestamp > (time() + self::TIME_DEVIATION)) {
                throw new Mage_Oauth_Exception(
                    $this->_translator->translate(
                        array('Incorrect timestamp value in the oauth_timestamp parameter.')),
                    self::ERR_TIMESTAMP_REFUSED);
            }

            $nonceObj = $this->_nonceFactory->create();
            $nonceObj->load($nonce, 'nonce');

            if ($nonceObj->getConsumerId() == $consumerId) {
                throw new Mage_Oauth_Exception(
                    $this->_translator->translate(
                        array('The nonce is already being used by the consumer with id %s.', $consumerId)),
                    self::ERR_NONCE_USED);
            }

            $consumer = $this->_consumerFactory->create();
            $consumer->load($consumerId);
            if (!$consumer->getId()) {
                throw new Mage_Oauth_Exception(
                    $this->_translator->translate(
                        array('A consumer with id %s was not found.', $consumerId), self::ERR_PARAMETER_REJECTED));
            }

            if ($nonceObj->getTimestamp() == $timestamp) {
                throw new Mage_Oauth_Exception(
                    $this->_translator->translate(
                        array('The nonce/timestamp combination has already been used.')), self::ERR_NONCE_USED);
            }

            $nonceObj->setNonce($nonce)
                ->setConsumerId($consumerId)
                ->setTimestamp($timestamp)
                ->save();
        } catch (Mage_Oauth_Exception $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new Mage_Oauth_Exception(
                $this->_translator->translate(array('An error occurred validating the nonce.'))
            );
        }
    }

    /**
     * Retrieve array of supported signature methods
     *
     * @return array
     */
    public static function getSupportedSignatureMethods()
    {
        return array(self::SIGNATURE_SHA1, self::SIGNATURE_SHA256);
    }

    /**
     * Create a new consumer account when an Add-On is installed.
     *
     * @param array $addOnData
     * @return array
     * @throws Mage_Core_Exception
     * @throws Mage_Oauth_Exception
     */
    public function createConsumer($addOnData)
    {
        try {
            $consumer = $this->_consumerFactory->create(
                array('key' => $this->_helper->generateConsumerKey(),
                    'secret' => $this->_helper->generateConsumerSecret()));
            $consumer->save();
            $data['store_url'] = $addOnData['store_url'];
            $data['store_api_base_url'] = $addOnData['store_api_base_url'];
            $data['oauth_consumer_key'] = $consumer->getKey();
            $data['oauth_consumer_secret'] = $consumer->getSecret();
            // TODO: Execute HTTP POST to $addOnData['http_post_url'] containing the attributes returned in the array.
            return $data;
        } catch (Mage_Core_Exception $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new Mage_Oauth_Exception(
                $this->_translator->translate(array('Unexpected error. Unable to create Oauth consumer.')));
        }
    }

	/**
	 * Issue a pre-authorization request token to the caller
	 *
	 * @param array $request input parameters such as consumer key, nonce, signature, signature method, timestamp, oauth version, auth code
     * @return array output containing the request token key and secret
     * @throws Mage_Oauth_Exception
	 */
	public function authorize($request)
    {
        // validate input parameters as much as possible before making database calls
        $this->_validateVersionParam($request['oauth_version']);
        $this->_validateVerifierParam($request['oauth_verifier']);
        $this->_validateNonce($request['nonce'], $request['consumer_key'], $request['oauth_timestamp']);

        $consumer = $this->_getConsumer($request['consumer_key']);
        $token = $this->_getToken($request['auth_code']);

        if ($token->getConsumerId() != $consumer->getId()) {
            throw new Mage_Oauth_Exception('', self::ERR_TOKEN_REJECTED);
        }
        if (Mage_Oauth_Model_Token::TYPE_AUTH_CODE != $token->getType()) {
            throw new Mage_Oauth_Exception('', self::ERR_TOKEN_REJECTED);
        }

        $this->_validateSignature($request['signature'], $request['consumer_key'], $request['auth_code'],
            $request['oauth_version'], $request['signature_method'], $request['oauth_timestamp'],
            $request['oauth_nonce'], $consumer->getSecret(), null, $request['http_method'], $request['url']);
    }

    /**
     * Initialize consumer
     *
     * @param string $consumerKey to load
     * @return Mage_Oauth_Model_Consumer
     * @throws Mage_Oauth_Exception
     */
    protected function _getConsumer($consumerKey)
    {
        if (strlen($consumerKey) != Mage_Oauth_Model_Consumer::KEY_LENGTH) {
            throw new Mage_Oauth_Exception('', self::ERR_CONSUMER_KEY_REJECTED);
        }

        $consumerObj = $this->_consumerFactory->create();
        $consumerObj->load($consumerKey, 'key');

        if (!$consumerObj->getId()) {
            throw new Mage_Oauth_Exception('', self::ERR_CONSUMER_KEY_REJECTED);
        }

        return $consumerObj;
    }

    /**
     * Load token object, validate it depending on request type, set access data and save
     * @param string $token to load
     * @return Mage_Oauth_Model_Server
     * @throws Mage_Oauth_Exception
     */
    protected function _getToken($token)
    {
        if (strlen($token) != Mage_Oauth_Model_Token::LENGTH_TOKEN) {
            throw new Mage_Oauth_Exception('', self::ERR_TOKEN_REJECTED);
        }

        $tokenObj = $this->_tokenFactory->create();
        $tokenObj->load($token, 'token');

        if (!$tokenObj->getId()) {
            throw new Mage_Oauth_Exception('', self::ERR_TOKEN_REJECTED);
        }

        return $tokenObj;
    }

    /**
     * Validate 'oauth_verifier' parameter
     *
     * @param string $verifier
     * @throws Mage_Oauth_Exception
     */
    protected function _validateVerifierParam($verifier)
    {
        if (!is_string($verifier)) {
            throw new Mage_Oauth_Exception('', self::ERR_VERIFIER_INVALID);
        }
        if (strlen($verifier) != Mage_Oauth_Model_Token::LENGTH_VERIFIER) {
            throw new Mage_Oauth_Exception('', self::ERR_VERIFIER_INVALID);
        }
    }

    /**
     * Validate 'oauth_version' parameter
     *
     * @param string $version
     * @throws Mage_Oauth_Exception
     */
    protected function _validateVersionParam($version)
    {
        // validate version if specified
        if ('1.0' != $version) {
            throw new Mage_Oauth_Exception('', self::ERR_VERSION_REJECTED);
        }
    }

    /**
     * Validate signature
     *
     * @param
     * @throws Mage_Oauth_Exception
     */
    protected function _validateSignature($signature, $consumerKey, $token, $oauthVersion, $signatureMethod,
        $timestamp, $nonce, $consumerSecret, $tokenSecret = null, $httpMethod = null, $url = null)
    {
        if (!in_array($signatureMethod, self::getSupportedSignatureMethods())) {
            throw new Mage_Oauth_Exception('', self::ERR_SIGNATURE_METHOD_REJECTED);
        }

        $util = new Zend_Oauth_Http_Utility();

        $calculatedSignature = $util->sign(
            array(
                'oauth_consumer_key' => $consumerKey,
                'oauth_token' => $token,
                'oauth_version' => $oauthVersion,
                'oauth_timestamp' => $timestamp,
                'oauth_signature_method' => $signatureMethod,
                'oauth_nonce' => $nonce,
                'http_method' => $httpMethod,
                'url' => $url
            ),
            $signatureMethod,
            $consumerSecret,
            $tokenSecret,
            $httpMethod,
            $url
        );

        if ($calculatedSignature != $signature) {
            throw new Mage_Oauth_Exception(
                $this->_translator->translate(array('Invalid signature.')), self::ERR_SIGNATURE_INVALID);
        }
    }

}
