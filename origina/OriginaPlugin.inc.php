<?php
/**
 * @file plugins/generic/origina/OriginaPlugin.inc.php
 * ORIGINA Academic Integrity & Multilingual AI Verification Plugin for OJS 3.3 / 3.4
 */
import('lib.pkp.classes.plugins.GenericPlugin');

class OriginaPlugin extends GenericPlugin {
    public function register($category, $path, $mainContextId = null) {
        $success = parent::register($category, $path, $mainContextId);
        if ($success && $this->getEnabled($mainContextId)) {
            HookRegistry::register('LoadHandler', array($this, 'setPageHandler'));
            HookRegistry::register('SubmissionSubmitStep4Form::execute', array($this, 'onSubmissionComplete'));
        }
        return $success;
    }

    public function getDisplayName() {
        return __('plugins.generic.origina.displayName');
    }

    public function getDescription() {
        return __('plugins.generic.origina.description');
    }

    public function getActions($request, $actionArgs) {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }
        $router = $request->getRouter();
        import('lib.pkp.classes.linkAction.request.AjaxModal');
        $actions[] = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        );
        return $actions;
    }

    public function manage($args, $request) {
        if ($request->getUserVar('verb') === 'settings') {
            $this->import('OriginaSettingsForm');
            $form = new OriginaSettingsForm($this, $request->getContext()->getId());
            if ($request->getUserVar('save')) {
                $form->readInputData();
                if ($form->validate()) {
                    $form->execute();
                    return new JSONMessage(true);
                }
            } else {
                $form->initData();
            }
            return new JSONMessage(true, $form->fetch($request));
        }
        return parent::manage($args, $request);
    }

    public function setPageHandler($hookName, $params) {
        $page = $params[0];
        if ($page === 'origina') {
            $this->import('OriginaHandler');
            define('HANDLER_CLASS', 'OriginaHandler');
            return true;
        }
        return false;
    }

    /**
     * Submit a manuscript file to ORIGINA Public REST API (`/api/v1/ojs/submit`).
     */
    public function submitFileToOrigina($contextId, $filePath, $filename, $title, $author, $ojsSubmissionId) {
        $apiBase = rtrim($this->getSetting($contextId, 'apiUrl') ?: 'https://origina.app', '/');
        $apiKey  = trim($this->getSetting($contextId, 'apiKey') ?: '');
        if (!$apiKey || !file_exists($filePath)) {
            return array('error' => 'Missing ORIGINA API key or manuscript file.');
        }

        $ch = curl_init($apiBase . '/api/v1/ojs/submit');
        $postFields = array(
            'document' => new CURLFile($filePath, mime_content_type($filePath) ?: 'application/octet-stream', $filename),
            'title' => $title,
            'author' => $author,
            'ojs_submission_id' => (string)$ojsSubmissionId,
            'journal_id' => (string)$contextId,
            'exclude_bibliography' => $this->getSetting($contextId, 'excludeBibliography') ? 'true' : 'false',
            'exclude_quotes' => $this->getSetting($contextId, 'excludeQuotes') ? 'true' : 'false',
            'no_repository' => 'true',
        );
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $apiKey),
        ));
        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return array('error' => $err);
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : array('status_code' => $code, 'raw' => $raw);
    }

    /**
     * Poll job status via `/api/v1/ojs/status/{job_id}`.
     */
    public function getJobStatus($contextId, $jobId) {
        $apiBase = rtrim($this->getSetting($contextId, 'apiUrl') ?: 'https://origina.app', '/');
        $apiKey  = trim($this->getSetting($contextId, 'apiKey') ?: '');
        $ch = curl_init($apiBase . '/api/v1/ojs/status/' . urlencode($jobId));
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $apiKey),
        ));
        $raw = curl_exec($ch);
        curl_close($ch);
        return json_decode($raw, true) ?: array('status' => 'error');
    }

    public function onSubmissionComplete($hookName, $args) {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        if (!$context || !$this->getSetting($context->getId(), 'autoCheck')) {
            return false;
        }
        $form = $args[0];
        $submission = $form->submission;
        if (!$submission) return false;
        // Editorial team can trigger or monitor scan via ORIGINA API using stored submission ID.
        return false;
    }
}
