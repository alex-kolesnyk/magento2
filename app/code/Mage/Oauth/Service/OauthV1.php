<?php
/**
 * Web API Oauth Service.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/**
 * TODO: Need to refactor to reduce coupling
 * Class Mage_Oauth_Service_OauthV1
 */
class Mage_Oauth_Service_OauthV1 implements Mage_Oauth_Service_OauthInterfaceV1
{
    /**
     * Error code to error messages pairs
     *
     * @var array
     */
    protected $_errors = array(
        self::ERR_VERSION_REJECTED => 'version_rejected',
        self::ERR_PARAMETER_ABSENT => 'parameter_absent',
        self::ERR_PARAMETER_REJECTED => 'parameter_rejected',
        self::ERR_TIMESTAMP_REFUSED => 'timestamp_refused',
        self::ERR_NONCE_USED => 'nonce_used',
        self::ERR_SIGNATURE_METHOD_REJECTED => 'signature_method_rejected',
        self::ERR_SIGNATURE_INVALID => 'signature_invalid',
        self::ERR_CONSUMER_KEY_REJECTED => 'consumer_key_rejected',
        self::ERR_TOKEN_USED => 'token_used',
        self::ERR_TOKEN_EXPIRED => 'token_expired',
        self::ERR_TOKEN_REVOKED => 'token_revoked',
        self::ERR_TOKEN_REJECTED => 'token_rejected',
        self::ERR_VERIFIER_INVALID => 'verifier_invalid',
        self::ERR_PERMISSION_UNKNOWN => 'permission_unknown',
        self::ERR_PERMISSION_DENIED => 'permission_denied',
        self::ERR_METHOD_NOT_ALLOWED => 'method_not_allowed'
    );

    /**
     * TODO: Possible combine both the error objects
     * Error code to HTTP error code
     *
     * @var array
     */
    protected $_errorsToHttpCode = array(
        self::ERR_VERSION_REJECTED => self::HTTP_BAD_REQUEST,
        self::ERR_PARAMETER_ABSENT => self::HTTP_BAD_REQUEST,
        self::ERR_PARAMETER_REJECTED => self::HTTP_BAD_REQUEST,
        self::ERR_TIMESTAMP_REFUSED => self::HTTP_BAD_REQUEST,
        self::ERR_NONCE_USED => self::HTTP_UNAUTHORIZED,
        self::ERR_SIGNATURE_METHOD_REJECTED => self::HTTP_BAD_REQUEST,
        self::ERR_SIGNATURE_INVALID => self::HTTP_UNAUTHORIZED,
        self::ERR_CONSUMER_KEY_REJECTED => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_USED => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_EXPIRED => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_REVOKED => self::HTTP_UNAUTHORIZED,
        self::ERR_TOKEN_REJECTED => self::HTTP_UNAUTHORIZED,
        self::ERR_VERIFIER_INVALID => self::HTTP_UNAUTHORIZED,
        self::ERR_PERMISSION_UNKNOWN => self::HTTP_UNAUTHORIZED,
        self::ERR_PERMISSION_DENIED => self::HTTP_UNAUTHORIZED
    );


    /**
     * Possible time deviation for timestamp validation in sec.
     */
    const TIME_DEVIATION = 600;


    /**#@+
     * Request Types
     */
    const REQUEST_AUTHORIZE = 'authorize'; // display authorize form
    const REQUEST_TOKEN = 'token'; // ask for permanent credentials
    const REQUEST_RESOURCE = 'resource'; // ask for protected resource using permanent credentials

    /** @var  Mage_Oauth_Model_Consumer_Factory */
    private $_consumerFactory;

    /** @var  Mage_Oauth_Model_Nonce_Factory */
    private $_nonceFactory;

    /** @var  Mage_Oauth_Model_Token_Factory */
    private $_tokenFactory;

    /** @var  Mage_Core_Model_Translate */
    private $_translator;

    /** @var  Mage_Oauth_Helper_Data */
    protected $_helper;

