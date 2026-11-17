<?php

namespace Axist\AxKirchenplaner\Api;

class KirchenplanerApi
{
    private $configuration;

    public function __construct()
    {
        $serialized = isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ax_kirchenplaner'])
            ? $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ax_kirchenplaner']
            : '';
        $configuration = $serialized !== '' ? unserialize($serialized) : array();
        $this->configuration = is_array($configuration) ? $configuration : array();
    }

    public function getBaseUrl()
    {
        $url = isset($this->configuration['apiUrl']) ? $this->configuration['apiUrl'] : 'https://www.portal.kirchenplaner.de/';
        return rtrim($url, '/');
    }

    public function getKey()
    {
        return isset($this->configuration['apiKey']) ? $this->configuration['apiKey'] : '';
    }

    public function getUserGroup()
    {
        $groups = explode('|', isset($this->configuration['userGroup']) ? $this->configuration['userGroup'] : '4', 2);
        return $groups[0];
    }

    public function getOptions($endpoint, $requiresKey = false)
    {
        $baseUrl = $this->getBaseUrl();
        if ($baseUrl === '' || ($requiresKey && $this->getKey() === '')) {
            return array();
        }

        $url = $baseUrl . '/api/' . $endpoint;
        if ($requiresKey) {
            $url .= '/' . rawurlencode(json_encode(array('key' => $this->getKey())));
        }
        $host = parse_url($url, PHP_URL_HOST);
        $context = stream_context_create(array(
            'http' => array('timeout' => 5),
            'ssl' => array('verify_peer' => true, 'verify_peer_name' => true, 'CN_match' => $host),
        ));
        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            return array();
        }

        $rows = json_decode($body, true);
        if (!is_array($rows)) {
            $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
            $rows = json_decode($body, true);
        }
        if (!is_array($rows)) {
            $rows = array();
            foreach (preg_split('/\R/', $body) as $line) {
                $rows[] = explode('|', trim($line), 2);
            }
        }

        $options = array();
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row[0], $row[1]) || !is_scalar($row[0]) || !is_scalar($row[1])) {
                continue;
            }
            $id = trim((string)$row[1]);
            if ($id !== '') {
                $options[] = array(
                    'id' => $id,
                    'name' => html_entity_decode(trim((string)$row[0]), ENT_QUOTES, 'UTF-8'),
                );
            }
        }
        return $options;
    }
}
