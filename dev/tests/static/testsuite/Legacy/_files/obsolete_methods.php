<?php
/**
 * Obsolete methods
 *
 * Format: array(<method_name = ''>[, <class_scope> = ''[, <replacement>]])
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
return array(
    array('__get', 'Varien_Object'),
    array('__set', 'Varien_Object'),
    array('_addMinimalPrice', 'Mage_Catalog_Model_Resource_Product_Collection'),
    array('_addTaxPercents', 'Mage_Catalog_Model_Resource_Product_Collection'),
    array('_afterSaveCommit', 'Mage_Core_Model_Abstract'),
    array('_afterSetConfig', 'Mage_Eav_Model_Entity_Abstract'),
    array('_aggregateByOrderCreatedAt', 'Mage_SalesRule_Model_Resource_Report_Rule'),
    array('_amountByCookies', 'Mage_Sendfriend_Model_Sendfriend'),
    array('_amountByIp', 'Mage_Sendfriend_Model_Sendfriend'),
    array('_applyCustomDesignSettings'),
    array('_applyDesign', 'Mage_Catalog_Model_Design'),
    array('_applyDesignRecursively', 'Mage_Catalog_Model_Design'),
    array('_avoidDoubleTransactionProcessing'),
    array('_beforeChildToHtml'),
    array('_calculatePrice', 'Mage_Sales_Model_Quote_Item_Abstract'),
    array('_canShowField', 'Mage_Backend_Block_System_Config_Form'),
    array('_canUseLocalModules'),
    array('_checkUrlSettings', 'Mage_Adminhtml_Controller_Action'),
    array('_collectOrigData', 'Mage_Catalog_Model_Resource_Abstract'),
    array('_decodeInput', 'Mage_Adminhtml_Catalog_ProductController'),
    array('_emailOrderConfirmation', 'Mage_Checkout_Model_Type_Abstract'),
    array('_escapeValue', 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract'),
    array('_getAddressTaxRequest', 'Mage_Tax_Model_Sales_Total_Quote_Shipping'),
    array('_getAggregationPerStoreView'),
    array('_getAttributeFilterBlockName', 'Mage_Catalog_Block_Layer_View'),
    array('_getAttributeFilterBlockName', 'Mage_CatalogSearch_Block_Layer'),
    array('_getAttributeFilterBlockName'),
    array('_getAvailable', 'Mage_GiftMessage_Model_Observer'),
    array('_getCacheId', 'Mage_Core_Model_App'),
    array('_getCacheKey', 'Mage_Catalog_Model_Layer_Filter_Price'),
    array('_getCacheTags', 'Mage_Core_Model_App'),
    array('_getChildHtml'),
    array('_getCollapseState', 'Mage_Backend_Block_System_Config_Form_Fieldset', '_isCollapseState'),
    array('_getCollectionNames', 'Mage_Adminhtml_Report_SalesController'),
    array('_getConnenctionType', 'Mage_Install_Model_Installer_Db'),
    array('_getDateFromToHtml', 'Mage_ImportExport_Block_Adminhtml_Export_Filter'),
    array('_getExistingBasePopularity'),
    array('_getFieldTableAlias', 'Mage_Newsletter_Model_Resource_Subscriber_Collection'),
    array('_getForeignKeyName', 'Varien_Db_Adapter_Pdo_Mysql'),
    array('_getGiftmessageSaveModel', 'Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid'),
    array('_getGlobalAggregation'),
    array('_getGroupByDateFormat', 'Mage_Log_Model_Resource_Visitor_Collection'),
    array('_getInputHtml', 'Mage_ImportExport_Block_Adminhtml_Export_Filter'),
    array('_getLabelForStore', 'Mage_Catalog_Model_Resource_Eav_Attribute'),
    array('_getMultiSelectHtml', 'Mage_ImportExport_Block_Adminhtml_Export_Filter'),
    array('_getNumberFromToHtml', 'Mage_ImportExport_Block_Adminhtml_Export_Filter'),
    array('_getPriceFilter', 'Mage_Catalog_Block_Layer_View'),
    array('_getProductQtyForCheck', 'Mage_CatalogInventory_Model_Observer'),
    array('_getRangeByType', 'Mage_Log_Model_Resource_Visitor_Collection'),
    array('_getRecentProductsCollection'),
    array('_getSelectHtml', 'Mage_ImportExport_Block_Adminhtml_Export_Filter'),
    array('_getSetData', 'Mage_Adminhtml_Block_Catalog_Product_Attribute_Set_Main'),
    array('_getSHAInSet', null, 'Mage_Ogone_Model_Api::getHash'),
    array('_getStoreTaxRequest', 'Mage_Tax_Model_Sales_Total_Quote_Shipping'),
    array('_importAddress', 'Mage_Paypal_Model_Api_Nvp'),
    array('_inheritDesign', 'Mage_Catalog_Model_Design'),
    array('_initOrder', 'Mage_Shipping_Block_Tracking_Popup'),
    array('_initShipment', 'Mage_Shipping_Block_Tracking_Popup'),
    array('_inludeControllerClass', null, '_includeControllerClass'),
    array('_isApplyDesign', 'Mage_Catalog_Model_Design'),
    array('_isApplyFor', 'Mage_Catalog_Model_Design'),
    array('_isPositiveDecimalNumber', 'Mage_Shipping_Model_Resource_Carrier_Tablerate'),
    array('_loadOldRates', 'Mage_Tax_Model_Resource_Setup'),
    array('_needSubtractShippingTax'),
    array('_needSubtractTax'),
    array('_needToAddDummy'),
    array('_needToAddDummyForShipment'),
    array('_parseDescription', 'Mage_Sales_Model_Order_Pdf_Items_Abstract'),
    array('_parseXmlTrackingResponse', 'Mage_Usa_Model_Shipping_Carrier_Fedex'),
    array('_prepareCondition', 'Mage_CatalogSearch_Model_Advanced'),
    array('_prepareConfigurableProductData', 'Mage_ImportExport_Model_Export_Entity_Product'),
    array('_prepareConfigurableProductPrice', 'Mage_ImportExport_Model_Export_Entity_Product'),
    array('_prepareOptionsForCart', 'Mage_Catalog_Model_Product_Type_Abstract'),
    array('_preparePackageTheme', 'Mage_Widget_Model_Widget_Instance'),
    array('_processItem', 'Mage_Weee_Model_Total_Quote_Weee'),
    array('_processShippingAmount'),
    array('_processValidateCustomer', 'Mage_Checkout_Model_Type_Onepage'),
    array('_putCustomerIntoQuote', 'Mage_Adminhtml_Model_Sales_Order_Create'),
    array('_quoteRow', 'Mage_Backup_Model_Resource_Db'),
    array('_recollectItem', 'Mage_Tax_Model_Sales_Total_Quote_Subtotal'),
    array('_resetItemPriceInclTax'),
    array('_saveCustomerAfterOrder', 'Mage_Adminhtml_Model_Sales_Order_Create'),
    array('_saveCustomers', 'Mage_Adminhtml_Model_Sales_Order_Create'),
    array('_sendUploadResponse', 'Mage_Adminhtml_CustomerController'),
    array('_sendUploadResponse', 'Mage_Adminhtml_Newsletter_SubscriberController'),
    array('_setAttribteValue'),
    array('_sort', 'Mage_Backend_Model_Config_Structure_Converter'),
    array('_updateMediaPathUseRewrites', 'Mage_Core_Model_Store', '_getMediaScriptUrl'),
    array('_usePriceIncludeTax'),
    array('addBackupedFilter'),
    array('addConfigField', 'Mage_Core_Model_Resource_Setup'),
    array('addConstraint', 'Varien_Db_Adapter_Pdo_Mysql'),
    array('addCustomersToAlertQueueAction'),
    array('addCustomerToSegments'),
    array('addGroupByTag', 'Mage_Tag_Model_Resource_Reports_Collection'),
    array('addKey', 'Varien_Db_Adapter_Pdo_Mysql'),
    array('addSaleableFilterToCollection'),
    array('addSearchQfFilter'),
    array('addStoresFilter', 'Mage_Poll_Model_Resource_Poll_Collection'),
    array('addSummary', 'Mage_Tag_Model_Resource_Tag'),
    array('addSummary', 'Mage_Tag_Model_Tag'),
    array('addTemplateData', 'Mage_Newsletter_Model_Queue'),
    array('addToAlersAction'),
    array('addToChildGroup'),
    array('addVisibleFilterToCollection', 'Mage_Catalog_Model_Product_Status'),
    array('addVisibleInCatalogFilterToCollection', null,
        '$collection->setVisibility(Mage_Catalog_Model_Product_Visibility->getVisibleInCatalogIds());'),
    array('addVisibleInSearchFilterToCollection', null,
        '$collection->setVisibility(Mage_Catalog_Model_Product_Visibility->getVisibleInSearchIds());'),
    array('addVisibleInSiteFilterToCollection', null,
        '$collection->setVisibility(Mage_Catalog_Model_Product_Visibility->getVisibleInSiteIds());'),
    array('addWishlistLink', 'Mage_Wishlist_Block_Links'),
    array('addWishListSortOrder', 'Mage_Wishlist_Model_Resource_Item_Collection'),
    array('aggregate', 'Mage_Tag_Model_Resource_Tag'),
    array('aggregate', 'Mage_Tag_Model_Tag'),
    array('applyDesign', 'Mage_Catalog_Model_Design'),
    array('authAdmin'),
    array('authFailed', null, 'Mage_Core_Helper_Http::failHttpAuthentication()'),
    array('authFrontend'),
    array('authValidate', null, 'Mage_Core_Helper_Http::getHttpAuthCredentials()'),
    array('bundlesAction', 'Mage_Adminhtml_Catalog_ProductController'),
    array('calcTaxAmount', 'Mage_Sales_Model_Quote_Item_Abstract'),
    array('canPrint', 'Mage_Checkout_Block_Onepage_Success'),
    array('catalogCategoryChangeProducts', 'Mage_Catalog_Model_Product_Flat_Observer'),
    array('catalogEventProductCollectionAfterLoad', 'Mage_GiftMessage_Model_Observer'),
    array('catalogProductLoadAfter', 'Mage_Bundle_Model_Observer'),
    array('chechAllowedExtension'),
    array('checkConfigurableProducts', 'Mage_Eav_Model_Resource_Entity_Attribute_Collection'),
    array('checkDatabase', 'Mage_Install_Model_Installer_Db'),
    array('checkDateTime', 'Mage_Core_Model_Date'),
    array('cleanDbRow', 'Mage_Core_Model_Resource'),
    array('cleanVarFolder', null, 'Varien_Io_File::rmdirRecursive()'),
    array('cleanVarSubFolders', null, 'glob() on Mage::getBaseDir(Mage_Core_Model_App_Dir::VAR_DIR)'),
    array('cloneIndexTable', 'Mage_Index_Model_Resource_Abstract'),
    array('convertOldTaxData', 'Mage_Tax_Model_Resource_Setup'),
    array('convertOldTreeToNew', 'Mage_Catalog_Model_Resource_Setup'),
    array('countChildren', 'Mage_Core_Block_Abstract'),
    array('crear'),
    array('createDirIfNotExists', null, 'mkdir()'),
    array('createOrderItem', 'Mage_CatalogInventory_Model_Observer'),
    array('debugRequest', 'Mage_Paypal_Model_Api_Standard'),
    array('deleteProductPrices', 'Mage_Catalog_Model_Resource_Product_Attribute_Backend_Tierprice'),
    array('display', 'Varien_Image_Adapter_Abstract', 'getImage()'),
    array('displayFullSummary', 'Mage_Tax_Model_Config'),
    array('displayTaxColumn', 'Mage_Tax_Model_Config'),
    array('displayZeroTax', 'Mage_Tax_Model_Config'),
    array('drawItem', 'Mage_Catalog_Block_Navigation'),
    array('dropKey', 'Varien_Db_Adapter_Pdo_Mysql'),
    array('editAction', 'Mage_Tag_CustomerController'),
    array('escapeJs', 'Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config'),
    array('exportOrderedCsvAction'),
    array('exportOrderedExcelAction'),
    array('fetchItemsCount', 'Mage_Wishlist_Model_Resource_Wishlist'),
    array('fetchRuleRatesForCustomerTaxClass'),
    array('forsedSave'),
    array('generateBlocks', null, 'generateElements()'),
    array('getAccount', 'Mage_GoogleAnalytics_Block_Ga'),
    array('getAclAssert', 'Mage_Admin_Model_Config'),
    array('getAclPrivilegeSet', 'Mage_Admin_Model_Config'),
    array('getAclResourceList', 'Mage_Admin_Model_Config'),
    array('getAclResourceTree', 'Mage_Admin_Model_Config'),
    array('getAddNewButtonHtml', 'Mage_Adminhtml_Block_Catalog_Product'),
    array('getAddToCartItemUrl', 'Mage_Wishlist_Block_Customer_Sidebar'),
    array('getAddToCartUrlBase64', null, '_getAddToCartUrl'),
    array('getAllEntityIds', 'Mage_Rss_Model_Resource_Order'),
    array('getAllEntityTypeCommentIds', 'Mage_Rss_Model_Resource_Order'),
    array('getAllOrderEntityIds', 'Mage_Rss_Model_Resource_Order'),
    array('getAllOrderEntityTypeIds', 'Mage_Rss_Model_Resource_Order'),
    array('getAnonSuffix'),
    array('getAttributesJson', 'Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config', 'getAttributes'),
    array('getBaseTaxAmount', 'Mage_Sales_Model_Quote_Item_Abstract'),
    array('getCheckoutMehod', 'Mage_Checkout_Model_Type_Onepage'),
    array('getChildGroup', null, 'Mage_Core_Block_Abstract::getGroupChildNames()'),
    array('getConfig', 'Mage_Eav_Model_Entity_Attribute_Abstract'),
    array('getCustomerData', 'Mage_Adminhtml_Block_Sales_Order_Create_Form_Account'),
    array('getDataForSave', 'Mage_Wishlist_Model_Item'),
    array('getDebug', 'Mage_Ogone_Model_Api'),
    array('getDebug', 'Mage_Paypal_Model_Api_Abstract'),
    array('getDefaultBasePath', 'Mage_Core_Model_Store'),
    array('getDirectOutput', 'Mage_Core_Model_Layout'),
    array('getDistroServerVars', 'Mage_Core_Model_Config', 'getDistroBaseUrl'),
    array('getEntityIdsToIncrementIds', 'Mage_Rss_Model_Resource_Order'),
    array('getEntityTypeIdsToTypes', 'Mage_Rss_Model_Resource_Order'),
    array('getFacets'),
    array('getFallbackTheme'),
    array('getFormated', null, 'getFormated(true) -> format(\'html\'), getFormated() -> format(\'text\')'),
    array('getFormObject', 'Mage_Adminhtml_Block_Widget_Form'),
    array('getGiftmessageHtml', 'Mage_Adminhtml_Block_Sales_Order_View_Tab_Info'),
    array('getHtmlFormat', 'Mage_Customer_Model_Address_Abstract'),
    array('getIsActiveAanalytics', null, 'getOnsubmitJs'),
    array('getIsAjaxRequest', 'Mage_Core_Model_Translate_Inline'),
    array('getIsEngineAvailable'),
    array('getIsGlobal', 'Mage_Eav_Model_Entity_Attribute_Abstract'),
    array('getIsInStock', 'Mage_Checkout_Block_Cart_Item_Renderer'),
    array('getItemRender', 'Mage_Checkout_Block_Cart_Abstract'),
    array('getJoinFlag', 'Mage_Tag_Model_Resource_Customer_Collection'),
    array('getJoinFlag', 'Mage_Tag_Model_Resource_Product_Collection'),
    array('getJoinFlag', 'Mage_Tag_Model_Resource_Tag_Collection'),
    array('getKeyList', 'Varien_Db_Adapter_Pdo_Mysql'),
    array('getLanguages', 'Mage_Install_Block_Begin'),
    array('getLayoutFilename', null, 'getFilename'),
    array('getLifeTime', 'Mage_Core_Model_Resource_Session'),
    array('getLocaleBaseDir', 'Mage_Core_Model_Design_Package'),
    array('getMail', 'Mage_Newsletter_Model_Template'),
    array('getMaxQueryLenght'),
    array('getMenuItemLabel', 'Mage_Admin_Model_Config'),
    array('getMergedCssUrl'),
    array('getMergedJsUrl'),
    array('getMinQueryLenght'),
    array('getNeedUsePriceExcludeTax', null, 'Mage_Tax_Model_Config::priceIncludesTax()'),
    array('getOneBalanceTotal'),
    array('getOption', 'Mage_Captcha_Helper_Data', 'Mage_Core_Model_Dir::getDir()'),
    array('setStore', 'Mage_Captcha_Helper_Data'),
    array('getStore', 'Mage_Captcha_Helper_Data'),
    array('setWebsite', 'Mage_Captcha_Helper_Data'),
    array('getWebsite', 'Mage_Captcha_Helper_Data'),
    array('setConfig', 'Mage_Captcha_Helper_Data'),
    array('getConfig', 'Mage_Captcha_Helper_Data'),
    array('getOptions', 'Mage_Core_Model_Config'),
    array('getOrderHtml', 'Mage_GoogleAnalytics_Block_Ga'),
    array('getOrderId', 'Mage_Checkout_Block_Onepage_Success'),
    array('getOrderId', 'Mage_Shipping_Block_Tracking_Popup'),
    array('getOriginalHeigh', null, 'getOriginalHeight'),
    array('getParentProductIds', 'Mage_Catalog_Model_Resource_Product'),
    array('getPriceFormatted', 'Mage_Adminhtml_Block_Customer_Edit_Tab_View_Sales'),
    array('getPrices', 'Mage_Bundle_Model_Product_Price'),
    array('getPricesDependingOnTax', 'Mage_Bundle_Model_Product_Price'),
    array('getPrintUrl', 'Mage_Checkout_Block_Onepage_Success'),
    array('getPrintUrl', 'Mage_Sales_Block_Order_Info'),
    array('getProductCollection', 'Mage_Wishlist_Helper_Data'),
    array('getProductCollection', 'Mage_Wishlist_Model_Wishlist'),
    array('getProductsNotInStoreIds'),
    array('getProfile', 'Varien_Convert_Container_Abstract'),
    array('getQuoteItem', 'Mage_Catalog_Model_Product_Option_Type_Default'),
    array('getQuoteItemOption', 'Mage_Catalog_Model_Product_Option_Type_Default'),
    array('getQuoteOrdersHtml', 'Mage_GoogleAnalytics_Block_Ga'),
    array('getResourceConfig', 'Mage_Config_Model_Config', 'Mage_Config_Model_Config_Resource::getResourceConfig'),
    array('getResourceConnectionConfig', 'Mage_Config_Model_Config',
        'Mage_Config_Model_Config_Resource::getResourceConnectionConfig'
    ),
    array('getResourceConnectionModel', 'Mage_Config_Model_Config',
        'Mage_Config_Model_Config_Resource::getResourceConnectionModel'
    ),
    array('getResourceTypeConfig', 'Mage_Config_Model_Config',
        'Mage_Config_Model_Config_Resource::getResourceTypeConfig'
    ),
    array('getRemoveItemUrl', 'Mage_Wishlist_Block_Customer_Sidebar'),
    array('getReorderUrl', 'Mage_Sales_Block_Order_Info'),
    array('getRowId', 'Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid'),
    array('getRowId', 'Mage_Adminhtml_Block_Widget_Grid'),
    array('getSaveTemplateFlag', 'Mage_Newsletter_Model_Queue'),
    array('getSelectionFinalPrice', 'Mage_Bundle_Model_Product_Price'),
    array('getSecure', 'Mage_Core_Model_Url', 'isSecure'),
    array('getSecure', 'Mage_Backend_Model_Url', 'isSecure'),
    array('getShipId', 'Mage_Shipping_Block_Tracking_Popup'),
    array('getSortedChildren', null, 'getChildNames'),
    array('getSortedChildBlocks', null, 'getChildNames() + $this->getLayout()->getBlock($name)'),
    array('getStatrupPageUrl'),
    array('getStoreButtonsHtml', 'Mage_Backend_Block_System_Config_Tabs'),
    array('getStoreCurrency', 'Mage_Sales_Model_Order'),
    array('getStoreSelectOptions', 'Mage_Backend_Block_System_Config_Tabs'),
    array('getSuggestedZeroDate'),
    array('getSuggestionsByQuery'),
    array('getSysTmpDir'),
    array('getTablePrefix', 'Mage_Core_Model_Config', 'Mage_Core_Model_Config_Resource::getTablePrefix'),
    array('getTaxAmount', 'Mage_Sales_Model_Quote_Item_Abstract'),
    array('getTaxRatesByProductClass', null, '_getAllRatesByProductClass'),
    array('getTemplateFilename', null, 'getFilename'),
    array('getTempVarDir', 'Mage_Core_Model_Config', 'Mage_Core_Model_Dir::getDir()'),
    array('getTotalModels', 'Mage_Sales_Model_Quote_Address'),
    array('getTrackId', 'Mage_Shipping_Block_Tracking_Popup'),
    array('getTrackingInfoByOrder', 'Mage_Shipping_Block_Tracking_Popup'),
    array('getTrackingInfoByShip', 'Mage_Shipping_Block_Tracking_Popup'),
    array('getTrackingInfoByTrackId', 'Mage_Shipping_Block_Tracking_Popup'),
    array('getTrackingPopUpUrlByOrderId', null, 'getTrackingPopupUrlBySalesModel'),
    array('getTrackingPopUpUrlByShipId', null, 'getTrackingPopupUrlBySalesModel'),
    array('getTrackingPopUpUrlByTrackId', null, 'getTrackingPopupUrlBySalesModel'),
    array('getUseCacheFilename', 'Mage_Core_Model_App'),
    array('getValidator', 'Mage_SalesRule_Model_Observer'),
    array('getValidatorData', 'Mage_Core_Model_Session_Abstract', 'use _getSessionEnvironment method'),
    array('getValueTable'),
    array('getVarDir', 'Mage_Core_Model_Config', 'Mage_Core_Model_Dir::getDir()'),
    array('getViewOrderUrl', 'Mage_Checkout_Block_Onepage_Success'),
    array('getWidgetSupportedBlocks', 'Mage_Widget_Model_Widget_Instance'),
    array('getWidgetSupportedTemplatesByBlock', 'Mage_Widget_Model_Widget_Instance'),
    array('hasItems', 'Mage_Wishlist_Helper_Data'),
    array('htmlEscape', null, 'escapeHtml'),
    array('imageAction', 'Mage_Catalog_ProductController'),
    array('importFromTextArray'),
    array('initLabels', 'Mage_Catalog_Model_Resource_Eav_Attribute'),
    array('insertProductPrice', 'Mage_Catalog_Model_Resource_Product_Attribute_Backend_Tierprice'),
    array('isAllowedGuestCheckout', 'Mage_Sales_Model_Quote'),
    array('isAutomaticCleaningAvailable', 'Varien_Cache_Backend_Eaccelerator'),
    array('isCheckoutAvailable', 'Mage_Checkout_Model_Type_Multishipping'),
    array('isFulAmountCovered'),
    array('isLeyeredNavigationAllowed'),
    array('isReadablePopupObject'),
    array('isTemplateAllowedForApplication'),
    array('loadLabel', 'Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute'),
    array('loadParentProductIds', 'Mage_Catalog_Model_Product'),
    array('loadPrices', 'Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute'),
    array('loadProductPrices', 'Mage_Catalog_Model_Resource_Product_Attribute_Backend_Tierprice'),
    array('lockOrderInventoryData', 'Mage_CatalogInventory_Model_Observer'),
    array('logEncryptionKeySave'),
    array('logInvitationSave'),
    array('mergeFiles', 'Mage_Core_Helper_Data'),
    array('order_success_page_view', 'Mage_GoogleAnalytics_Model_Observer'),
    array('orderedAction', 'Mage_Adminhtml_Report_ProductController'),
    array('parseDateTime', 'Mage_Core_Model_Date'),
    array('postDispatchMyAccountSave'),
    array('postDispatchSystemImportExportRun'),
    array('prepareCacheId', 'Mage_Core_Model_App'),
    array('prepareGoogleOptimizerScripts'),
    array('preprocess', 'Mage_Newsletter_Model_Template'),
    array('processBeacon'),
    array('processBeforeVoid', 'Mage_Payment_Model_Method_Abstract'),
    array('processSubst', 'Mage_Core_Model_Store'),
    array('productEventAggregate'),
    array('push', 'Mage_Catalog_Model_Product_Image'),
    array('rebuildCategoryLevels', 'Mage_Catalog_Model_Resource_Setup'),
    array('regenerateSessionId', 'Mage_Core_Model_Session_Abstract'),
    array('refundOrderItem', 'Mage_CatalogInventory_Model_Observer'),
    array('renderView', null, 'Mage_Core_Block_Template::_toHtml()'),
    array('removeCustomerFromSegments'),
    array('revalidateCookie', 'Mage_Core_Model_Session_Abstract_Varien'),
    array('sales_order_afterPlace'),
    array('sales_quote_address_discount_item'),
    array('salesOrderPaymentPlaceEnd'),
    array('saveRow__OLD'),
    array('saveAction', 'Mage_Tag_CustomerController'),
    array('saveSegmentCustomersFromSelect'),
    array('send', 'Mage_Newsletter_Model_Template'),
    array('sendNewPasswordEmail'),
    array('setAnonSuffix'),
    array('setAttributeSetExcludeFilter', 'Mage_Eav_Model_Resource_Entity_Attribute_Collection'),
    array('setBlockAlias'),
    array('setCustomerId', 'Mage_Customer_Model_Resource_Address'),
    array('setIsAjaxRequest', 'Mage_Core_Model_Translate_Inline'),
    array('setJoinFlag', 'Mage_Tag_Model_Resource_Customer_Collection'),
    array('setJoinFlag', 'Mage_Tag_Model_Resource_Product_Collection'),
    array('setJoinFlag', 'Mage_Tag_Model_Resource_Tag_Collection'),
    array('setOption', 'Mage_Captcha_Helper_Data'),
    array('setOrderId', 'Mage_Shipping_Block_Tracking_Popup'),
    array('setNeedUsePriceExcludeTax', null, 'Mage_Tax_Model_Config::setPriceIncludesTax()'),
    array('setScriptPath', 'Mage_Core_Block_Template'),
    array('setVarSubFolders'),
    array('setWatermarkHeigth', null, 'setWatermarkHeight'),
    array('getWatermarkHeigth', null, 'getWatermarkHeight'),
    array('setOptions', 'Mage_Core_Model_Config'),
    array('setParentBlock'),
    array('setProfile', 'Varien_Convert_Container_Abstract'),
    array('setSaveTemplateFlag', 'Mage_Newsletter_Model_Queue'),
    array('setScriptPath'),
    array('setShipId', 'Mage_Shipping_Block_Tracking_Popup'),
    array('setTaxGroupFilter'),
    array('setTrackId', 'Mage_Shipping_Block_Tracking_Popup'),
    array('shaCrypt', null, 'Mage_Ogone_Model_Api::getHash'),
    array('shaCryptValidation', null, 'Mage_Ogone_Model_Api::getHash'),
    array('shouldCustomerHaveOneBalance'),
    array('shouldShowOneBalance'),
    array('substDistroServerVars', 'Mage_Core_Model_Config'),
    array('sortChildren'),
    array('toOptionArray', 'Mage_Cms_Model_Resource_Page_Collection'),
    array('toOptionArray', 'Mage_Sendfriend_Model_Sendfriend'),
    array('truncate', 'Varien_Db_Adapter_Pdo_Mysql'),
    array('unsetBlock'),
    array('unsetJoinFlag', 'Mage_Tag_Model_Resource_Customer_Collection'),
    array('unsetJoinFlag', 'Mage_Tag_Model_Resource_Product_Collection'),
    array('unsetJoinFlag', 'Mage_Tag_Model_Resource_Tag_Collection'),
    array('useValidateRemoteAddr', 'Mage_Core_Model_Session_Abstract'),
    array('useValidateHttpVia', 'Mage_Core_Model_Session_Abstract'),
    array('useValidateHttpXForwardedFor', 'Mage_Core_Model_Session_Abstract'),
    array('useValidateHttpUserAgent', 'Mage_Core_Model_Session_Abstract'),
    array('updateCofigurableProductOptions', 'Mage_Weee_Model_Observer', 'updateConfigurableProductOptions'),
    array('updateTable', 'Mage_Core_Model_Resource_Setup'),
    array('applyAllUpdates', 'Mage_Core_Model_Resource_Setup'),
    array('applyAllDataUpdates', 'Mage_Core_Model_Resource_Setup'),
    array('urlEscape', null, 'escapeUrl'),
    array('validateDataArray', 'Varien_Convert_Container_Abstract'),
    array('validateFile', 'Mage_Core_Model_Design_Package'),
    array('validateOrder', 'Mage_Checkout_Model_Type_Onepage'),
    array('prepareAttributesForSave', 'Mage_ImportExport_Model_Import_Entity_Product'),
    array('fetchUpdatesByHandle', 'Mage_Core_Model_Resource_Layout',
        'Mage_Core_Model_Resource_Layout_Update'),
    array('getElementClass', 'Mage_Core_Model_Layout_Update'),
    array('addUpdate', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('asArray', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('asString', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('addHandle', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('removeHandle', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('getHandles', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('addPageHandles', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('getPageHandleParents', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('pageHandleExists', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('getPageHandles', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('getPageHandlesHierarchy', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('getPageHandleLabel', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('getPageHandleType', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('load', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('asSimplexml', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('getFileLayoutUpdatesXml', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('getContainers', 'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Layout_Merge'),
    array('getDataMaxSize'),
    array('getDataMaxSizeInBytes', 'Mage_Adminhtml_Block_Media_Uploader', 'Magento_File_Size::getMaxFileSize()'),
    array('getPostMaxSize', 'Mage_Adminhtml_Block_Media_Uploader', 'Magento_File_Size::getPostMaxSize()'),
    array('getUploadMaxSize', 'Mage_Adminhtml_Block_Media_Uploader', 'Magento_File_Size::getUploadMaxSize()'),
    array('_getBytesIniValue', 'Mage_Catalog_Model_Product_Option_Type_File'),
    array('_getUploadMaxFilesize', 'Mage_Catalog_Model_Product_Option_Type_File'),
    array('_bytesToMbytes', 'Mage_Catalog_Model_Product_Option_Type_File'),
    array('getMaxUploadSize', 'Mage_ImportExport_Helper_Data', 'getMaxUploadSizeMessage'),
    array('prepareRedirect', 'Mage_Core_Controller_Varien_Exception'),
    array('getOptions', 'Mage_Core_Model_Design_Source_Design',
        'Mage_Core_Model_Theme::getThemeCollectionOptionArray'),
    array('getThemeOptions', 'Mage_Core_Model_Design_Source_Design',
        'Mage_Core_Model_Theme::getThemeCollectionOptionArray'),
    array('isThemeCompatible', 'Mage_Core_Model_Design_Package', 'Mage_Core_Model_Theme::isThemeCompatible'),
    array('setPackageTheme', 'Mage_Widget_Model_Widget_Instance', 'setThemeId'),
    array('getPackageTheme', 'Mage_Widget_Model_Widget_Instance', 'getThemeId'),
    array('getPackage', 'Mage_Widget_Model_Widget_Instance'),
    array('getTheme', 'Mage_Widget_Model_Widget_Instance'),
    array('_parsePackageTheme', 'Mage_Widget_Model_Widget_Instance'),
    array('isVerbose', 'Magento_Shell'),
    array('setVerbose', 'Magento_Shell'),
    array('output', 'Magento_Shell'),
    array('init', 'Mage'),
    array('run', 'Mage'),
    array('loadEventObservers', 'Mage_Core_Model_Config'),
    array('getEvents', 'Mage'),
    array('addObserver', 'Mage'),
    array('getModelClassName', 'Mage_Core_Model_Config'),
    array('getBlockClassName', 'Mage_Core_Model_Config'),
    array('getHelperClassName', 'Mage_Core_Model_Config'),
    array('getResourceModelClassName', 'Mage_Core_Model_Config'),
    array('getInitParam', 'Mage_Core_Model_App'),
    array('composeLocaleHierarchy', 'Mage_Core_Helper_Translate'),
);
