<?php

class AdminLimitOrdersController extends ModuleAdminController {

    public function __construct() {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent() {
        parent::initContent();
        $this->getLimitOrders();
    }

    public function postProcess() {

        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('MONTHLY_LIMIT_EUROS', (int)Tools::getValue('monthly_limit_euros')); 
            Configuration::updateValue('MONTHLY_LIMIT_TIMES', (int)Tools::getValue('monthly_limit_times'));
            Configuration::updateValue('MONTHLY_LIMIT_PRODUCTS', (int)Tools::getValue('monthly_limit_products'));
        }
    }    

    private function getLimitOrders() {

        $this->context->smarty->assign(array(
            'monthly_limit_euros' => (int)Configuration::get('MONTHLY_LIMIT_EUROS'),
            'monthly_limit_times' => (int)Configuration::get('MONTHLY_LIMIT_TIMES'),
            'monthly_limit_products' => (int)Configuration::get('MONTHLY_LIMIT_PRODUCTS'),
            'module_dir' => $this->module->getPathUri(),
            '_token' => Tools::getAdminTokenLite('AdminOrders')
        ));
        
        $this->setTemplate('monthly_limit.tpl');
    }
}