    /** @var  Mage_Oauth_Model_Consumer */
    protected $_consumer;


    /**#@+
     * Required parameters for each token operation
     */
    protected $_requiredGeneric = array(
        "oauth_consumer_key",
        "oauth_signature",
        "oauth_signature_method",
        "oauth_nonce",
        "oauth_timestamp"
    );

    protected $_requiredAccess = array(
        "oauth_consumer_key",
        "oauth_signature",
        "oauth_signature_method",
        "oauth_nonce",
        "oauth_timestamp",
        "oauth_token",
        "oauth_verifier"
    );

    protected $_requiredValidate = array(
        "oauth_consumer_key",
        "oauth_signature",
        "oauth_signature_method",
        "oauth_nonce",
        "oauth_timestamp",
        "oauth_token"
    );

    /**
     * @param Mage_Oauth_Model_Consumer_Factory $consumerFactory
     * @param Mage_Oauth_Model_Nonce_Factory $nonceFactory
     * @param Mage_Oauth_Model_Token_Factory $tokenFactory
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_Translate $translator
     */
    public function __construct(
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
            $timestamp = (int)$timestamp;
            if ($timestamp <= 0 || $timestamp > (time() + self::TIME_DEVIATION)) {
                throw new Mage_Oauth_Exception(
                    $this->_translator->translate(
                        array('Incorrect timestamp value in the oauth_timestamp parameter.')
                    ),
                    self::ERR_TIMESTAMP_REFUSED);
            }

            $nonceObj = $this->_fetchNonce($nonce, $consumerId);

            if ($nonceObj->getConsumerId() == $consumerId) {
                throw new Mage_Oauth_Exception(
                    $this->_translator->translate(
                        array('The nonce is already being used by the consumer with id %s.', $consumerId)
                    ),
                    self::ERR_NONCE_USED);
            }

            $consumer = $this->_fetchConsumer($consumerId);

            if (!$consumer->getId()) {
                throw new Mage_Oauth_Exception(
                    $this->_translator->translate(
                        array('A consumer with id %s was not found.', $consumerId),
                        self::ERR_PARAMETER_REJECTED
                    ));
            }

            if ($nonceObj->getTimestamp() == $timestamp) {
                throw new Mage_Oauth_Exception(
                    $this->_translator->translate(
                        array('The nonce/timestamp combination has already been used.')
                    ), self::ERR_NONCE_USED);
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
     * @param array $consumerData
     * @return array
     * @throws Mage_Core_Exception
     * @throws Mage_Oauth_Exception
     */
    public function createConsumer($consumerData)
    {
        try {
            $consumer = $this->_consumerFactory->create($consumerData);
            $consumer->save();
            return array(
                'oauth_consumer_key' => $consumer->getKey(), 'oauth_consumer_secret' => $consumer->getSecret());
        } catch (Mage_Core_Exception $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new Mage_Oauth_Exception(
                $this->_translator->translate(array('Unexpected error. Unable to create OAuth Consumer account.')));
        }
    }

    /**
     * Issue a pre-authorization request token to the caller
     *
     * @param array $signedRequest input parameters such as consumer key, nonce, signature, signature method, timestamp,
     * oauth version, auth code
     * @return array output containing the request token key and secret
     * @throws Mage_Oauth_Exception
     */
    public function getRequestToken($signedRequest)
    {
        // validate input parameters as much as possible before making database calls
        $this->_validateVersionParam($signedRequest['oauth_version']);
        $this->_validateVerifierParam($signedRequest['oauth_verifier']);
        $this->_validateNonce(
            $signedRequest['nonce'],
            $signedRequest['consumer_key'],
            $signedRequest['oauth_timestamp']
        );

        $consumer = $this->_getConsumer($signedRequest['consumer_key']);
        $token = $this->_getToken($signedRequest['auth_code']);

        if ($token->getConsumerId() != $consumer->getId()) {
            throw new Mage_Oauth_Exception('', self::ERR_TOKEN_REJECTED);
        }
        if (Mage_Oauth_Model_Token::TYPE_AUTH_CODE != $token->getType()) {
            throw new Mage_Oauth_Exception('', self::ERR_TOKEN_REJECTED);
        }

        $this->_validateSignature(
            $signedRequest,
            $consumer->getSecret(),
            null,
            $signedRequest['http_method'],
            $signedRequest['request_url']
        );

        return $tokenObj->createRequestToken($consumer->getId(), null)->toString();
    }

    /**
     * TODO: log the request token in dev mode since its not persisted
     * Get an access token in exchange for a pre-authorized token
     * Perform appropriate parameter and signature validation
     *
     * @param array $requestArray
     * @return string
     * @throws Mage_Oauth_Exception
     */
    public function getAccessToken($requestArray)
    {
        // make generic validation of request parameters
        $this->_validateProtocolParams($requestArray, $this->_requiredAccess);

        $tokenParam = $requestArray['oauth_token'];
        $requestUrl = $requestArray['request_url'];
        $httpMethod = $requestArray['http_method'];
        $consumerKeyParam = $requestArray['oauth_consumer_key'];

        $this->_validateToken($tokenParam);

        $consumer = $this->_fetchConsumerByConsumerKey($consumerKeyParam);
        $token = $this->_fetchToken($tokenParam);

        if (!$this->_isTokenAssociatedToConsumer($token, $consumer)) {
            $this->_throwException('', self::ERR_TOKEN_REJECTED);
        }

        //The pre-auth token has a value of "request" in the type when it is requested and created initially
        //In this flow (token flow) the token has to be of type "request" else its marked as reused
        //TODO: Need to check security implication of this message
        if (Mage_Oauth_Model_Token::TYPE_REQUEST != $token->getType()) {
            $this->_throwException('', self::ERR_TOKEN_USED);
        }

        $this->_validateVerifierParam($requestArray['oauth_verifier'], $token->getVerifier());

        // Need to unset and remove unnecessary params from the requestTokenData array
        unset($requestArray['request_url']);
        unset($requestArray['http_method']);

        $this->_validateSignature(
            $requestArray,
            $consumer->getSecret(),
            $token->getSecret(),
            $httpMethod,
            $requestUrl
        );

        //Mark this token associated to the consumer as "access". Replace type with "access" from "request"
        return $token->convertToAccess()->toString();
    }

    /**
     * Validate a requested access token
     *
     * @param array $requestArray
     * @return boolean
     * @throws Mage_Oauth_Exception
     */
    public function validateAccessToken($requestArray)
    {
        $tokenParam = $requestArray['oauth_token'];
        $requestUrl = $requestArray['request_url'];
        $httpMethod = $requestArray['http_method'];
        $consumerKeyParam = $requestArray['oauth_consumer_key'];

        // make generic validation of request parameters
        $this->_validateProtocolParams($requestArray, $this->_requiredValidate);
        $this->_validateToken($tokenParam);

        $consumer = $this->_fetchConsumerByConsumerKey($consumerKeyParam);
        $token = $this->_fetchToken($tokenParam);

        //TODO: Verify if we need to check the association in token validation
        if (!$this->_isTokenAssociatedToConsumer($token, $consumer)) {
            $this->_throwException('', self::ERR_TOKEN_REJECTED);
        }

        if (Mage_Oauth_Model_Token::TYPE_ACCESS != $token->getType()) {
            $this->_throwException('', self::ERR_TOKEN_REJECTED);
        }
        if ($token->getRevoked()) {
            $this->_throwException('', self::ERR_TOKEN_REVOKED);
        }

        $this->_validateSignature(
            $requestArray,
            $consumer->getSecret(),
            $token->getSecret(),
            $httpMethod,
            $requestUrl
        );

        //If no exceptions were raised return as a valid token
        return true;
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

        $consumer = $this->_consumerFactory->create();
        $consumer->load($consumerKey, 'key');

        if (!$consumer->getId()) {
            throw new Mage_Oauth_Exception('', self::ERR_CONSUMER_KEY_REJECTED);
        }

        return $consumer;
    }

    /**
     * Load token object, validate it depending on request type, set access data and save
     *
     * @param string $tokenParam to load
     * @return Mage_Oauth_Model_Server
     * @throws Mage_Oauth_Exception
     */
    protected function _getToken($tokenParam)
    {
        if (strlen($tokenParam) != Mage_Oauth_Model_Token::LENGTH_TOKEN) {
            throw new Mage_Oauth_Exception('', self::ERR_TOKEN_REJECTED);
        }

        $token = $this->_tokenFactory->create();
        $token->load($tokenParam, 'token');

        if (!$token->getId()) {
            throw new Mage_Oauth_Exception('', self::ERR_TOKEN_REJECTED);
        }

        return $token;
    }

    /**
     * Validate 'oauth_verifier' parameter
     *
     * @param string $verifier
     * @param string $verifierFromToken
     * @throws Mage_Oauth_Exception
     */
    protected function _validateVerifierParam($verifier, $verifierFromToken = null)
    {
        if (!is_string($verifier)) {
            throw new Mage_Oauth_Exception('', self::ERR_VERIFIER_INVALID);
        }
        if (strlen($verifier) != Mage_Oauth_Model_Token::LENGTH_VERIFIER) {
            throw new Mage_Oauth_Exception('', self::ERR_VERIFIER_INVALID);
        }
        if (!is_null($verifierFromToken) && $verifierFromToken != $verifier) {
            throw new Mage_Oauth_Exception('', self::ERR_VERIFIER_INVALID);
        }
    }

    /**
     * Validate signature based on the signature method used
     *
     * @param array $params
     * @param string $consumerSecret
     * @param string $tokenSecret
     * @param string $httpMethod
     * @param string $requestUrl
     * @throws Mage_Oauth_Exception
     */
    protected function _validateSignature($params, $consumerSecret, $tokenSecret = null, $httpMethod, $requestUrl)
    {
        if (!in_array($params['oauth_signature_method'], self::getSupportedSignatureMethods())) {
            throw new Mage_Oauth_Exception('', self::ERR_SIGNATURE_METHOD_REJECTED);
        }

        $util = new Zend_Oauth_Http_Utility();
        $calculatedSign = $util->sign(
            $params,
            $params['oauth_signature_method'],
            $consumerSecret,
            $tokenSecret,
            $httpMethod,
            $requestUrl
        );

        if ($calculatedSign != $params['oauth_signature']) {
            $this->_throwException('Invalid signature.', self::ERR_SIGNATURE_INVALID);
        }
    }

    /**
     * Validate oauth version
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
     * //TODO: Resolve cyclomatic complexity
     * Validate request and header parameters
     *
     * @param $protocolParams
     * @param $requiredParams
     */
    protected function _validateProtocolParams($protocolParams, $requiredParams)
    {
        // validate version if specified
        if (isset($protocolParams['oauth_version']) && '1.0' != $protocolParams['oauth_version']) {
            $this->_throwException('', self::ERR_VERSION_REJECTED);
        }
        // required parameters validation. Default to generic params if no provided
        if (empty($requiredParams)) {
            $requiredParams = $this->_requiredGeneric;
        }
        $this->_checkRequiredParams($protocolParams, $requiredParams);

        // validate parameters type
        //TODO Need to verify if this is required
        foreach ($protocolParams as $paramName => $paramValue) {
            if (!is_string($paramValue)) {
                $this->_throwException($paramName, self::ERR_PARAMETER_REJECTED);
            }
        }
        // validate signature method
        if (!in_array($protocolParams['oauth_signature_method'], self::getSupportedSignatureMethods())) {
            $this->_throwException('', self::ERR_SIGNATURE_METHOD_REJECTED);
        }

        $consumer = $this->_fetchConsumerByConsumerKey($protocolParams['oauth_consumer_key']);

        $this->_validateNonce(
            $protocolParams['oauth_nonce'],
            $consumer->getId(),
            $protocolParams['oauth_timestamp']
        );
    }

    /**
     * Fetch a nonce based on a composite primary key consisting of the nonce string and a consumer id
     *
     * @param string $nonce - The nonce string
     * @param int $consumerId - A consumer id
     * @return Mage_Oauth_Model_Nonce
     */
    protected function _fetchNonce($nonce, $consumerId)
    {
        $nonceObj = $this->_nonceFactory->create()->loadByCompositeKey($nonce, $consumerId);
        return $nonceObj;
    }

    /**
     * Fetch consumer by consumer id
     *
     * @param $consumerId
     * @return Mage_Oauth_Model_Consumer
     */
    protected function _fetchConsumer($consumerId)
    {
        $this->_consumer = $this->_consumer == null ? $this->_consumerFactory->create()->load($consumerId)
            : $this->_consumer;
        if (!$this->_consumer->getId()) {
            $this->_throwException('', self::ERR_CONSUMER_KEY_REJECTED);
        }
        return $this->_consumer;
    }

    /**
     * Fetch consumer object by consumer key
     *
     * @param $key
     * @return Mage_Oauth_Model_Consumer
     */
    protected function _fetchConsumerByConsumerKey($key)
    {
        $this->_consumer = $this->_consumer == null ? $this->_consumerFactory->create()->load($key, 'key')
            : $this->_consumer;
        if (!$this->_consumer->getId()) {
            $this->_throwException('', self::ERR_CONSUMER_KEY_REJECTED);
        }
        return $this->_consumer;
    }

    /**
     * Validate Token param passed in user request
     * return back the token object
     *
     * @param $tokenParam
     * @throws Mage_Oauth_Exception
     */
    protected function _validateToken($tokenParam)
    {
        if (!is_string($tokenParam)) {
            $this->_throwException('', self::ERR_TOKEN_REJECTED);
        }
        if (strlen($tokenParam) != Mage_Oauth_Model_Token::LENGTH_TOKEN) {
            $this->_throwException('', self::ERR_TOKEN_REJECTED);
        }

    }

    /**
     * Check if token belongs to the same consumer
     *
     * @param $token Mage_Oauth_Model_Token
     * @param $consumer Mage_Oauth_Model_Consumer
     * @return boolean
     */
    protected function _isTokenAssociatedToConsumer($token, $consumer)
    {
        return $token->getConsumerId() == $consumer->getId();
    }


    /**
     * Throw OAuth exception
     *
     * @param string $message Exception message
     * @param int $code Exception code
     * @throws Mage_Oauth_Exception
     */
    protected function _throwException($message = '', $code = 0)
    {
        throw new Mage_Oauth_Exception($message, $code);
    }

    /**
     * //TODO : Can be cached if used more than once in a flow
     * Fetch token based on token param
     *
     * @param $tokenParam
     * @return Mage_Oauth_Model_Token
     */
    protected function _fetchToken($tokenParam)
    {
        return $this->_tokenFactory->create()->load($tokenParam, 'token');
    }

    /**
     * Get map of error code and error message
     *
     * @return array
     */
    public function getErrorMap()
    {
        return $this->_errors;
    }


    /**
     * Get map of error code and HTTP code
     *
     * @return array
     */
    public function getErrorToHttpCodeMap()
    {
        return $this->_errorsToHttpCode;
    }

    /**
     * Check if mandatory OAuth parameters are present
     *
     * @param $protocolParams
     * @param $requiredParams
     * @return mixed
     */
    protected function _checkRequiredParams($protocolParams, $requiredParams)
    {
        foreach ($requiredParams as $param) {
            if (!isset($protocolParams[$param])) {
                $this->_throwException($param, self::ERR_PARAMETER_ABSENT);
            }
        }
    }

}
