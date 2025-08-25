<?php

class LimitManager {

    private $monthlyLimitProducts; // Product limit per product ID
    private $monthlyLimitEuros;    // Monthly spending limit in euros
    private $monthlyLimitTimes;    // Monthly order limit
    private $excludedCustomers = [];

    public function __construct() {
        $this->monthlyLimitProducts = (int) Configuration::get('MONTHLY_LIMIT_PRODUCTS'); 
        $this->monthlyLimitEuros = (float) Configuration::get('MONTHLY_LIMIT_EUROS');
        $this->monthlyLimitTimes = (int) Configuration::get('MONTHLY_LIMIT_TIMES');
        $excluded = Configuration::get('MONTHLY_LIMIT_EXCLUDED_CUSTOMERS');
        $this->excludedCustomers = $excluded ? explode(',', $excluded) : [];
    }

    // Check monthly product limit per product ID
    public function checkMonthlyLimitProducts($cart, $customerId, $productId, $quantityChange, $operator = 'up') {
        // If the customer is excluded, no limit applies
        if (in_array($customerId, $this->excludedCustomers)) {
            return false;
        }

        if ($this->monthlyLimitProducts == 0) {
            return false; // If the limit is 0, there is no limit
        }
    
        // Get the current quantity of this product in the cart
        $cartProductQuantities = $this->getCartProductQuantities($cart);
        $quantityInCart = isset($cartProductQuantities[$productId]) ? $cartProductQuantities[$productId] : 0;
    
        // Get the total quantity bought of this product during the current month
        $totalQuantityBoughtThisMonth = $this->getTotalProductQuantityBoughtThisMonth($productId, $customerId);
    
        // Adjust the change depending on whether it's an addition or subtraction
        $quantityChange = ($operator === 'up' ? 1 : -1) * $quantityChange;
    
        // Calculate the updated quantity of the product
        $totalQuantityForProduct = $totalQuantityBoughtThisMonth + $quantityInCart + $quantityChange;
    
        // Check if the limit has been exceeded
        if ($totalQuantityForProduct > $this->monthlyLimitProducts) {
            // Calculate how much quantity can be added without exceeding the limit
            $remainingQuantity = $this->monthlyLimitProducts - $totalQuantityBoughtThisMonth - $quantityInCart;
    
            // If no more units can be added, do not show the remaining quantity part
            if ($remainingQuantity <= 0) {
                return $this->l('Has superado el límite de compras de este producto durante el mes.');
            }
    
            // If units can still be added, show the remaining quantity that can be purchased
            $unit_word = $remainingQuantity == 1 ? 'unidad' : 'unidades';
            return $this->l(
                'Has superado el límite de compras de este producto durante el mes. Puedes comprar ' . 
                $remainingQuantity . ' ' . $unit_word . ' más.'
            );
        }
    
        return false; // If no limit has been exceeded
    }    

    // Check monthly spending limit in euros
    public function checkMonthlyLimitEuros($cart, $customerId, $productId, $quantityChange, $operator = 'up') {
        // If the customer is excluded, no limit applies
        if (in_array($customerId, $this->excludedCustomers)) {
            return false;
        }

        if ($this->monthlyLimitEuros == 0) {
            return false; // If the limit is 0, there is no limit
        }
        
        // Get the total of the current cart
        $cartTotal = (float) $cart->getOrderTotal(true, Cart::BOTH);
    
        // Get the price of the product
        $productPrice = $this->getProductPrice($productId, $cart->id_currency); 
    
        // If the action is add, sum the quantity; if remove, subtract the quantity
        $additionalCost = ($operator == 'up' ? 1 : -1) * $productPrice * $quantityChange;
    
        // Update the cart total considering the change (add or remove)
        $updatedCartTotal = $cartTotal + $additionalCost;
    
        // Count previous purchases of this customer
        $totalSpentThisMonth = $this->getTotalSpentThisMonth($customerId);
    
        // Calculate the total spending with the current cart + previous purchases
        $totalSpentForCustomer = $totalSpentThisMonth + $updatedCartTotal;
    
        // If the total spending exceeds the limit, return an error
        if ($totalSpentForCustomer > $this->monthlyLimitEuros) {
            // Calculate how much is actually left to spend
            $remainingLimit = $this->monthlyLimitEuros - ($totalSpentThisMonth + $cartTotal);

            // Adjust the remaining limit to a minimum of 0
            $remainingLimit = max(0, $remainingLimit);

            // Format the remaining limit without unnecessary decimals
            $remainingLimitFormatted = number_format($remainingLimit, 2, ',', '.');
            if (fmod($remainingLimit, 1) === 0.0) {
                $remainingLimitFormatted = number_format($remainingLimit, 0, ',', '.');
            }

            return $this->l(
                'Has superado el límite de gasto mensual. Sólo puedes gastar ' . 
                $remainingLimitFormatted . '€ más.'
            );
        }
    
        return false; // All correct
    }    

