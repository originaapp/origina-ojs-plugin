<script>
  $(function() {
    $('#originaSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
  });
</script>
<form class="pkp_form" id="originaSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
  {csrf}
  {fbvFormArea id="originaSettingsArea" title="plugins.generic.origina.displayName"}
    {fbvFormSection title="plugins.generic.origina.settings.apiUrl" for="apiUrl" required=true}
      {fbvElement type="text" id="apiUrl" value=$apiUrl required=true}
    {/fbvFormSection}
    {fbvFormSection title="plugins.generic.origina.settings.apiKey" for="apiKey" required=true}
      {fbvElement type="text" id="apiKey" value=$apiKey required=true}
    {/fbvFormSection}
    {fbvFormSection list=true}
      {fbvElement type="checkbox" id="autoCheck" label="plugins.generic.origina.settings.autoCheck" checked=$autoCheck}
      {fbvElement type="checkbox" id="excludeBibliography" label="plugins.generic.origina.settings.excludeBibliography" checked=$excludeBibliography}
      {fbvElement type="checkbox" id="excludeQuotes" label="plugins.generic.origina.settings.excludeQuotes" checked=$excludeQuotes}
    {/fbvFormSection}
  {/fbvFormArea}
  {fbvFormButtons}
</form>
