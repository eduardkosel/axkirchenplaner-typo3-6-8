<?php

namespace Axist\AxKirchenplaner\Controller;

use Axist\AxKirchenplaner\Api\KirchenplanerApi;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class KirchenplanerController extends ActionController
{
    public function showAction()
    {
        $baseUrl = rtrim($this->request->getBaseUri(), '/') . '/';
        $contentObject = $this->configurationManager->getContentObject();
        $contentData = isset($contentObject->data) ? $contentObject->data : array();
        $contentUid = (int)$this->value($contentData, 'uid', 0);
        $queryParams = $_GET;

        $page = $this->value($this->settings, 'pageconfig', array());
        $query = $this->value($this->settings, 'query', array());
        $search = $this->value($this->settings, 'search', array());
        $api = new KirchenplanerApi();
        $assetPath = ExtensionManagementUtility::siteRelPath('ax_kirchenplaner') . 'Resources/Public';
        $publicResources = $baseUrl . $assetPath;

        $pageRenderer = is_callable(array($GLOBALS['TSFE'], 'getPageRenderer'))
            ? $GLOBALS['TSFE']->getPageRenderer()
            : GeneralUtility::makeInstance('TYPO3\CMS\Core\Page\PageRenderer');
        $this->addAssets($pageRenderer, $assetPath, $this->value($page, 'framework'));

        $templateName = ($this->value($page, 'seitentyp')) === 'details' ? 'defaultDetail' : 'defaultListing';
        $htmlTemplate = $this->getFileUrl($this->value($page, 'templatedatei'), 'templatedatei', $contentUid, $baseUrl)
            ?: $publicResources . '/Angular/templates/' . $templateName . '.html';
        $customCss = $this->getFileUrl($this->value($page, 'cssdatei'), 'cssdatei', $contentUid, $baseUrl);
        if ($customCss !== null) {
            $cssPath = strpos($customCss, $baseUrl) === 0 ? substr($customCss, strlen($baseUrl)) : $customCss;
            $pageRenderer->addCssFile($cssPath, 'stylesheet', 'all', '', false, false);
        }

        $detailsPage = '';
        if (!empty($page['detailseite'])) {
            $detailsPage = $this->uriBuilder->reset()
                ->setTargetPageUid((int)$page['detailseite'])
                ->setCreateAbsoluteUri(true)
                ->build();
        }

        $searchCommunities = array();
        if (!empty($search['overridegemeinden'])) {
            foreach (explode(',', $search['overridegemeinden']) as $entry) {
                list($id, $name) = array_pad(explode('|', $entry, 2), 2, '');
                if ($id !== '' && $name !== '') {
                    $searchCommunities[] = array('id' => $id, 'name' => $name);
                }
            }
        } else {
            $searchCommunities = $this->getSearchOptions($api, $this->value($search, 'gemeinden'), 'organisationen', true);
        }

        $config = array(
            'apiKey' => $api->getKey(),
            'apiUrl' => $api->getBaseUrl(),
            'baseUrl' => $baseUrl,
            'pageId' => (string)($this->getQueryValue($queryParams, 'p_id') ?: ($this->value($contentData, 'pid'))),
            'userGroup' => $api->getUserGroup(),
            'topics' => (string)($this->value($query, 'rubriken')),
            'locations' => (string)($this->value($query, 'orte')),
            'targetAudience' => (string)($this->value($query, 'zielgruppen')),
            'htmlTemplate' => $htmlTemplate,
            'modalTemplate' => $publicResources . '/Angular/Controller/kirchenPlanerModal.html',
            'detailLinkType' => (string)($this->value($page, 'detaillinktyp', 'modal')),
            'detailsPage' => $detailsPage,
            'groupItems' => (string)($this->value($page, 'datumgruppieren', 'nein')),
            'borderColors' => (string)($this->value($page, 'randfarben')),
            'showPagination' => (string)($this->value($page, 'paginierungzeigen')),
            'format' => (string)($this->value($query, 'format')),
            'startDate' => $this->formatDate($this->value($query, 'startdatum')),
            'endDate' => $this->formatDate($this->value($query, 'enddatum')),
            'daysToFetch' => (string)($this->value($query, 'abruftage', '10')),
            'communities' => (string)(($this->value($query, 'overridegemeinden')) ?: ($this->value($query, 'gemeinden'))),
            'includeSubCommunities' => (string)($this->value($query, 'unterorganisationen')),
            'itemsPerPage' => (string)($this->value($page, 'eintraegeproseite', '10')),
            'holidays' => (string)($this->value($page, 'feiertage')),
            'additionalItems' => (string)($this->value($page, 'zusatztermine')),
            'items' => (string)($this->value($query, 'termine')),
            'detailItem' => $this->getQueryValue($queryParams, 'v_id'),
            'displaySearch' => (string)($this->value($search, 'anzeigen')),
            'searchWidth' => (string)($this->value($search, 'breite')),
            'searchTextSearch' => (string)($this->value($search, 'textsuche')),
            'searchTextSearchValue' => $this->getQueryValue($queryParams, 's'),
            'searchCalendar' => (string)($this->value($search, 'kalender')),
            'searchPast' => (string)($this->value($search, 'vergangenheit')),
            'searchCategories' => $this->getSearchOptions($api, $this->value($search, 'kategorien'), 'themen'),
            'searchCategoryValue' => $this->getQueryValue($queryParams, 'kategorie'),
            'searchTargetAudiences' => $this->getSearchOptions($api, $this->value($search, 'zielgruppen'), 'zielgruppen'),
            'searchTargetAudienceValue' => $this->getQueryValue($queryParams, 'zielgruppe'),
            'searchLocations' => $this->getSearchOptions($api, $this->value($search, 'orte'), 'raeume', true),
            'searchLocationValue' => $this->getQueryValue($queryParams, 'ort'),
            'searchCommunities' => $searchCommunities,
            'searchCommunityValue' => $this->getQueryValue($queryParams, 'gemeinde'),
        );

        $this->view->assign('uid', $contentUid);
        $configJson = json_encode(
            $config,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        $this->view->assign('configJson', $configJson !== false ? $configJson : '{}');

        return $this->view->render();
    }

    private function addAssets(PageRenderer $pageRenderer, $publicResources, $framework)
    {
        foreach (array('FontAwesome/css/font-awesome.min.css', 'Bootstrap4/css/glyphicons.css', 'Css/kirchenplaner.css') as $file) {
            $pageRenderer->addCssFile($publicResources . '/' . $file, 'stylesheet', 'all', '', false, false);
        }

        $scripts = array(
            'libs/angular.min.js',
            'libs/angular-locale_de-de.js',
            'libs/angular-ui-bootstrap.min.js',
            'libs/angular-filter.min.js',
            'libs/angular-animate.min.js',
            'libs/angular-scroll.min.js',
            'libs/underscore.min.js',
            'app.js',
            'Controller/kirchenPlaner.js',
            'Controller/kirchenPlanerModal.js',
        );
        foreach ($scripts as $file) {
            $pageRenderer->addJsFile($publicResources . '/Angular/' . $file, 'text/javascript', false, false);
        }

        if ($framework === 'bootstrap3' || $framework === 'bootstrap4') {
            $directory = ucfirst($framework);
            $pageRenderer->addCssFile($publicResources . '/' . $directory . '/css/bootstrap.min.css', 'stylesheet', 'all', '', false, true);
            $pageRenderer->addJsFile($publicResources . '/' . $directory . '/js/bootstrap.min.js', 'text/javascript', false, false);
        }
    }

    private function getFileUrl($value, $fieldName, $contentUid, $baseUrl)
    {
        if (!is_scalar($value) || $value === '') {
            return null;
        }
        if (strpos($value, 'fileadmin/') === 0 || strpos($value, 'uploads/') === 0) {
            return $baseUrl . $value;
        }
        if (!preg_match('/^(?:(sys_file_reference_|sys_file_|file:))?([1-9]\d*)$/D', $value, $matches)) {
            return null;
        }
        $uid = (int)$matches[2];
        $resourceFactory = GeneralUtility::makeInstance('TYPO3\CMS\Core\Resource\ResourceFactory');
        $file = null;
        try {
            $reference = $matches[1] === 'sys_file_' || $matches[1] === 'file:'
                ? null : $resourceFactory->getFileReferenceObject($uid);
            $properties = $reference ? $reference->getReferenceProperties() : array();
            if (
                isset($properties['tablenames'], $properties['fieldname'], $properties['uid_foreign'])
                && $properties['tablenames'] === 'tt_content'
                && $properties['fieldname'] === $fieldName
                && (int)$properties['uid_foreign'] === $contentUid
            ) {
                $file = $reference;
            }
        } catch (ResourceDoesNotExistException $exception) {
            // A legacy record may already contain the file UID.
        }

        if ($file === null && $matches[1] !== 'sys_file_reference_') {
            try {
                $file = $resourceFactory->getFileObject($uid);
            } catch (ResourceDoesNotExistException $exception) {
                return null;
            }
        }

        $url = $file ? $file->getPublicUrl() : null;
        if ($url && !preg_match('~^(?:/|[a-z][a-z0-9+.-]*:)~i', $url)) {
            $url = rtrim($baseUrl, '/') . '/' . $url;
        }
        return $url ?: null;
    }

    private function getSearchOptions(KirchenplanerApi $api, $selection, $endpoint, $requiresKey = false)
    {
        if ($selection === '') {
            return array();
        }

        $selected = explode(',', $selection);
        return array_values(array_filter($api->getOptions($endpoint, $requiresKey), function (array $option) use ($selected) {
            return in_array($option['id'], $selected, true);
        }));
    }

    private function value(array $values, $key, $default = '')
    {
        return isset($values[$key]) ? $values[$key] : $default;
    }

    private function getQueryValue(array $params, $name)
    {
        $value = $this->value($params, $name);
        return is_scalar($value) ? (string)$value : '';
    }

    private function formatDate($value)
    {
        if ($value === '' || $value === null || $value === '0' || $value === 0) {
            return '';
        }
        if (is_numeric($value)) {
            return date('Y-m-d', (int)$value);
        }
        return preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) ? $value : '';
    }
}
