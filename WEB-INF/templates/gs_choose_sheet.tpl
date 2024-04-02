
{$forms.googleSheetsForm.open}
<table class="centered-table">
  {if $error_message}
  <tr>
    <td colspan="2" style="text-align:left; padding-top: 8px; padding-bottom: 8px;">
      <strong>{$error_message}</strong>
    </td>
  </tr>
  {/if}
  <!--Spreadsheet ID Dropdown-->
    <tr>
      <td colspan="2" style="text-align:left; padding-top: 8px; padding-bottom: 8px;">
        <strong>Update Existing Sheet:</strong>
      </td>
    </tr>
    <tr class="small-screen-label">
        <td>
          label for="sheetId">Spreadsheet:</label>
        </td>
    </tr>
    <tr>
        <td class="large-screen-label"><label for="sheetId">Spreadsheet:</label></td>
        <td class="td-with-input">{$forms.googleSheetsForm.sheetId.control}</td>
    </tr>
  <!-- Folder ID Dropdown -->
    <tr>
      <td colspan="2" style="text-align:left; padding-top: 10px; padding-bottom: 8px;">
        <strong>Create New Sheet:</strong>
      </td>
    </tr>
    <tr class="small-screen-label">
        <td><label for="folderId">Folder:</label></td>
    </tr>
    <tr>
        <td class="large-screen-label"><label for="folderId">Folder:</label></td>
        <td class="td-with-input">{$forms.googleSheetsForm.folderId.control}</td>
    </tr>
  <!-- New Spreadsheet Input -->
    <tr class="small-screen-label">
        <td><label for="newSheet">New Spreadsheet:</label></td>
    </tr>
    <tr>
      <td class="large-screen-label"><label for="newSheet">New Spreadsheet:</label></td>
      <td class="td-with-input">{$forms.googleSheetsForm.newSheet.control}</td>
    </tr>
  <tr><td><div class="small-screen-form-control-separator"></div></td></tr>
</table>
<div class="button-set">{$forms.googleSheetsForm.btn_send.control}</div>
<div class="button-set">{$forms.googleSheetsForm.btn_settings.control}</div>
{$forms.googleSheetsForm.close}
