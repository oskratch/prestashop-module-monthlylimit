<?php

class LimitManager {

    private $monthlyLimitProducts; // Límit de productes per ID de producte
    private $monthlyLimitEuros;    // Límit de despesa mensual en euros
    private $monthlyLimitTimes;    // Límit de comandes mensuals

    public function __construct() {
        $this->monthlyLimitProducts = (int) Configuration::get('MONTHLY_LIMIT_PRODUCTS'); 
        $this->monthlyLimitEuros = (float) Configuration::get('MONTHLY_LIMIT_EUROS');
        $this->monthlyLimitTimes = (int) Configuration::get('MONTHLY_LIMIT_TIMES');
    }

    // Comprovació de límit de productes per ID durant el mes
    public function checkMonthlyLimitProducts($cart, $customerId, $productId, $quantityChange, $operator = 'up') {

        if ($this->monthlyLimitProducts == 0) {
            return false; // Si el límit és 0, no hi ha límit
        }
    
        // Obtenim la quantitat actual d'aquest producte al carro
        $cartProductQuantities = $this->getCartProductQuantities($cart);
        $quantityInCart = isset($cartProductQuantities[$productId]) ? $cartProductQuantities[$productId] : 0;
    
        // Obtenim la quantitat total comprada d'aquest producte durant el mes actual
        $totalQuantityBoughtThisMonth = $this->getTotalProductQuantityBoughtThisMonth($productId, $customerId);
    
        // Ajustem el canvi segons si és una suma o una resta
        $quantityChange = ($operator === 'up' ? 1 : -1) * $quantityChange;
    
        // Calcula la quantitat actualitzada del producte
        $totalQuantityForProduct = $totalQuantityBoughtThisMonth + $quantityInCart + $quantityChange;
    
        // Comprovem si s'ha superat el límit
        if ($totalQuantityForProduct > $this->monthlyLimitProducts) {
            // Calculem quanta quantitat es pot afegir per no superar el límit
            $remainingQuantity = $this->monthlyLimitProducts - $totalQuantityBoughtThisMonth - $quantityInCart;
    
            // Si no es pot afegir cap unitat més, no mostrem la part de la quantitat restant
            if ($remainingQuantity <= 0) {
                return $this->l('Has superado el límite de compras de este producto durante el mes.');
            }
    
            // Si encara es poden afegir unitats, mostrem la quantitat restant que es pot comprar
            $unit_word = $remainingQuantity == 1 ? 'unidad' : 'unidades';
            return $this->l(
                'Has superado el límite de compras de este producto durante el mes. Puedes comprar ' . 
                $remainingQuantity . ' ' . $unit_word . ' más.'
            );
        }
    
        return false; // Si no s'ha superat cap límit
    }    

    // Comprovació de límit de despesa mensual en euros
    public function checkMonthlyLimitEuros($cart, $customerId, $productId, $quantityChange, $operator = 'up') {

        if ($this->monthlyLimitEuros == 0) {
            return false; // Si el límit és 0, no hi ha límit
        }
        
        // Obtenim el total de la cistella actual
        $cartTotal = (float) $cart->getOrderTotal(true, Cart::BOTH);
    
        // Obtenim el preu del producte
        $productPrice = $this->getProductPrice($productId, $cart->id_currency); 
    
        // Si l'acció és afegir, sumem la quantitat; si és eliminar, restem la quantitat
        $additionalCost = ($operator == 'up' ? 1 : -1) * $productPrice * $quantityChange;
    
        // Actualitzem el total de la cistella tenint en compte el canvi (afegir o eliminar)
        $updatedCartTotal = $cartTotal + $additionalCost;
    
        // Comptem les compres anteriors d'aquest client
        $totalSpentThisMonth = $this->getTotalSpentThisMonth($customerId);
    
        // Calcula la despesa total amb la despesa actual de la cistella + les compres anteriors
        $totalSpentForCustomer = $totalSpentThisMonth + $updatedCartTotal;
    
        // Si la despesa total supera el límit, retornem un error
        if ($totalSpentForCustomer > $this->monthlyLimitEuros) {
            // Calculem quant realment queda per gastar
            $remainingLimit = $this->monthlyLimitEuros - ($totalSpentThisMonth + $cartTotal);

            // Ajustem el límit restant a un mínim de 0
            $remainingLimit = max(0, $remainingLimit);

            // Formatem el límit restant sense decimals innecessaris
            $remainingLimitFormatted = number_format($remainingLimit, 2, ',', '.');
            if (fmod($remainingLimit, 1) === 0.0) {
                $remainingLimitFormatted = number_format($remainingLimit, 0, ',', '.');
            }

            return $this->l(
                'Has superado el límite de gasto mensual. Sólo puedes gastar ' . 
                $remainingLimitFormatted . '€ más.'
            );
        }
    
        return false; // Tot correcte
    }    

