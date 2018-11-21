/*
 * Copyright (c) 2018, Ryo Currency Project
*/
function wownero_showNotification(message, type='success') {
    var toast = jQuery('<div class="' + type + '"><span>' + message + '</span></div>');
    jQuery('#wownero_toast').append(toast);
    toast.animate({ "right": "12px" }, "fast");
    setInterval(function() {
        toast.animate({ "right": "-400px" }, "fast", function() {
            toast.remove();
        });
    }, 2500)
}
function wownero_showQR(show=true) {
    jQuery('#wownero_qr_code_container').toggle(show);
}
function wownero_fetchDetails() {
    var data = {
        '_': jQuery.now(),
        'order_id': wownero_details.order_id
    };
    jQuery.get(wownero_ajax_url, data, function(response) {
        if (typeof response.error !== 'undefined') {
            console.log(response.error);
        } else {
            wownero_details = response;
            wownero_updateDetails();
        }
    });
}

function wownero_updateDetails() {

    var details = wownero_details;

    jQuery('#wownero_payment_messages').children().hide();
    switch(details.status) {
        case 'unpaid':
            jQuery('.wownero_payment_unpaid').show();
            jQuery('.wownero_payment_expire_time').html(details.order_expires);
            break;
        case 'partial':
            jQuery('.wownero_payment_partial').show();
            jQuery('.wownero_payment_expire_time').html(details.order_expires);
            break;
        case 'paid':
            jQuery('.wownero_payment_paid').show();
            jQuery('.wownero_confirm_time').html(details.time_to_confirm);
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'confirmed':
            jQuery('.wownero_payment_confirmed').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired':
            jQuery('.wownero_payment_expired').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired_partial':
            jQuery('.wownero_payment_expired_partial').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
    }

    jQuery('#wownero_exchange_rate').html('1 WOW = '+details.rate_formatted+' '+details.currency);
    jQuery('#wownero_total_amount').html(details.amount_total_formatted);
    jQuery('#wownero_total_paid').html(details.amount_paid_formatted);
    jQuery('#wownero_total_due').html(details.amount_due_formatted);

    jQuery('#wownero_integrated_address').html(details.integrated_address);

    if(wownero_show_qr) {
        var qr = jQuery('#wownero_qr_code').html('');
        new QRCode(qr.get(0), details.qrcode_uri);
    }

    if(details.txs.length) {
        jQuery('#wownero_tx_table').show();
        jQuery('#wownero_tx_none').hide();
        jQuery('#wownero_tx_table tbody').html('');
        for(var i=0; i < details.txs.length; i++) {
            var tx = details.txs[i];
            var height = tx.height == 0 ? 'N/A' : tx.height;
            var row = ''+
                '<tr>'+
                '<td style="word-break: break-all">'+
                '<a href="'+wownero_explorer_url+'/tx/'+tx.txid+'" target="_blank">'+tx.txid+'</a>'+
                '</td>'+
                '<td>'+height+'</td>'+
                '<td>'+tx.amount_formatted+' wownero</td>'+
                '</tr>';

            jQuery('#wownero_tx_table tbody').append(row);
        }
    } else {
        jQuery('#wownero_tx_table').hide();
        jQuery('#wownero_tx_none').show();
    }

    // Show state change notifications
    var new_txs = details.txs;
    var old_txs = wownero_order_state.txs;
    if(new_txs.length != old_txs.length) {
        for(var i = 0; i < new_txs.length; i++) {
            var is_new_tx = true;
            for(var j = 0; j < old_txs.length; j++) {
                if(new_txs[i].txid == old_txs[j].txid && new_txs[i].amount == old_txs[j].amount) {
                    is_new_tx = false;
                    break;
                }
            }
            if(is_new_tx) {
                wownero_showNotification('Transaction received for '+new_txs[i].amount_formatted+' wownero');
            }
        }
    }

    if(details.status != wownero_order_state.status) {
        switch(details.status) {
            case 'paid':
                wownero_showNotification('Your order has been paid in full');
                break;
            case 'confirmed':
                wownero_showNotification('Your order has been confirmed');
                break;
            case 'expired':
            case 'expired_partial':
                wownero_showNotification('Your order has expired', 'error');
                break;
        }
    }

    wownero_order_state = {
        status: wownero_details.status,
        txs: wownero_details.txs
    };

}
jQuery(document).ready(function($) {
    if (typeof wownero_details !== 'undefined') {
        wownero_order_state = {
            status: wownero_details.status,
            txs: wownero_details.txs
        };
        setInterval(wownero_fetchDetails, 30000);
        wownero_updateDetails();
        new ClipboardJS('.clipboard').on('success', function(e) {
            e.clearSelection();
            if(e.trigger.disabled) return;
            switch(e.trigger.getAttribute('data-clipboard-target')) {
                case '#wownero_integrated_address':
                    wownero_showNotification('Copied destination address!');
                    break;
                case '#wownero_total_due':
                    wownero_showNotification('Copied total amount due!');
                    break;
            }
            e.clearSelection();
        });
    }
});
