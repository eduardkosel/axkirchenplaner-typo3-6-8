<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    array(
        'Kirchenplaner Termine',
        'axkirchenplaner_termine',
        class_exists('TYPO3\CMS\Core\Imaging\IconRegistry') ? 'kirchenplaner' : '../typo3conf/ext/ax_kirchenplaner/ext_icon.gif'
    ),
    'CType',
    'ax_kirchenplaner'
);

$GLOBALS['TCA']['tt_content']['types']['axkirchenplaner_termine']['showitem'] =
    '--palette--;;general,header,pi_flexform,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,--palette--;;hidden,--palette--;;access';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:ax_kirchenplaner/Configuration/FlexForms/' . ((int)TYPO3_version === 6 ? 'setupV6.xml' : 'setup.xml'),
    'axkirchenplaner_termine'
);
