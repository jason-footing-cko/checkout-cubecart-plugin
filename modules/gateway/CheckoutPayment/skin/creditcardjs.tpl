<h4>Please select a Credit / Debit card</h4>
<div class="widget-container"></div>
<div class="content" id="payment">
    <input type="hidden" name="cko-cc-paymenToken" id="cko-cc-paymenToken" value="">
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#gateway-transfer .button.success').click(function (e) {
            jQuery(this).prop("disabled", true);
            e.preventDefault();
            CheckoutIntegration.open();
        });
    });
</script>
<script type="text/javascript">
    
    window.CKOConfig = {
        debugMode: false,
        renderMode: 2,
        namespace: 'CheckoutIntegration',
        publicKey: '{$CheckoutapiData.public_key}',
        paymentToken: '{$CheckoutapiData.paymentToken}',
        value: '{$CheckoutapiData.value}',
        currency: '{$CheckoutapiData.currency}',
        customerEmail: '{$CheckoutapiData.email}',
        customerName: '{$CheckoutapiData.name}',
        title: '',
        subtitle: 'Please enter your credit card details',
        widgetContainerSelector: '.widget-container',
        cardCharged: function (event) {
            document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
            jQuery('#gateway-transfer').submit();
        },
        lightboxDeactivated: function () {
            jQuery("#gateway-transfer .button.success").prop("disabled", false);
        }
    };
</script>
{if $checkoutapiData.mode =='live'}
        <script src="https://www.checkout.com/cdn/js/checkout.js" async ></script>
{else}
        <script src="//sandbox.checkout.com/js/v1/checkout.js" async ></script>
{/if}