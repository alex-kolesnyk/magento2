<?php
use Zend\Soap\Wsdl\ComplexTypeStrategy\ComplexTypeStrategyInterface as ComplexTypeStrategy;
/**
 * WSDL Generation class.
 *
 * @copyright {}
 */
class Mage_Webapi_Model_Soap_Wsdl extends \Zend\Soap\Wsdl
{
    /**
     * @var string|Zend\Uri\Uri
     */
    protected $_uri;

    /**
     * Constructor.
     * Save URI for targetNamespace generation.
     *
     * @param string $name
     * @param string|Zend\Uri\Uri $uri
     * @param Zend\Soap\Wsdl\ComplexTypeStrategy\ComplexTypeStrategyInterface $strategy
     * @param array $classMap
     */
    public function __construct($name, $uri, ComplexTypeStrategy $strategy = null, array $classMap = array())
    {
        $this->_uri = $uri;
        parent::__construct($name, $uri, $strategy, $classMap);
    }

    /**
     * Retrieve target namespace of the WSDL document.
     *
     * @return string
     */
    public function getTargetNamespace()
    {
        return sprintf('urn:Magento-%s', md5($this->_uri));
    }
}
