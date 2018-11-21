<?php foreach($errors as $error): ?>
<div class="error"><p><strong>Wownero Gateway Error</strong>: <?php echo $error; ?></p></div>
<?php endforeach; ?>

<h1>Wownero Gateway Settings</h1>

<?php if($confirm_type === 'wownero-wallet-rpc'): ?>
<div style="border:1px solid #ddd;padding:5px 10px;">
    <?php
         echo 'Wallet height' . $balance['height'] . '</br>';
         echo 'Your balance is: ' . $balance['balance'] . '</br>';
         echo 'Unlocked balance: ' . $balance['unlocked_balance'] . '</br>';
         ?>
</div>
<?php endif; ?>

<table class="form-table">
    <?php echo $settings_html ?>
</table>

<h4><a href="https://github.com/monero-integrations/monerowp">Learn more about using the Wownero payment gateway</a></h4>

<script>
function wowneroUpdateFields() {
    var confirmType = jQuery("#woocommerce_wownero_gateway_confirm_type").val();
    if(confirmType == "wownero-wallet-rpc") {
        jQuery("#woocommerce_wownero_gateway_wownero_address").closest("tr").hide();
        jQuery("#woocommerce_wownero_gateway_viewkey").closest("tr").hide();
        jQuery("#woocommerce_wownero_gateway_daemon_host").closest("tr").show();
        jQuery("#woocommerce_wownero_gateway_daemon_port").closest("tr").show();
    } else {
        jQuery("#woocommerce_wownero_gateway_wownero_address").closest("tr").show();
        jQuery("#woocommerce_wownero_gateway_viewkey").closest("tr").show();
        jQuery("#woocommerce_wownero_gateway_daemon_host").closest("tr").hide();
        jQuery("#woocommerce_wownero_gateway_daemon_port").closest("tr").hide();
    }
    var useWowneroPrices = jQuery("#woocommerce_wownero_gateway_use_wownero_price").is(":checked");
    if(useWowneroPrices) {
        jQuery("#woocommerce_wownero_gateway_use_wownero_price_decimals").closest("tr").show();
    } else {
        jQuery("#woocommerce_wownero_gateway_use_wownero_price_decimals").closest("tr").hide();
    }
}
wowneroUpdateFields();
jQuery("#woocommerce_wownero_gateway_confirm_type").change(wowneroUpdateFields);
jQuery("#woocommerce_wownero_gateway_use_wownero_price").change(wowneroUpdateFields);
</script>

<style>
#woocommerce_wownero_gateway_wownero_address,
#woocommerce_wownero_gateway_viewkey {
    width: 100%;
}
</style>
