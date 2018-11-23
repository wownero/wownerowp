<?php

defined( 'ABSPATH' ) || exit;

return array(
    'enabled' => array(
        'title' => __('Enable / Disable', 'wownero_gateway'),
        'label' => __('Enable this payment gateway', 'wownero_gateway'),
        'type' => 'checkbox',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'wownero_gateway'),
        'type' => 'text',
        'desc_tip' => __('Payment title the customer will see during the checkout process.', 'wownero_gateway'),
        'default' => __('Wownero Gateway', 'wownero_gateway')
    ),
    'description' => array(
        'title' => __('Description', 'wownero_gateway'),
        'type' => 'textarea',
        'desc_tip' => __('Payment description the customer will see during the checkout process.', 'wownero_gateway'),
        'default' => __('Pay securely using Wownero. You will be provided payment details after checkout.', 'wownero_gateway')
    ),
    'discount' => array(
        'title' => __('Discount for using Wownero', 'wownero_gateway'),
        'desc_tip' => __('Provide a discount to your customers for making a private payment with Wownero', 'wownero_gateway'),
        'description' => __('Enter a percentage discount (i.e. 5 for 5%) or leave this empty if you do not wish to provide a discount', 'wownero_gateway'),
        'type' => __('number'),
        'default' => '0'
    ),
    'valid_time' => array(
        'title' => __('Order valid time', 'wownero_gateway'),
        'desc_tip' => __('Amount of time order is valid before expiring', 'wownero_gateway'),
        'description' => __('Enter the number of seconds that the funds must be received in after order is placed. 3600 seconds = 1 hour', 'wownero_gateway'),
        'type' => __('number'),
        'default' => '3600'
    ),
    'confirms' => array(
        'title' => __('Number of confirmations', 'wownero_gateway'),
        'desc_tip' => __('Number of confirms a transaction must have to be valid', 'wownero_gateway'),
        'description' => __('Enter the number of confirms that transactions must have. Enter 0 to zero-confim. Each confirm will take approximately four minutes', 'wownero_gateway'),
        'type' => __('number'),
        'default' => '5'
    ),
    'confirm_type' => array(
        'title' => __('Confirmation Type', 'wownero_gateway'),
        'desc_tip' => __('Select the method for confirming transactions', 'wownero_gateway'),
        'description' => __('Select the method for confirming transactions', 'wownero_gateway'),
        'type' => 'select',
        'options' => array(
            'viewkey'        => __('viewkey', 'wownero_gateway'),
            'wownero-wallet-rpc' => __('wownero-wallet-rpc', 'wownero_gateway')
        ),
        'default' => 'viewkey'
    ),
    'wownero_address' => array(
        'title' => __('Wownero Address', 'wownero_gateway'),
        'label' => __('Useful for people that have not a daemon online'),
        'type' => 'text',
        'desc_tip' => __('Wownero Wallet Address (Wownero)', 'wownero_gateway')
    ),
    'viewkey' => array(
        'title' => __('Secret Viewkey', 'wownero_gateway'),
        'label' => __('Secret Viewkey'),
        'type' => 'text',
        'desc_tip' => __('Your secret Viewkey', 'wownero_gateway')
    ),
    'daemon_host' => array(
        'title' => __('Wownero wallet RPC Host/IP', 'wownero_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the Daemon Host/IP to authorize the payment with', 'wownero_gateway'),
        'default' => '127.0.0.1',
    ),
    'daemon_port' => array(
        'title' => __('Wownero wallet RPC port', 'wownero_gateway'),
        'type' => __('number'),
        'desc_tip' => __('This is the Wallet RPC port to authorize the payment with', 'wownero_gateway'),
        'default' => '28080',
    ),
    'testnet' => array(
        'title' => __(' Testnet', 'wownero_gateway'),
        'label' => __(' Check this if you are using testnet ', 'wownero_gateway'),
        'type' => 'checkbox',
        'description' => __('Advanced usage only', 'wownero_gateway'),
        'default' => 'no'
    ),
    'onion_service' => array(
        'title' => __(' SSL warnings ', 'wownero_gateway'),
        'label' => __(' Check to Silence SSL warnings', 'wownero_gateway'),
        'type' => 'checkbox',
        'description' => __('Check this box if you are running on an Onion Service (Suppress SSL errors)', 'wownero_gateway'),
        'default' => 'no'
    ),
    'show_qr' => array(
        'title' => __('Show QR Code', 'wownero_gateway'),
        'label' => __('Show QR Code', 'wownero_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to show a QR code after checkout with payment details.'),
        'default' => 'no'
    ),
    'use_wownero_price' => array(
        'title' => __('Show Prices in Wownero', 'wownero_gateway'),
        'label' => __('Show Prices in Wownero', 'wownero_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to convert ALL prices on the frontend to Wownero (experimental)'),
        'default' => 'no'
    ),
    'use_wownero_price_decimals' => array(
        'title' => __('Display Decimals', 'wownero_gateway'),
        'type' => __('number'),
        'description' => __('Number of decimal places to display on frontend. Upon checkout exact price will be displayed.'),
        'default' => 11,
    ),
);
