<?php
/**
 * Interface of REST response renderers.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
interface Magento_Webapi_Controller_Rest_Response_RendererInterface
{
    /**
     * Render content in a certain format.
     *
     * @param array|object $data
     * @return string
     */
    public function render($data);

    /**
     * Get MIME type generated by renderer.
     *
     * @return string
     */
    public function getMimeType();
}
