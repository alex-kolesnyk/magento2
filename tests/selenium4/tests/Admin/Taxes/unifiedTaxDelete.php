<?php

class Admin_Taxes_unifiedTaxDelete extends TestCaseAbstract {

    /**
     * Setup procedure.
     * Initializes model and loads configuration
     */
    function setUp()
    {
        $this->model = $this->getModel('admin/tax');
        $this->setUiNamespace();
    }

    /**
     * Test deleating Tax Rule
     */
    function testUnifiedTaxDeleating()
    {
        $taxData = array(
            'search_tax_customer_class_name' => 'Test customer tax class 1',
            'search_tax_product_class_name' => 'Test product tax class 1',
            'search_tax_rate_identigier' => 'Test tax rate 1',
            'search_tax_rule_name' => 'Test tax rule',
        );
        if ($this->model->doLogin()) {
            $this->model->unifiedTaxDelete($taxData, "manage_tax_rules", "tax_rule_name");
            /*
             * manage_tax_zone_rate, tax_rate_identifier
             * manage_tax_rules, tax_rule_name
             * product_tax_classes, product_tax_class_name
             * customer_tax_classes, customer_tax_class_name
             */
        }
    }

}