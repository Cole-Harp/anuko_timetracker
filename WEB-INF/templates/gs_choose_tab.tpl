<form action="gs_upload.php" method="post">
    <table class="centered-table">
        <!-- Tab Selection Dropdown -->
        <tr>
            <td><label for="tabId">Tab:</label></td>
            <td>
                <select id="tabId" name="tabId">
                    <option value="">Select a Tab</option>
                    {foreach from=$tabs item=tab}
                        <option value="{$tab|escape}">{$tab|escape}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    </table>
    <div class="button-set">
        <input type="submit" value="Next">
    </div>
</form>
