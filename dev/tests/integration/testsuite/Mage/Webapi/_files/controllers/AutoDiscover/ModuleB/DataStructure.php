<?php
/**
 * Data structure fixture.
 *
 * @copyright {}
 */
class Vendor_ModuleB_Webapi_ModuleB_DataStructure
{
    /**
     * String doc.
     * {callInfo:vendorModuleBCreate:requiredInput:conditionally}
     * {maxLength:255 chars.}
     *
     * @var string
     */
    public $stringParam;

    /**
     * Integer doc.
     * {min:10}{max:100}
     * {callInfo:vendorModuleBGet:returned:Conditionally}
     *
     * @var int {callInfo:allCallsExcept(vendorModuleBUpdate):requiredInput:yes}
     */
    public $integerParam = 5;

    /**
     * Optional bool doc.
     * {summary:this is summary}
     * {seeLink:http://google.com/:link title:link for}
     * {docInstructions:output:noDoc}
     *
     * @var bool
     */
    public $optionalBool = false;

    /**
     * {tagStatus:some status}
     *
     * @optional
     * @var Vendor_ModuleB_Webapi_ModuleB_Subresource_DataStructure
     */
    public $optionalComplexType;
}
