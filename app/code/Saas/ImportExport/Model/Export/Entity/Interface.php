<?php
/**
 * Entity Export Interface
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
interface Saas_ImportExport_Model_Export_Entity_Interface
{
    public function export();

    public function getIsLast();

    public function setCurrentPage($page);

    public function setIsLast($isLast = true);
}