    // Comprovació de límit de nombre de comandes mensuals
    public function checkMonthlyLimitTimes($customerId) {
        if ($this->monthlyLimitTimes == 0) {
            return false; // Si el límit és 0, no hi ha límit
        }

        // Comptem les comandes fetes aquest mes
        $totalOrdersThisMonth = $this->getTotalOrdersThisMonth($customerId);

        // Si el nombre de comandes supera el límit, retornem un error
        if ($totalOrdersThisMonth >= $this->monthlyLimitTimes) {
            return $this->l('Has alcanzado el límite de pedidos para este mes.');
        }

        return false;
    }

    // Recuperar les quantitats de productes de la cistella actual
    private function getCartProductQuantities($cart) {
        $productQuantities = [];
        foreach ($cart->getProducts() as $product) {
            $productQuantities[$product['id_product']] = $product['quantity'];
        }
        return $productQuantities;
    }

    // Recuperar la quantitat total de productes comprats d'un producte específic durant el mes
    private function getTotalProductQuantityBoughtThisMonth($productId, $customerId) {
        $sql = 'SELECT SUM(product_quantity) FROM ' . _DB_PREFIX_ . 'order_detail 
                JOIN ' . _DB_PREFIX_ . 'orders ON ' . _DB_PREFIX_ . 'orders.id_order = ' . _DB_PREFIX_ . 'order_detail.id_order 
                WHERE ' . _DB_PREFIX_ . 'orders.id_customer = ' . (int)$customerId . ' 
                AND ' . _DB_PREFIX_ . 'order_detail.product_id = ' . (int)$productId . ' 
                AND YEAR(' . _DB_PREFIX_ . 'orders.date_add) = YEAR(CURDATE()) 
                AND MONTH(' . _DB_PREFIX_ . 'orders.date_add) = MONTH(CURDATE())';
        return (int) Db::getInstance()->getValue($sql); // Retorna la quantitat total comprada aquest mes
    }

    // Recuperar la despesa total d'un client durant el mes en curs
    private function getTotalSpentThisMonth($customerId) {
        $sql = 'SELECT SUM(total_paid) FROM ' . _DB_PREFIX_ . 'orders 
                WHERE id_customer = ' . (int)$customerId . ' 
                AND YEAR(date_add) = YEAR(CURDATE()) 
                AND MONTH(date_add) = MONTH(CURDATE())';
        return (float) Db::getInstance()->getValue($sql); // Retorna el total gastat aquest mes
    }

    // Recuperar el nombre total de comandes fetes per un client durant el mes
    private function getTotalOrdersThisMonth($customerId) {
        $sql = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'orders 
                WHERE id_customer = ' . (int)$customerId . ' 
                AND YEAR(date_add) = YEAR(CURDATE()) 
                AND MONTH(date_add) = MONTH(CURDATE())';
        return (int) Db::getInstance()->getValue($sql); // Retorna el nombre de comandes aquest mes
    }

    /// Recuperar el preu d'un producte
    private function getProductPrice($productId, $currencyId) {
        $product = new Product($productId);
        return Product::getPriceStatic($product->id, true, null, 2, null, false, false, 1, false, null, $currencyId);
    }

    // Funció per retornar el missatge d'error localitzat
    private function l($message) {
        return $message;
    }
}