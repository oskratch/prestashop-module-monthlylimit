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
        }

        if (Tools::isSubmit('exclude_customers_submit')) {
            $excluded = Tools::getValue('excluded_customers', []);
            if (!is_array($excluded)) {
                $excluded = [$excluded];
            }
            // Save as comma-separated string
            Configuration::updateValue('MONTHLY_LIMIT_EXCLUDED_CUSTOMERS', implode(',', $excluded));
        }
    }    

    private function getLimitOrders() {

    // Load all active customers
    $customers = Db::getInstance()->executeS('SELECT id_customer, firstname, lastname, email FROM '._DB_PREFIX_.'customer WHERE active = 1');

    // Load excluded customers
    $excluded = Configuration::get('MONTHLY_LIMIT_EXCLUDED_CUSTOMERS');
    $excluded_customers = $excluded ? explode(',', $excluded) : [];

        $this->context->smarty->assign(array(
            'monthly_limit_euros' => (int)Configuration::get('MONTHLY_LIMIT_EUROS'),
            'monthly_limit_times' => (int)Configuration::get('MONTHLY_LIMIT_TIMES'),
            'module_dir' => $this->module->getPathUri(),
            '_token' => Tools::getAdminTokenLite('AdminOrders'),
            'customers' => $customers,
            'excluded_customers' => $excluded_customers
        ));

        $this->setTemplate('monthly_limit.tpl');
    }
}