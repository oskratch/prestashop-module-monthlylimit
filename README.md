
# MonthlyLimit - PrestaShop Module

**Version:** 1.0.1  
**Compatible with PrestaShop:** 1.7.x and 1.8.x  

## Overview
MonthlyLimit helps you control and restrict monthly purchases for customers (employees) in your PrestaShop store. You can set global and per-product limits, and exclude specific customers from all restrictions using a powerful search-enabled selector.

## Features
- **Maximum monthly amount:** Set a maximum amount a customer can spend per month.
- **Maximum number of purchases:** Limit the number of orders a customer can place each month.
- **Individual product limit:** Restrict the quantity of each product a customer can buy monthly.
- **Exclude customers from limits:** Easily search and select customers to exclude from all limits using a multi-select field with instant search (Select2).

## Requirements
- PrestaShop 1.7.x or 1.8.x
- PHP 7.2 or higher
- jQuery (included by default in PrestaShop backoffice)

## Installation
1. Compress the module folder (`monthlylimit/`) into a `.zip` file.
2. Go to your PrestaShop admin panel.
3. Navigate to **Modules and Services** > **Upload a module**.
4. Upload the `.zip` file and activate the module.

## Configuration
1. Access the module configuration from the **Orders** menu (**Monthly Limits** submenu).
2. Set your desired purchase limits (amount, number of purchases, per-product limits).
3. To set product-specific limits, go to a product page in the admin panel, select the **Modules** tab, and configure the limit for each product under **Monthly Limit**.
4. To exclude customers from all limits, use the search-enabled multi-select field in the module configuration. Select one or more customers and save exclusions.

## Exclude Customers from Limits
- In the module configuration, scroll to the **Exclude customers from limits** section.
- Use the search box to quickly find customers by name or email.
- Select one or more customers to exclude from all purchase limits.
- Click **Save exclusions** to apply changes.

## Changelog
**v1.0.1**
- Initial release
- Added exclusion of customers from all limits
- Integrated Select2 for customer search and selection

## Support
For help or questions, contact: `oskratch@gmail.com`

## Contributing
Pull requests and suggestions are welcome! Please fork the repository and submit your improvements.

## License
This plugin is licensed under the GPLv2 or later. See [LICENSE](LICENSE) for details.