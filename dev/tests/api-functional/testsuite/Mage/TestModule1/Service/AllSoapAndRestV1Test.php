<?php
/**
 * Test AllSoapAndRestV1Test TestModule1
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_TestModule1_Service_AllSoapAndRestV1Test extends Magento_Test_TestCase_WebapiAbstract
{

    /**
     *  Test get item
     */
    public function testItem()
    {
        $itemId = 1;
        $serviceInfo = array(
            'rest' => array(
                'resourcePath' => '/V1/testmodule1/' . $itemId,
                'httpMethod' => 'GET'
            ),
            'soap' => array(
                'service' => 'testModule1AllSoapAndRest',
                'serviceVersion' => 'V1',
                'operation' => 'testModule1AllSoapAndRestItem'
            )
        );
        $requestData = array('id' => $itemId);
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($itemId, $item['id'], 'Item was retrieved unsuccessfully');
    }

    /**
     * Test fetching all items
     */
    public function testItems()
    {
        //TODO: Fix SOAP testModule1AllSoapAndRestItems operation
        $this->markTestIncomplete(
            'This test has not been implemented yet'
        );

        $itemArr = array(
            array(
                'id' => 1,
                'name' => 'testProduct1'
            ),
            array(
                'id' => 2,
                'name' => 'testProduct2'
            )
        );
        $serviceInfo = array(
            'rest' => array(
                'resourcePath' => '/V1/testmodule1',
                'httpMethod' => 'GET'
            ),
            'soap' => array(
                'service' => 'testModule1AllSoapAndRest',
                'serviceVersion' => 'V1',
                'operation' => 'testModule1AllSoapAndRestItems'
            )
        );
        $item = $this->_webApiCall($serviceInfo, null);
        $this->assertEquals($itemArr, $item, 'Items were not retrieved');
    }

    /**
     *  Test create item
     */
    public function testCreate()
    {
        $createdItemName = 'createdItemName';
        $serviceInfo = array(
            'rest' => array(
                'resourcePath' => '/V1/testmodule1',
                'httpMethod' => 'POST'
            ),
            'soap' => array(
                'service' => 'testModule1AllSoapAndRest',
                'serviceVersion' => 'V1',
                'operation' => 'testModule1AllSoapAndRestCreate'
            )
        );
        $requestData = array('name' => $createdItemName);
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($createdItemName, $item['name'], 'Item creation failed');
    }


    /**
     *  Test update item
     */
    public function testUpdate()
    {
        $itemId = 1;
        $serviceInfo = array(
            'rest' => array(
                'resourcePath' => '/V1/testmodule1/' . $itemId,
                'httpMethod' => 'PUT'
            ),
            'soap' => array(
                'service' => 'testModule1AllSoapAndRest',
                'serviceVersion' => 'V1',
                'operation' => 'testModule1AllSoapAndRestUpdate'
            )
        );
        $requestData = array('id' => $itemId, 'name' => 'testName');
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals('Updated' . $requestData['name'], $item['name'], 'Item update failed');
    }
}