<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (version_compare(TYPO3_version, '6.2.0', '<')) {
    require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('ax_kirchenplaner')
        . 'Configuration/TCA/Overrides/tt_content.php';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'ax_kirchenplaner',
        'Configuration/TypoScript',
        'Kirchenplaner'
    );
}
