<?php
/**
 * @file plugins/generic/origina/OriginaSettingsForm.inc.php
 */
import('lib.pkp.classes.form.Form');

class OriginaSettingsForm extends Form {
    public $plugin;
    public $contextId;

    public function __construct($plugin, $contextId) {
        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
        $this->plugin = $plugin;
        $this->contextId = $contextId;
        $this->addCheck(new FormValidator($this, 'apiUrl', 'required', 'plugins.generic.origina.settings.apiUrlRequired'));
        $this->addCheck(new FormValidator($this, 'apiKey', 'required', 'plugins.generic.origina.settings.apiKeyRequired'));
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    public function initData() {
        $this->setData('apiUrl', $this->plugin->getSetting($this->contextId, 'apiUrl') ?: 'https://origina.app');
        $this->setData('apiKey', $this->plugin->getSetting($this->contextId, 'apiKey') ?: '');
        $this->setData('autoCheck', (bool)$this->plugin->getSetting($this->contextId, 'autoCheck'));
        $this->setData('excludeBibliography', $this->plugin->getSetting($this->contextId, 'excludeBibliography') !== false);
        $this->setData('excludeQuotes', $this->plugin->getSetting($this->contextId, 'excludeQuotes') !== false);
    }

    public function readInputData() {
        $this->readUserVars(array('apiUrl', 'apiKey', 'autoCheck', 'excludeBibliography', 'excludeQuotes'));
    }

    public function execute(...$functionArgs) {
        $this->plugin->updateSetting($this->contextId, 'apiUrl', trim($this->getData('apiUrl')), 'string');
        $this->plugin->updateSetting($this->contextId, 'apiKey', trim($this->getData('apiKey')), 'string');
        $this->plugin->updateSetting($this->contextId, 'autoCheck', (bool)$this->getData('autoCheck'), 'bool');
        $this->plugin->updateSetting($this->contextId, 'excludeBibliography', (bool)$this->getData('excludeBibliography'), 'bool');
        $this->plugin->updateSetting($this->contextId, 'excludeQuotes', (bool)$this->getData('excludeQuotes'), 'bool');
        parent::execute(...$functionArgs);
    }
}
