
# 🛒 MonthlyLimit - PrestaShop Module

[![PrestaShop](https://img.shields.io/badge/PrestaShop-8.2%20--%209.1-blue)](https://www.prestashop.com/)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0-green.svg)](LICENSE)

A powerful PrestaShop module that helps store owners control and restrict monthly purchases for customers (especially useful for employee stores or B2B environments).

## ✨ Features

- 💰 **Monthly spending limits**: Set maximum amounts customers can spend per month
- 📦 **Order frequency limits**: Restrict the number of orders per customer per month  
- 🎯 **Individual product limits**: Set specific quantity limits for each product per customer monthly
- 👥 **Customer exclusions**: Exclude specific customers from all limits with an intuitive search interface
- 🔍 **Advanced search**: Find and select customers easily using integrated Select2 search functionality
- 🌐 **Multi-language support**: Fully translatable interface

## 📋 Requirements

- PrestaShop 8.2 – 9.1 (per `ps_versions_compliancy` in `config.xml`)
- PHP 7.2 or higher
- MySQL 5.6 or higher
- No frontend JS framework required: works with jQuery-based themes (e.g. Classic) and native-fetch themes (e.g. Hummingbird/PrestaShop 9+) alike

## 🚀 Installation

1. **Download**: Clone this repository or download as ZIP
   ```bash
   git clone https://github.com/oskratch/prestashop-module-monthlylimit.git
   ```

2. **Prepare**: Compress the `monthlylimit/` folder into a `.zip` file

3. **Install**: 
   - Go to your PrestaShop admin panel
   - Navigate to **Modules and Services** → **Upload a module**
   - Upload the `.zip` file and activate the module

## ⚙️ Configuration

### Global Limits
1. Go to **Orders** → **Monthly Limits** in your admin panel
2. Configure global settings:
   - **Monthly spending limit** (in euros, 0 = unlimited)
   - **Monthly order limit** (number of orders, 0 = unlimited)

### Product-Specific Limits
1. Edit any product in your catalog
2. Go to the **Modules** tab
3. Find the **Monthly Limit** section
4. Set the maximum units per customer per month (0 = unlimited)

### Customer Exclusions
1. In the **Monthly Limits** configuration page
2. Scroll to **Exclude customers from limits**
3. Use the search field to find customers by name or email
4. Select customers to exclude from ALL limits
5. Click **Save exclusions**

## 📖 How It Works

The module checks purchase limits in real-time when customers add products to their cart:

- **Monthly spending**: Tracks total spent by customer in current month
- **Order frequency**: Counts number of completed orders in current month  
- **Product limits**: Tracks quantities purchased per product per customer per month
- **Exclusions**: Bypasses all checks for excluded customers

## 🐛 Troubleshooting

### Common Issues

**Products showing wrong limits**
- Ensure individual product limits are set correctly in product configuration
- Check that the monthly limit values are greater than 0

**Customers not being excluded properly**  
- Verify customers are saved in the exclusion list
- Clear PrestaShop cache after making changes

**Limits not working**
- Check that module hooks are properly installed
- Verify database table `ps_monthlylimit_products_limit` exists

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

Need help? Contact us:
- 📧 Email: oskratch@gmail.com
- 🐛 Issues: [GitHub Issues](https://github.com/oskratch/prestashop-module-monthlylimit/issues)

## 📄 License

This project is licensed under the GPL-2.0 License - see the [LICENSE](LICENSE) file for details.

---

⭐ **Found this module useful?** Give us a star on GitHub!

Made with ❤️ by [oskratch](https://github.com/oskratch)