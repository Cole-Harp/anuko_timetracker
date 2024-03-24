<form action="gs_choose_tab.php" method="post">
    <table class="centered-table">
        <!-- Spreadsheet ID Dropdown -->
        <tr>
            <td><label for="sheetId">Spreadsheet:</label></td>
            <td>
                <select id="sheetId" name="sheetId">
                    <option value="">Select a Spreadsheet</option>
                    {foreach from=$listOfSpreadsheets key=spreadsheet_id item=title}
                        <option value="{$spreadsheet_id|escape}">{$title|escape}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    </table>
    <div class="button-set">
        <input type="submit" value="Next">
    </div>
</form>
