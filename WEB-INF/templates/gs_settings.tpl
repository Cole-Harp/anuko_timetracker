{$forms.settingsForm.open}  
<table class="centered-table">

    <!-- New Sheet ID Input -->
    <tr class="small-screen-label">
        <td><label for="addSheetId">Existing Spreadsheet ID:</label></td>
    </tr>
    <tr>
        <td class="large-screen-label"><label for="addSheetId">Existing Spreadsheet ID:</label></td>
        <td class="td-with-input">{$forms.settingsForm.addSheetId.control}</td>
    </tr>
    <div class="button-set">{$forms.settingsForm.btn_add_sheet.control}</div>
    <!-- Spreadsheet ID Dropdown -->
    <tr class="small-screen-label">
        <td><label for="sheetId">Spreadsheet:</label></td>
    </tr>
    <tr>
        <td class="large-screen-label"><label for="sheetId">Spreadsheet:</label></td>
        <td class="td-with-input">{$forms.settingsForm.sheetId.control}</td>
    </tr>

    <div class="button-set">{$forms.settingsForm.btn_delete_sheet.control}</div>
</table>
{$forms.settingsForm.close}
