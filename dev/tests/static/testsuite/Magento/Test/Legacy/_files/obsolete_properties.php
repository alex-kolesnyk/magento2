<?php
/**
 * Obsolete class attributes
 *
 * Format: array(<attribute_name>[, <class_scope> = ''[, <replacement>]])
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
return array(
    array('_addresses', 'Magento\Customer\Model\Customer'),
    array('_addMinimalPrice', 'Magento\Catalog\Model\Resource\Product\Collection'),
    array('_alias', 'Magento\Core\Block\AbstractBlock'),
    array('_anonSuffix'),
    array('_appMode', 'Magento\App\ObjectManager\ConfigLoader'),
    array('_baseDirCache', 'Magento\Core\Model\Config'),
    array('_cacheConf'),
    array('_canUseLocalModules'),
    array('_checkedProductsQty', 'Magento\CatalogInventory\Model\Observer'),
    array('_children', 'Magento\Core\Block\AbstractBlock'),
    array('_childrenHtmlCache', 'Magento\Core\Block\AbstractBlock'),
    array('_childGroups', 'Magento\Core\Block\AbstractBlock'),
    array('_combineHistory'),
    array('_config', 'Magento\Core\Model\Design\Package'),
    array('_config', 'Magento\Logger', '_dirs'),
    array('_config', 'Magento\Core\Model\Resource\Setup'),
    array('_configModel', 'Magento\Backend\Model\Menu\AbstractDirector'),
    array('_configuration', 'Magento\Index\Model\Lock\Storage', '_dirs'),
    array('_connectionConfig', 'Magento\Core\Model\Resource\Setup'),
    array('_connectionTypes', 'Magento\App\Resource'),
    array('_currency', 'Magento\GoogleCheckout\Model\Api\Xml\Checkout'),
    array('_currencyNameTable'),
    array('_customEtcDir', 'Magento\Core\Model\Config'),
    array('_defaultTemplates', 'Magento\Email\Model\Template'),
    array('_designProductSettingsApplied'),
    array('_directOutput', 'Magento\Core\Model\Layout'),
    array('_dirs', 'Magento\App\Resource'),
    array('_distroServerVars'),
    array('_entityIdsToIncrementIds'),
    array('entities', 'Magento\App\Resource'),
    array('_entityTypeIdsToTypes'),
    array('_factory', 'Magento\Backend\Model\Menu\Config'),
    array('_factory', 'Magento\Backend\Model\Menu\AbstractDirector', '_commandFactory'),
    array('_isAnonymous'),
    array('_isFirstTimeProcessRun', 'Magento\SalesRule\Model\Validator'),
    array('_isRuntimeValidated', 'Magento\ObjectManager\Config\Reader\Dom'),
    array('_loadDefault', 'Magento\Core\Model\Resource\Store\Collection'),
    array('_loadDefault', 'Magento\Core\Model\Resource\Store\Group\Collection'),
    array('_loadDefault', 'Magento\Core\Model\Resource\Website\Collection'),
    array('_mapper', 'Magento\ObjectManager\Config\Reader\Dom'),
    array('_menu', 'Magento\Backend\Model\Menu\Builder'),
    array('_modulesReader', 'Magento\App\ObjectManager\ConfigLoader'),
    array('_moduleReader', 'Magento\Backend\Model\Menu\Config'),
    array('_option', 'Magento\Captcha\Helper\Data', '_dirs'),
    array('_options', 'Magento\Core\Model\Config', 'Magento\Filesystem'),
    array('_optionsMapping', null, '\Magento\Filesystem::getPath($nodeKey)'),
    array('_order', 'Magento\Checkout\Block\Onepage\Success'),
    array('_order_id'),
    array('_parent', 'Magento\Core\Block\AbstractBlock'),
    array('_parentBlock', 'Magento\Core\Block\AbstractBlock'),
    array('_persistentCustomerGroupId'),
    array('_queriesHooked', 'Magento\Core\Model\Resource\Setup'),
    array('_ratingOptionTable', 'Magento\Rating\Model\Resource\Rating\Option\Collection'),
    array('_readerFactory', 'Magento\App\ObjectManager\ConfigLoader'),
    array('_resourceConfig', 'Magento\Core\Model\Resource\Setup'),
    array('_saveTemplateFlag', 'Magento\Newsletter\Model\Queue'),
    array('_searchTextFields'),
    array('_setAttributes', 'Magento\Catalog\Model\Product\Type\AbstractType'),
    array('_skipFieldsByModel'),
    array('_ship_id'),
    array('_shipTable', 'Magento\Shipping\Model\Resource\Carrier\Tablerate\Collection'),
    array('_showTemplateHints', 'Magento\View\Block\Template',
        'Magento\Core\Model\TemplateEngine\Plugin\DebugHints'),
    array('_showTemplateHintsBlocks', 'Magento\View\Block\Template',
        'Magento\Core\Model\TemplateEngine\Plugin\DebugHints'),
    array('_sortedChildren'),
    array('_sortInstructions'),
    array('_storeFilter', 'Magento\Catalog\Model\Product\Type\AbstractType'),
    array('_substServerVars'),
    array('_track_id'),
    array('_varSubFolders', null, 'Magento\Filesystem'),
    array('_viewDir', 'Magento\View\Block\Template', '_dirs'),
    array('decoratedIsFirst', null, 'getDecoratedIsFirst'),
    array('decoratedIsEven', null, 'getDecoratedIsEven'),
    array('decoratedIsOdd', null, 'getDecoratedIsOdd'),
    array('decoratedIsLast', null, 'getDecoratedIsLast'),
    array('static', 'Magento\Email\Model\Template\Filter'),
    array('_useAnalyticFunction'),
    array('_defaultIndexer', 'Magento\CatalogInventory\Model\Resource\Indexer\Stock'),
    array('_engine', 'Magento\CatalogSearch\Model\Resource\Fulltext'),
    array('_allowedAreas', 'Magento\Core\Model\Config'),
    array('_app', 'Magento\Core\Block\AbstractBlock'),
    array('_app', 'Magento\View\Block\Template'),
    array('_config', 'Magento\Backend\Helper\Data'),
    array('_defaultAreaFrontName', 'Magento\Backend\Helper\Data'),
    array('_areaFrontName', 'Magento\Backend\Helper\Data'),
    array('_backendFrontName', 'Magento\Backend\Helper\Data'),
    array('_app', 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency'),
    array('_enginePool', '\Magento\View\Block\Template\Context', '_engineFactory'),
    array('_fileHandler', '\Magento\Sitemap\Model\Sitemap', '_stream'),
    array('_fileIo', '\Magento\Theme\Model\Uploader\Service', '_filesystem'),
    array('_streamFactory', '\Magento\Core\Model\File\Storage\Config', '_filesystem'),
    array('_streamFactory', '\Magento\Core\Model\File\Storage\Synchronization', '_filesystem'),
    array('_allowedFormats', '\Magento\Core\Helper\Data', '\Magento\Core\Model\Locale'),
    array('types', '\Magento\Core\Model\Theme'),
    array('_collectionFactory', '\Magento\Install\Controller\Action', 'themeProvider'),
    array('_collectionFactory', '\Magento\Theme\Model\Config\Customization', 'themeProvider'),
    array('_message', 'Magento\Checkout\Model\Cart', 'messageFactory'),
    array('_message', 'Magento\Core\Model\Session\AbstractSession', 'messageFactory'),
    array('_messageFactory', 'Magento\Core\Model\Session\AbstractSession', 'messagesFactory'),
    array('_message', 'Magento\Core\Model\Session\Context', 'messageFactory'),
    array('_messageFactory', 'Magento\Core\Model\Session\Context', 'messagesFactory'),
    array('_sessionQuote', 'Magento\Sales\Block\Adminhtml\Order\Create\Messages', 'sessionQuote'),
    array('_coreRegistry', 'Magento\Sales\Block\Adminhtml\Order\View\Messages', 'coreRegistry'),
    array('_message', 'Magento\Sales\Model\Quote', 'messageFactory'),
    array('_filesystem', '\Magento\Cms\Helper\Wysiwyg\Images', '_directory'),
    array('_filesystem', '\Magento\Cms\Model\Wysiwyg\Images\Storage', '_directory'),
    array('_filesystem', '\Magento\Core\Model\Page\Asset\MergeStrategy\Direct', '_directory'),
    array('_filesystem', '\Magento\Core\Model\Page\Asset\MergeStrategy\Checksum', '_directory'),
    array('_filesystem', 'Magento\Sales\Model\Order\Pdf\AbstractPdf'),
    array('_baseDir', 'Magento\Core\Model\Resource\Setup\Migration'),
    array('_dir', 'Magento\Core\Model\Resource\Setup\Migration'),
    array('_filesystem', 'Magento\Core\Model\Resource\Setup\Migration', '_directory'),
    array('_filesystem', 'Magento\Core\Model\Theme\Collection', '_directory'),
    array('_mediaBaseDirectory', 'Magento\Core\Model\Resource\File\Storage\File'),
    array('_dbHelper', 'Magento\Core\Model\Resource\File\Storage\File'),
    array('_filesystem', 'Magento\Core\Model\Theme\CopyService', '_directory'),
    array('_baseDir', 'Magento\Core\Model\Theme\Collection'),
    array('_filesystem', 'Magento\Downloadable\Controller\Adminhtml\Downloadable\File'),
    array('_dirModel', 'Magento\Downloadable\Controller\Adminhtml\Downloadable\File'),
    array('_dirModel', 'Magento\Downloadable\Model\Link'),
    array('_dirModel', 'Magento\Downloadable\Model\Sample'),
    array('_dir', 'Magento\App\Dir'),
    array('_baseDir', 'Magento\Backup\Model\Fs\Collection'),
    array('_filesystem', 'Magento\Backup\Model\Fs\Collection'),
    array('_dir', 'Magento\Backup\Model\Fs\Collection'),
    array('_dir', 'Magento\Cms\Model\Wysiwyg\Images\Storage'),
    array('_dirs', 'Magento\Core\Helper\Theme'),
    array('_dirs', 'Magento\Core\Model\Resource\Type\Db\Pdo\Mysql'),
    array('_filesystem', 'Magento\GiftWrapping\Model\Wrapping'),
    array('_dirs', 'Magento\Index\Model\Lock\Storage'),
    array('_filesystem', 'Magento\Index\Model\Lock\Storage'),
    array('_coreDir', 'Magento\Sales\Model\Order\Pdf\AbstractPdf'),
    array('_coreDir', 'Magento\ScheduledImportExport\Model\Scheduled\Operation'),
    array('_dir', 'Magento\Core\App\FrontController\Plugin\DispatchExceptionHandler'),
    array('_dirs', 'Magento\Core\Block\Template'),
    array('_applicationDirs', 'Magento\Core\Model\Config\FileResolver'),
    array('_dir', 'Magento\Core\Model\File\Storage'),
    array('_dir', 'Magento\Core\Model\Locale\Hierarchy\Config\FileResolver'),
    array('_dirs', 'Magento\Core\Block\Template\Context'),
    array('_dir', 'Magento\Core\Model\Page\Asset\MergeService'),
    array('_dir', 'Magento\Core\Model\Page\Asset\MinifyService'),
    array('_dir', 'Magento\Core\Model\Resource'),
    array('_dir', 'Magento\Core\Model\Session\Context'),
    array('dir', 'Magento\Core\Model\Theme\Image\Path'),
    array('_dir', 'Magento\Install\App\Action\Plugin\Dir'),
    array('_dirs', 'Magento\View\Block\Template\Context'),
    array('_coreDir', 'Magento\Sales\Model\Order\Pdf\AbstractItems' ,'_rootDirectory'),
    array('_dir', 'Magento\AdvancedCheckout\Model\Import', '_filesystem'),
    array('_dir', 'Magento\Backup\Helper\Data'),
    array('_dir', 'Magento\Backup\Model\Observer', '_filesystem'),
    array('_dir', 'Magento\Catalog\Model\Category\Attribute\Backend\Image', '_filesystem'),
    array('_dir', 'Magento\Catalog\Model\Resource\Product\Attribute\Backend\Image', '_filesystem'),
    array('_dir', 'Magento\CatalogEvent\ModelEvent', '_filesystem'),
    array('_dir', 'Magento\Cms\Helper\Wyiswig\Images'),
    array('_dir', 'Magento\Email\Model\Template'),
    array('_dir', 'Magento\ImportExport\Model\Import\Entity\Product', '_mediaDirectory'),
    array('_dir', 'Magento\ImportExport\Model\AbstractModel', '_varDirectory'),
    array('_coreDir', 'Magento\Install\Model\Installer\Console'),
    array('_dir', 'Magento\Install\Model\Installer\Filesystem'),
    array('_coreDir', 'Magento\Paypal\Model\Report\Settlement', '_filesystem'),
    array('_applicationDirs', 'Magento\Widget\Model\Config\FileResolver', '_filesystem'),
);
