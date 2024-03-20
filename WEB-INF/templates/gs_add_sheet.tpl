{$forms.googleSheetsForm.open}
<table class="centered-table">
  <!--Spreadsheet ID Dropdown-->
    <tr class = "small-screen-label"><td><label for="newSheetId">New Spreadsheet ID:</label></td></tr>
    <tr>
      <td class="large-screen-label"><label for="newSheetId">New Spreadsheet ID:</label></td>
      <td class="td-with-input">{$forms.googleSheetsForm.newSheetId.control}</td>
    </tr>
    <tr><td><div class="small-screen-form-control-separator"></div></td></tr>
</table>
<div class="button-set">{$forms.googleSheetsForm.btn_send.control}</div>
{$forms.googleSheetsForm.close}
