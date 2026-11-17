<?php

use Axist\AxKirchenplaner\Api\KirchenplanerApi;

class tx_ax_kirchenplaner_addCustomFieldsToFlexForm
{
    public function addTopics(array &$config)
    {
        $this->addOptions($config, 'themen');
    }

    public function addTargetAudiences(array &$config)
    {
        $this->addOptions($config, 'zielgruppen');
    }

    public function addLocations(array &$config)
    {
        $this->addOptions($config, 'raeume', true);
    }

    public function addCommunities(array &$config)
    {
        $this->addOptions($config, 'organisationen', true);
    }

    private function addOptions(array &$config, $endpoint, $requiresKey = false)
    {
        $api = new KirchenplanerApi();
        if (!isset($config['items'])) {
            $config['items'] = array();
        }
        foreach ($api->getOptions($endpoint, $requiresKey) as $option) {
            $label = $requiresKey ? $option['name'] . ' (' . $option['id'] . ')' : $option['name'];
            $config['items'][] = array($label, $option['id']);
        }
    }
}
