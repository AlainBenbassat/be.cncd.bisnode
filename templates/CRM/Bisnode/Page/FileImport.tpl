
<h3>{ts}Logs{/ts}</h3>
<h4>{ts}Download{/ts}</h4>
<p>
{foreach from=$logs_download item=log}
{$log}<br>
{/foreach}
</p>

<h4>{ts}Import{/ts}</h4>
<p>
{foreach from=$logs_import item=log}
{$log}<br>
{/foreach}
</p>

<h4>{ts}Summary{/ts}</h4>
<p>
{$logs_summary}
</p>

<div>
  <a class="button" href="{crmURL p='civicrm/bisnode'}">
    <div class="icon inform-icon"></div>{ts}Done{/ts}
  </a>
</div>
