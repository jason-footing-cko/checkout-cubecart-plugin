<h2>{$LANG.orders.title_card_details}</h2>
<table width="100%" cellpadding="3" cellspacing="10" border="0">
    <tr>
        <td width="140">{$LANG.user.name_first}</td>
        <td><input type="text" name="firstName" value="{$CUSTOMER.first_name}" required/></td>
    </tr>
    <tr>
        <td width="140">{$LANG.user.name_last}</td>
        <td><input type="text" name="lastName" value="{$CUSTOMER.last_name}" required/></td>
    </tr>
    <tr>
        <td width="140">{$LANG.gateway.card_number}
        <td><input type="text" name="cardNumber" value="" size="16" maxlength="16" autocomplete="off" required/></td>
    </tr>
    <tr>
        <td width="140">{$LANG.gateway.card_expiry_date}</td>
        <td>
            <select name="expirationMonth" >
                {foreach from=$CARD.months item=month}<option value="{$month.value}" {$month.selected}>{$month.display}</option>{/foreach}
            </select> 
            / 
            <select name="expirationYear" >
                {foreach from=$CARD.years item=year}<option value="{$year.value}" {$year.selected}>{$year.value}</option>{/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td width="140">{$LANG.gateway.card_security}
        <td><input type="text" name="cvc2" value="" size="5" maxlength="4" class="textbox_small" style="text-align: center" required autocomplete="off"/>
            <a href="images/general/cvv.gif" class="colorbox" title="{$LANG.gateway.card_security}" /> {$LANG.common.whats_this}</a>
        </td>
    </tr>
</table>
