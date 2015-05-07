<?php
/**
* CubeCart v6
* ========================================
* CubeCart is a registered trade mark of CubeCart Limited
* Copyright CubeCart Limited 2014. All rights reserved.
* UK Private Limited Company No. 5323904
* ========================================
* Web:   http://www.cubecart.com
* Email:  sales@devellion.com
* License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
*/
?>
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
    <div id="CheckoutApipayment" class="tab_content">
        <h3>{$TITLE}</h3>
        <fieldset><legend>{$LANG.module.cubecart_settings}</legend>
            <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
            <div><label for="position">{$LANG.module.position}</label><span><input type="text" name="module[position]" id="position" class="textbox number" value="{$MODULE.position}" /></span></div>

            <div><label for="default">{$LANG.common.default}</label><span><input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" /></span></div>
            <div><label for="description">{$LANG.common.description}</label><span><input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>
            <div><label for="secretkey">{$LANG.checkoutapipayment.secret_key}</label><span><input name="module[secretkey]" id="secretkey" class="textbox" type="text" value="{$MODULE.secretkey}" /></span></div>
            <div><label for="publickey">{$LANG.checkoutapipayment.public_key}</label><span><input name="module[publickey]" id="publickey" class="textbox" type="text" value="{$MODULE.publickey}" /></span></div>
            <div>
                <label for="type">{$LANG.checkoutapipayment.type}</label>
                <span>
                    <select name="module[type]">
                        <option value="pci" {$SELECT_type_pci}>{$LANG.checkoutapipayment.pci}</option>
                        <option value="nonpci" {$SELECT_type_nonpci}>{$LANG.checkoutapipayment.nonpci}</option>
                    </select>
                </span>
            </div>
            <div>
                <label for="payment_action">{$LANG.checkoutapipayment.payment_type}</label>
                <span>
                    <select name="module[payment_type]">
                        <option value="AUTH_CAPTURE" {$SELECT_payment_type_AUTH_CAPTURE}>{$LANG.checkoutapipayment.auth_capture}</option>
                        <option value="AUTH_ONLY" {$SELECT_payment_type_AUTH_ONLY}>{$LANG.checkoutapipayment.auth_only}</option>
                    </select>
                </span>
            </div>
            <div>
                <label for="mode">{$LANG.checkoutapipayment.mode}</label>
                <span>
                    <select name="module[mode]">
                        <option value="test" {$SELECT_mode_test}>{$LANG.checkoutapipayment.test}</option>
                        <option value="preprod" {$SELECT_mode_preprod}>{$LANG.checkoutapipayment.preprod}</option>
                        <option value="live" {$SELECT_mode_live}>{$LANG.checkoutapipayment.live}</option>
                    </select>
                </span>
            </div>

            <div><label for="local_payment">{$LANG.checkoutapipayment.local_payment}</label><span><input type="hidden" name="module[local_payment]" id="local_payment" class="toggle" value="{$MODULE.local_payment}" /></span></div>

            <div><label for="autocaptime">{$LANG.checkoutapipayment.autocaptime}</label><span><input name="module[autocaptime]" id="autocaptime" class="textbox" type="text" value="{$MODULE.autocaptime}" /></span></div>
            <div><label for="timeout">{$LANG.checkoutapipayment.timeout}</label><span><input name="module[timeout]" id="timeout" class="textbox" type="text" value="{$MODULE.timeout}" /></span></div>


        </fieldset>

    </div>
    {$MODULE_ZONES}
    <div class="form_control">
        <input type="submit" name="save" value="{$LANG.common.save}" />
    </div>

    <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>