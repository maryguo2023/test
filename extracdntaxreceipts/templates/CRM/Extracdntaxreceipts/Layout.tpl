<div id="rlayout">
    {$form.receipt_layout.html}
</div>

{literal}
    <script type="text/javascript">
        CRM.$(function($) {
           $('#rlayout').insertBefore('.crm-submit-buttons');
        });
    </script>
{/literal}