<form action="/civicrm/bisnode/import" method="post" name="DataSource" id="DataSource" enctype="multipart/form-data" >
  <div class="crm-block crm-form-block crm-import-datasource-form-block" id="upload_file">
  <h3>{ts}Téléchargement du fichier{/ts}</h3>
  <p>Charger le fichier CSV Bisnode à importer au format UTF-8 avec ; comme séparateur.</p>
  <table class="form-layout">
    <tbody>
      <tr>
          <td class="label">
            <label for="uploadFile">  {ts}Import Data File{/ts}<span title="This field is required." class="crm-marker">*</span></label>
          </td>
          <td>
            <input type="file" class="form-file required" id="uploadFile" name="uploadFile" maxlength="255" size="30"><br>
        </td>
      </tr>
      <tr>
        <td>
          <input type="checkbox" class="form-checkbox" value="on" name="dry_run" id="dry_run">
          {ts}Dry run{/ts}</input>
        </td>
      </tr>
    </tbody>
  </table>
  </div>
  <div class="crm-submit-buttons">
    <a class="button" onclick="cj(this).closest('form').submit();" >
        <span><i class="crm-i fa-upload"></i>&nbsp;{ts}Import{/ts}</span>
    </a>
  </div>
</form>
