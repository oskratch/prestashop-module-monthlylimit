# MonthlyLimit

[![PrestaShop](https://img.shields.io/badge/PrestaShop-8.2%20--%209.1-blue)](https://www.prestashop.com/)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0-green.svg)](LICENSE)

PrestaShop module that limits how much each customer can buy per month. It was built for employee shops and works for any B2B store that needs purchase caps.

## Features

- **Monthly spending limit** per customer.
- **Monthly order limit**: maximum number of orders per customer.
- **Per-product limit**: maximum units of a product each customer can buy in a month.
- **Exclusions**: customers who skip every limit, picked with a search field (name or email).
- Translatable interface.

## Requirements

- PrestaShop 8.2 – 9.1 (per `ps_versions_compliancy` in `config.xml`)
- PHP 7.2 or higher
- MySQL 5.6 or higher
- Works with jQuery-based themes (e.g. Classic) and native-fetch themes (e.g. Hummingbird on PrestaShop 9+); no front-end framework required

## Installation

1. Clone this repository or download it as a ZIP:
   ```bash
   git clone https://github.com/oskratch/prestashop-module-monthlylimit.git
   ```
2. Compress the `monthlylimit/` folder into a `.zip` file.
3. In the back office, go to **Modules and Services** → **Upload a module**, upload the `.zip` and enable the module.

## Configuration

### Global limits

Go to **Orders** → **Monthly Limits** and set:

- **Monthly spending limit** in euros (0 = no limit)
- **Monthly order limit** (0 = no limit)

### Per-product limits

Edit a product, open the **Modules** tab and set the maximum units per customer per month in the **Monthly Limit** section (0 = no limit).

### Excluded customers

On the **Monthly Limits** page, scroll to **Exclude customers from limits**, search customers by name or email, select them and click **Save exclusions**. Excluded customers skip every limit.

## How it works

Limits are checked when a customer adds a product to the cart:

- **Spending**: total spent by the customer in the current month.
- **Orders**: completed orders in the current month.
- **Products**: units of each product bought in the current month.
- **Exclusions**: excluded customers are not checked.

## Troubleshooting

**A product shows the wrong limit**
- Check the limit set on the product, and that the value is greater than 0.

**An excluded customer is still limited**
- Check the customer is saved in the exclusion list, then clear the PrestaShop cache.

**Limits are not applied at all**
- Check the module hooks are installed and the `ps_monthlylimit_products_limit` table exists.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Contributing

1. Fork the repository.
2. Create a branch (`git checkout -b feature/my-change`).
3. Commit your changes.
4. Push the branch and open a pull request.

## Support

- Email: oskratch@gmail.com
- Issues: [GitHub Issues](https://github.com/oskratch/prestashop-module-monthlylimit/issues)

## License

GPL-2.0. See [LICENSE](LICENSE).
