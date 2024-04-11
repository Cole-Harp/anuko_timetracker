{$forms.tabsForm.open}
  <table class="centered-table">
    {if $error_message}
      <tr>
        <td colspan="2" style="text-align:left; padding-top: 8px; padding-bottom: 8px;">
          <strong>{$error_message}</strong>
        </td>
      </tr>
    {/if}
    <tr>
      <td colspan="2" style="text-align:left; padding-top: 10px; padding-bottom: 8px;">
        Selected Sheet Name: <strong>{$selectedSheetName}</strong>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:left; padding-top: 10px; padding-bottom: 8px;">
        Selected Sheet ID: <strong>{$selectedSheetId}</strong>
      </td>
    </tr>
    <!-- Tab ID Dropdown -->
    <tr>
      <td><label name="tabId" for="tabId">Tab:</label></td>
      <td>{$forms.tabsForm.tabId.control}</td>
    </tr>
    <!-- New Tab Name Input -->
    <tr>
      <td><label name="newTab" for="newTab">New Tab Name:</label></td>
      <td>{$forms.tabsForm.newTab.control}</td>
    </tr>
  </table>
  <div class="button-set">{$forms.tabsForm.btn_select_tab.control}</div>
{$forms.tabsForm.close}