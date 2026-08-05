<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_.'monthlylimit/classes/LimitManager.php');

class MonthlyLimit extends Module {

    public function __construct(){
        $this->name = 'monthlylimit';
        $this->tab = 'administration';
        $this->version = '1.2.1'; // x-release-please-version
        $this->author = 'Oscar Periche - 4funkies';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

    $this->displayName = $this->l('Monthly Limit'); 
    $this->description = $this->l('This module allows you to control employees monthly purchases in the shop by setting specific limits. Define a maximum monthly purchase amount, the maximum number of times an employee can buy each month, or the total quantity of each product that each employee can purchase monthly.');
    }

    public function install(){
        if (!parent::install() || !$this->installTab()) {
            return false;
        }
        
    Configuration::updateValue('MONTHLY_LIMIT_EUROS', 0); // Limit purchase amount - 0 means unlimited
    Configuration::updateValue('MONTHLY_LIMIT_TIMES', 0); // Limit times - 0 means unlimited
        
        if (!Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'monthlylimit_products_limit` (
                `id_product` INT UNSIGNED NOT NULL,
                `quantity` INT(3) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id_product`),
                CONSTRAINT `fk_id_product` FOREIGN KEY (`id_product`) REFERENCES `' . _DB_PREFIX_ . 'product`(`id_product`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ')) {
            $this->_errors[] = $this->l('Error creating monthlylimit_products_limit table.');
            return false;
        }

        if (!$this->registerHook('actionCartUpdateQuantityBefore')) {
            return false;
        }

        if(!$this->registerHook('displayAdminProductsExtra')) {
            return false;
        }

        if(!$this->registerHook('actionProductUpdate')) {
            return false;
        }

        // Copy custom.js to theme to override default behaviour.
        // _PS_THEME_DIR_ already points to the active theme's directory
        // (themes/<theme_name>/); some themes (e.g. Hummingbird) don't ship
        // an assets/js/ folder by default, so it must be created first or
        // copy() fails.
        $jsThemeDir = _PS_THEME_DIR_.'assets/js/';
        if (!is_dir($jsThemeDir)) {
            mkdir($jsThemeDir, 0755, true);
        }
        $source = _PS_MODULE_DIR_.'monthlylimit/assets/js/custom.js';
        copy($source, $jsThemeDir.'custom.js');

        return true;
    }

    public function uninstall(){
        if (!parent::uninstall() || !$this->uninstallTab()) {
            return false;
        }

        // Delete custom.js from theme
        $destination = _PS_THEME_DIR_.'assets/js/custom.js';
        if (file_exists($destination)) {
            unlink($destination);
        }

        return true;
    }

    public function enable($force_all = false) {
        return parent::enable($force_all)
            && $this->installTab()
        ;
    }

    public function disable($force_all = false) {
        return parent::disable($force_all)
            && $this->uninstallTab()
        ;
    }

    public function installTab() {
        $tabId = (int) Tab::getIdFromClassName('AdminLimitOrdersController');
    
        if (!$tabId) {
            $tab = new Tab();
        } else {
            $tab = new Tab($tabId);
        }

        $tab->active = 1;
        $tab->class_name = 'AdminLimitOrdersController'; 
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Límites mensuales', array(), 'Modules.MonthlyLimit.Admin', $lang['locale']);
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders'); 
        $tab->module = $this->name;

        return $tab->save();

    }  

    public function uninstallTab() {
        $tabId = (int) Tab::getIdFromClassName('AdminLimitOrdersController');

        if ($tabId) {
            $tab = new Tab($tabId);
            $tab->delete();
        }

        return true;

    }

    /*
    // Hooks
    */

    public function hookActionCartUpdateQuantityBefore($params) {
        
        $cart = $params['cart'];
        $productId = $params['product']->id;
        $quantity = $params['quantity'];
        $operator = $params['operator'];
        $customerId = (int) $cart->id_customer;
        
        $limitManager = new LimitManager();
        
        $errorMessages = [];
        
        // Comprovar límit d'euros
        $errorEuros = $limitManager->checkMonthlyLimitEuros($cart, $customerId, $productId, $quantity, $operator); 
        if ($errorEuros) {
            $errorMessages[] = $errorEuros;
        }
        
        // Comprovar límit de productes per ID
        $errorProducts = $limitManager->checkMonthlyLimitProducts($cart, $customerId, $productId, $quantity, $operator);
        if ($errorProducts) {
            $errorMessages[] = $errorProducts;
        }
        
        // Comprovar límit de comandes (només quan s'afegeix quantitat, mai en reduir/eliminar del carret)
        if ($operator === 'up') {
            $errorTimes = $limitManager->checkMonthlyLimitTimes($customerId);
            if ($errorTimes) {
                $errorMessages[] = $errorTimes;
            }
        }
        
        if (!empty($errorMessages)) {
            $fullErrorMessage = implode("\n", $errorMessages);
        
            $errorMessage = [
                'hasError' => true,
                'errors' => [$fullErrorMessage]
            ];
        
            die(json_encode($errorMessage));
            
        } else {
            return true;
        }
    }

    public function hookDisplayAdminProductsExtra($params) {
        $productId = (int) Tools::getValue('id_product');
        
        $sql = 'SELECT quantity FROM ' . _DB_PREFIX_ . 'monthlylimit_products_limit WHERE id_product = ' . (int) $productId;
        $monthlyLimitProducts = Db::getInstance()->getValue($sql);
        
        $this->context->smarty->assign([
            'monthlyLimitProducts' => $monthlyLimitProducts
        ]);
        
        return $this->display(__FILE__, 'views/templates/admin/admin_product_extra.tpl');
    }

    public function hookActionProductUpdate($params) {
        // actionProductUpdate fires on every product save (any tab), but the "quantity"
        // field only gets submitted from the tab that renders our displayAdminProductsExtra
        // field. Without this guard, saving the product from any other tab would silently
        // reset the configured monthly limit back to 0.
        if (!Tools::getIsset('quantity')) {
            return;
        }

        $productId = (int) $params['id_product'];
        $quantity = (int) Tools::getValue('quantity');

        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'monthlylimit_products_limit (id_product, quantity)
                VALUES (' . $productId . ', ' . $quantity . ')
                ON DUPLICATE KEY UPDATE quantity = ' . $quantity;

        Db::getInstance()->execute($sql);
    }
    
}
