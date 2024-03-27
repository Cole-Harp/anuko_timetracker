{$forms.googleSheetsForm.open}
<table class="centered-table">

  <!--Spreadsheet ID Dropdown-->
    <tr class="small-screen-label">
        <td><label for="sheetId">Spreadsheet:</label></td>
    </tr>
    <tr>
        <td class="large-screen-label"><label for="sheetId">Spreadsheet:</label></td>
        <td class="td-with-input">{$forms.googleSheetsForm.sheetId.control}</td>
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
<div class="button-set">{$forms.googleSheetsForm.btn_add.control}</div>
<div class="button-set">{$forms.googleSheetsForm.btn_delete.control}</div>
{$forms.googleSheetsForm.close}
