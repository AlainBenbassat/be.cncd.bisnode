<table id="contact-activity-selector-dashlet">
<thead>
  <tr>
<!--    <th colspan="1" rowspan="1" class="ui-state-default">
	<div class="DataTables_sort_wrapper"><input type="checkbox" id="form_selector"><span class="DataTables_sort_icon"></span></div>
    </th>-->
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}File{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}Status{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}Records{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}Importation date{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}Errors{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="hiddenElement ui-state-default">
	<div class="DataTables_sort_wrapper">&nbsp;</div>
    </th>
  </tr>
</thead>
<tbody>
  {foreach from=$files item=file key=fieldName}
  <tr class="odd ">
<!--    <td><input id="check_{$form.id}" type="checkbox"></td>-->
    <td>{$file.name}</td>
    <td>{$file.status}</td>
    <td>{$file.records}</td>
    <td>{$file.last_importation_time|date_format:"%d/%m/%y - %H:%M"}</td>
    <td>{$file.errors}</td>
    <td><a href="{$file.id}">{ts}Details{/ts}</a> 
        <a href="../import?file_id={$file.id}">{ts}Import{/ts}</a>
    </td>
  </tr>
  {/foreach}
</tbody>
</table>

<div>
  <a class="button crm-extensions-refresh" id="new" onClick="update(this);">
    <div class="icon inform-icon"></div>{ts}Refresh{/ts}
  </a>
  <img name='busy' src="{$config->resourceBase}i/loading.gif" hidden="1"/>
</div>

{literal}
<script type="text/javascript">
  // UPDATE BUTTONS
  function update(button) {
    location.reload(); 
  }
</script>
{/literal}

