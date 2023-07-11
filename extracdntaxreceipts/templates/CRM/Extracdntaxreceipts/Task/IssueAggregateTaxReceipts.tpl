{* Confirmation of tax receipts  *}
<div class="crm-block crm-content-block crm-contribution-view-form-block">
  <h3>Receipts Details</h3>
  <table class="crm-stripes-rows crm-info-panel border-top-td crm-stripes-tr">
    <tr>
      <td class="label bold-text">{ts}Tax Year{/ts}</td>
      <td id="receipt_year">
        {$form.receipt_year.html}
      </td>
      <td class="label display-cell-padding bold-weight">{ts}Contributions{/ts}</td>
      {math equation="(x + y)" x=$receiptList.original.$defaultYear.total_contrib y=$receiptList.duplicate.$defaultYear.total_contrib assign="count_contributions"}
      <td id="count_contributions">{$count_contributions}</td>
      <td></td>
    </tr>
    <tr>
      <td class="label bold-weight">{ts}Eligible Contacts{/ts}</td>
      {assign var="total_contacts" value="`$receiptList.original.$defaultYear.total_contacts`"}
      <td id="total_contacts" class="label">{$total_contacts}</td>
      <td class="label display-cell-padding bold-weight">{ts}Eligible Contributions{/ts}</td>
      {math equation="(x - y)" x=$receiptList.original.$defaultYear.total_contrib y=$receiptList.original.$defaultYear.not_eligible assign="total_contributions"}
      <td id="total_contributions" class="label">{$total_contributions}</td>
      <td></td>
    </tr>
    <tr>
      <td class="label bold-weight">{ts}Total Eligible Amount{/ts}</td>
      <td id="total_amount" class="display-cell-padding-right">{$receiptList.totals.total_eligible_amount.$defaultYear|crmMoney}</td>
      <td class="label display-cell-padding bold-weight">{ts}Ineligible Contributions{/ts}</td>
      <td id="skipped_contributions" class="label display-cell-padding-right">{$receiptList.original.$defaultYear.not_eligible+$receiptList.duplicate.$defaultYear.total_contrib}</td>
      <td></td>
    </tr>
  </table>
</div>

<div class="crm-block crm-content-block crm-contribution-thank-you-block">
  <h3>{ts domain='org.civicrm.cdntaxreceipts'}Thank You Settings{/ts}</h3>
  <table class="crm-info-panel">
    <tr>
      <td class="content">{$form.thankyou_date.html}</td>
      <td class="label">{$form.thankyou_date.label}</td>
    </tr>
    <tr>
      <td class="content">{$form.thankyou_email.html}</td>
      <td class="label">{$form.thankyou_email.label}</td>
    </tr>
    {include file="CRM/Cdntaxreceipts/Task/PDFLetterCommon.tpl"}
  </table>
</div>

<div class="crm-block crm-content-block">
  <h3>{ts domain='org.civicrm.cdntaxreceipts'}Table of Users{/ts}</h3>
  {include file="CRM/Cdntaxreceipts/Task/ContributionTable.tpl"}
</div>

<div class="hidden-receipt-page">
  <p>{$form.receipt_option.original_only.html}<br />
     {$form.receipt_option.include_duplicates.html}</p>
  <p>{ts domain='org.civicrm.cdntaxreceipts'}Clicking 'Issue Tax Receipts' will issue the selected tax receipts.
    <strong>This action cannot be undone.</strong> Tax receipts will be logged for auditing purposes,
    and a copy of each receipt will be submitted to the tax receipt archive.{/ts}</p>
</div>
<div class="crm-block crm-content-block crm-contribution-view-form-block">
  <h3>{ts domain='org.civicrm.cdntaxreceipts'}Delivery Method{/ts}</h3>
  <table class="crm-info-panel">
    <tr>
      <td class="label bold-text">{$form.delivery_method.label}</td>
      <td class="content">{$form.delivery_method.html}</td>
    </tr>
  </table>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
</div>

{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      var receipts = {/literal}{$receiptList|@json_encode}{literal};
      var receiptTypes = {/literal}{$receiptTypes|@json_encode}{literal};
      $("#receipt_year").change(function(){
        var tax_year = $('option:selected', this).text();
        var total_contributions = receipts.original[tax_year].total_contrib-receipts.original[tax_year].not_eligible;
        var total_amount = receipts.totals.total_eligible_amount[tax_year];
        var count_contributions = receipts.original[tax_year].total_contrib + receipts.duplicate[tax_year].total_contrib;
        var total_contacts = receipts.original[tax_year].total_contacts;
        var myTable = '';
        $('#total_contributions').text(total_contributions);
        $('#count_contributions').text(count_contributions);
        $('#total_contacts').text(total_contacts);
        $('#total_amount').text("$ "+ (total_amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
        $('#skipped_contributions').text(receipts.original[tax_year].not_eligible+receipts.duplicate[tax_year].total_contrib);

        //Append new values for table-of-users
        $.each(receiptTypes, function(key, receiptType){
          $.each(receipts[receiptType][tax_year].contact_ids, function(cid, contact){
            $.each(contact.contributions, function(contrId, contribution){
              if(!contribution.campaign) {
                contribution.campaign = '';
              }
              if(contribution.eligible === true) {
                contribution.eligible_status = 'Eligible';
                contribution.eligibility_reason_contri = '';
              } else {
                contribution.eligible_status = 'Not Eligible';
                contribution.eligibility_reason_contri = contribution.eligibility_reason;
                if(!contribution.eligibility_reason) {
                  contribution.eligibility_reason_contri = '';
                }
              }
              var contactUrl = '/dms/contact/view?reset=1&cid='+contribution.contact_id;
              var contributionUrl = '/dms/contact/view/contribution?reset=1&cid='+contribution.contact_id+'&id='+contribution.contribution_id+'&action=view&context=search&selectedChild=contribute';
              myTable += '<tr class="'+receiptType+'-receipt-contributions contribution-id-'+contribution.contribution_id+'"><td>'+contribution.receive_date+'<br/>'+contribution.receive_time+'</td><td><a href="'+contactUrl+'">'+contact.display_name+'</a></td><td><a href="'+contributionUrl+'">$&nbsp;'+contribution.total_amount+'</a></td><td>'+contribution.fund+'</td><td>'+contribution.campaign+'</td><td>'+contribution.contribution_source+'</td><td>'+contribution.payment_instrument+'</td><td>'+contribution.contribution_status+'</td><td>'+contribution.eligible_status+'<br/>'+contribution.eligibility_reason_contri+'</td></tr>';
            });
          });
        });
        $('.table-of-users tbody').html(myTable);
      });
    });
  </script>
{/literal}
