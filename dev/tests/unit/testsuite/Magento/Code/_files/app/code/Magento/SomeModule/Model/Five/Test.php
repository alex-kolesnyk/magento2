<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\SomeModule\Model\Five;

require_once __DIR__ .  '/../Three/Test.php';
require_once __DIR__ .  '/../Proxy.php';

class Test extends \Magento\SomeModule\Model\Three\Test
{
    /**
     * @var \Magento\SomeModule\Model\Proxy
     */
    protected $_proxy;

    public function __construct(
        \Magento\SomeModule\Model\Proxy $proxy
    ) {
        parent::__construct($proxy);
    }

}