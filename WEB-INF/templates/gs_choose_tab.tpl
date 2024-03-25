{if $selectedSheetId}
    <div>Selected Sheet Name: {$selectedSheetName}</div>
    <div>Selected Sheet ID: {$selectedSheetId}</div>
{/if}

{$forms.tabsForm.open}
<table class="centered-table">
    <!-- Tab ID Dropdown -->
    <tr>
        <td><label for="tabId">Tab:</label></td>
        <td>{$forms.tabsForm.tabId.control}</td>
    </tr>
    <!-- New Tab Name Input -->
    <tr>
        <td><label for="newTab">New Tab Name:</label></td>
        <td>{$forms.tabsForm.newTab.control}</td>
    </tr>
</table>
<div class="button-set">{$forms.tabsForm.btn_select_tab.control}</div>
{$forms.tabsForm.close}