    // Check monthly order limit
    public function checkMonthlyLimitTimes($customerId) {
        // If the customer is excluded, no limit applies
        if (in_array($customerId, $this->excludedCustomers)) {
            return false;
        }
        if ($this->monthlyLimitTimes == 0) {
            return false; // If the limit is 0, there is no limit
        }

        // Count the orders made this month
        $totalOrdersThisMonth = $this->getTotalOrdersThisMonth($customerId);

        // If the number of orders exceeds the limit, return an error
        if ($totalOrdersThisMonth >= $this->monthlyLimitTimes) {
            return $this->l('Has alcanzado el límite de pedidos para este mes.');
        }

        return false;
    }

    // Retrieve product quantities from the current cart
    private function getCartProductQuantities($cart) {
        $productQuantities = [];
        foreach ($cart->getProducts() as $product) {
            $productQuantities[$product['id_product']] = $product['quantity'];
        }
        return $productQuantities;
    }

    // Retrieve the total quantity of products bought of a specific product during the month
    private function getTotalProductQuantityBoughtThisMonth($productId, $customerId) {
        $sql = 'SELECT SUM(product_quantity) FROM ' . _DB_PREFIX_ . 'order_detail 
                JOIN ' . _DB_PREFIX_ . 'orders ON ' . _DB_PREFIX_ . 'orders.id_order = ' . _DB_PREFIX_ . 'order_detail.id_order 
                WHERE ' . _DB_PREFIX_ . 'orders.id_customer = ' . (int)$customerId . ' 
                AND ' . _DB_PREFIX_ . 'order_detail.product_id = ' . (int)$productId . ' 
                AND YEAR(' . _DB_PREFIX_ . 'orders.date_add) = YEAR(CURDATE()) 
                AND MONTH(' . _DB_PREFIX_ . 'orders.date_add) = MONTH(CURDATE())';
        return (int) Db::getInstance()->getValue($sql); // Returns the total quantity bought this month
    }

    // Retrieve the total spending of a customer during the current month
    private function getTotalSpentThisMonth($customerId) {
        $sql = 'SELECT SUM(total_paid) FROM ' . _DB_PREFIX_ . 'orders 
                WHERE id_customer = ' . (int)$customerId . ' 
                AND YEAR(date_add) = YEAR(CURDATE()) 
                AND MONTH(date_add) = MONTH(CURDATE())';
        return (float) Db::getInstance()->getValue($sql); // Returns the total spent this month
    }

    // Retrieve the total number of orders made by a customer during the month
    private function getTotalOrdersThisMonth($customerId) {
        $sql = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'orders 
                WHERE id_customer = ' . (int)$customerId . ' 
                AND YEAR(date_add) = YEAR(CURDATE()) 
                AND MONTH(date_add) = MONTH(CURDATE())';
        return (int) Db::getInstance()->getValue($sql); // Returns the number of orders this month
    }

    /// Retrieve the price of a product
    private function getProductPrice($productId, $currencyId) {
        $product = new Product($productId);
        return Product::getPriceStatic($product->id, true, null, 2, null, false, false, 1, false, null, $currencyId);
    }

    // Function to return the localized error message
    private function l($message) {
        return $message;
    }
}