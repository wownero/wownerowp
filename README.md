# Wownero Gateway for WooCommerce

## Features

* Payment validation done through either `wownero-wallet-rpc` or the [explore.wownero.com blockchain explorer](https://explore.wownero.com/).
* Validates payments with `cron`, so does not require users to stay on the order confirmation page for their order to validate.
* Order status updates are done through AJAX instead of Javascript page reloads.
* Customers can pay with multiple transactions and are notified as soon as transactions hit the mempool.
* Configurable block confirmations, from `0` for zero confirm to `60` for high ticket purchases.
* Live price updates every minute; total amount due is locked in after the order is placed for a configurable amount of time (default 60 minutes) so the price does not change after order has been made.
* Hooks into emails, order confirmation page, customer order history page, and admin order details page.
* View all payments received to your wallet with links to the blockchain explorer and associated orders.
* Optionally display all prices on your store in terms of Wownero.
* Shortcodes! Display exchange rates in numerous currencies.

## Requirements

* Wownero wallet to receive payments - [GUI](https://github.com/wownero/wownero/releases)
* [BCMath](http://php.net/manual/en/book.bc.php) - A PHP extension used for arbitrary precision maths

## Installing the plugin

* Download the plugin from the [releases page](https://github.com/itssteven/monerowp) or clone with `git clone https://github.com/itssteven/monerowp`
* Unzip or place the `wownero-woocommerce-gateway` folder in the `wp-content/plugins` directory.
* Activate "Wownero Woocommerce Gateway" in your WordPress admin dashboard.
* It is highly recommended that you use native cronjobs instead of WordPress's "Poor Man's Cron" by adding `define('DISABLE_WP_CRON', true);` into your `wp-config.php` file and adding `* * * * * wget -q -O - https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1` to your crontab.

## Option 1: Use your wallet address and viewkey

This is the easiest way to start accepting Wownero on your website. You'll need:

* Your Wownero wallet address starting with `W`
* Your wallet's secret viewkey

Then simply select the `viewkey` option in the settings page and paste your address and viewkey. You're all set!

Note on privacy: when you validate transactions with your private viewkey, your viewkey is sent to (but not stored on) xmrchain.net over HTTPS. This could potentially allow an attacker to see your incoming, but not outgoing, transactions if they were to get his hands on your viewkey. Even if this were to happen, your funds would still be safe and it would be impossible for somebody to steal your money. For maximum privacy use your own `wownero-wallet-rpc` instance.

## Option 2: Using `wownero-wallet-rpc`

The most secure way to accept Wownero on your website. You'll need:

* Root access to your webserver
* Latest [Wownero-currency binaries](https://github.com/wownero/wownero/releases)

After downloading (or compiling) the Wownero binaries on your server, install the [systemd unit files](https://github.com/monero-integrations/monerowp/tree/master/assets/systemd-unit-files) or run `wownerod` and `wownero-wallet-rpc` with `screen` or `tmux`. You can skip running `wownerod` by using a remote node with `wownero-wallet-rpc` by adding `--daemon-address node.wowne.ro:34568` to the `wownero-wallet-rpc.service` file.

Note on security: using this option, while the most secure, requires you to run the Wownero wallet RPC program on your server. Best practice for this is to use a view-only wallet since otherwise your server would be running a hot-wallet and a security breach could allow hackers to empty your funds.

## Configuration

* `Enable / Disable` - Turn on or off Wownero gateway. (Default: Disable)
* `Title` - Name of the payment gateway as displayed to the customer. (Default: Wownero Gateway)
* `Discount for using Wownero` - Percentage discount applied to orders for paying with Wownero. Can also be negative to apply a surcharge. (Default: 0)
* `Order valid time` - Number of seconds after order is placed that the transaction must be seen in the mempool. (Default: 3600 [1 hour])
* `Number of confirmations` - Number of confirmations the transaction must recieve before the order is marked as complete. Use `0` for nearly instant confirmation. (Default: 5)
* `Confirmation Type` - Confirm transactions with either your viewkey, or by using `wownero-wallet-rpc`. (Default: viewkey)
* `Wownero Address` (if confirmation type is viewkey) - Your public Wownero address starting with 4. (No default)
* `Secret Viewkey` (if confirmation type is viewkey) - Your *private* viewkey (No default)
* `Wownero wallet RPC Host/IP` (if confirmation type is `wownero-wallet-rpc`) - IP address where the wallet rpc is running. It is highly discouraged to run the wallet anywhere other than the local server! (Default: 127.0.0.1)
* `Wownero wallet RPC port` (if confirmation type is `wownero-wallet-rpc`) - Port the wallet rpc is bound to with the `--rpc-bind-port` argument. (Default 18080)
* `Testnet` - Check this to change the blockchain explorer links to the testnet explorer. (Default: unchecked)
* `SSL warnings` - Check this to silence SSL warnings. (Default: unchecked)
* `Show QR Code` - Show payment QR codes. There is no Wownero software that can read QR codes at this time (Default: unchecked)
* `Show Prices in Wownero` - Convert all prices on the frontend to Wownero. Experimental feature, only use if you do not accept any other payment option. (Default: unchecked)
* `Display Decimals` (if show prices in Wownero is enabled) - Number of decimals to round prices to on the frontend. The final order amount will not be rounded and will be displayed down to the nanoWownero. (Default: 11)

## Shortcodes

This plugin makes available two shortcodes that you can use in your theme.

#### Live price shortcode

This will display the price of Wownero in the selected currency. If no currency is provided, the store's default currency will be used.

```
[wownero-price]
[wownero-price currency="BTC"]
[wownero-price currency="USD"]
[wownero-price currency="CAD"]
[wownero-price currency="EUR"]
[wownero-price currency="GBP"]
```
Will display:
```
1 WOW = 123.68000 USD
1 WOW = 0.01827000 BTC
1 WOW = 123.68000 USD
1 WOW = 168.43000 CAD
1 WOW = 105.54000 EUR
1 WOW = 94.84000 GBP
```


#### Wownero accepted here badge

This will display a badge showing that you accept Wownero-currency.

`[wownero-accepted-here]`

![Wownero Accepted Here](/assets/images/wownero-accepted-here.png?raw=true "Wownero Accepted Here")

## Donations

monero-integrations: 44krVcL6TPkANjpFwS2GWvg1kJhTrN7y9heVeQiDJ3rP8iGbCd5GeA4f3c2NKYHC1R4mCgnW7dsUUUae2m9GiNBGT4T8s2X
guy who converted it to wownero: Wo57gnyJUihhnfpr7rbjv8Q9eJ8igcbyFPtbPogbLoGaLrYCV3RZHANc1SCJoL3WkCD6XGHbVaWYminF6rCzei6Y1Bhix1iRn
