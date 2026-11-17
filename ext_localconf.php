<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('ax_kirchenplaner')
    . 'Classes/Api/KirchenplanerApi.php';
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('ax_kirchenplaner')
    . 'Classes/Controller/KirchenplanerController.php';
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('ax_kirchenplaner')
    . 'Classes/class.tx_axkirchenplaner_addCustomFieldsToFlexForm.php';

if (class_exists('TYPO3\CMS\Core\Imaging\IconRegistry')) {
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Imaging\IconRegistry')->registerIcon(
        'kirchenplaner',
        'TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider',
        array('source' => 'EXT:ax_kirchenplaner/Resources/Public/Icons/Kirchenplaner.png')
    );
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Axist.AxKirchenplaner',
    'Termine',
    array('Kirchenplaner' => 'show'),
    array('Kirchenplaner' => 'show'),
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:ax_kirchenplaner/Configuration/PageTS/Mod/ContentElements/Kirchenplaner.ts">'
);
