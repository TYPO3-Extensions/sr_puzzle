<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/PluginSetup', 'Puzzle Setup');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/DefaultStyles', 'Puzzle CSS Styles');

/**
 * Registers the plugin to be listed in the Backend
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	// The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
	$_EXTKEY,
	// A unique name of the plugin in UpperCamelCase
	'Puzzle',
	// A title shown in the backend dropdown field
	'LLL:EXT:sr_puzzle/Resources/Private/Language/locallang.xlf:pi1_title'
);

if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 6001000) {
	\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('tt_content');
}
$tempColumns = Array (
	'tx_srpuzzle_piece_size' => Array (
		'exclude' => 0,
		'label' => 'LLL:EXT:sr_puzzle/Resources/Private/Language/locallang.xlf:settings.pieceSize',
		'config' => Array (
			'type' => 'input',
			'size' => '8',
			'max' => '20',
			'eval' => 'number,required',
			'range' => Array (
				'upper' => 1000,
				'lower' => 0)
		)
	),
	'tx_srpuzzle_offsetX' => Array (
		'exclude' => 0,	
		'label' => 'LLL:EXT:sr_puzzle/Resources/Private/Language/locallang.xlf:settings.offsetX',
		'config' => Array (
			'type' => 'input',
			'size' => '8',
			'max' => '20',
			'eval' => 'number',
			'default' => 0,
			'range' => Array (
				'upper' => 1000,
				'lower' => 0)
		)
	),
	'tx_srpuzzle_offsetY' => Array (
		'exclude' => 0,	
		'label' => 'LLL:EXT:sr_puzzle/Resources/Private/Language/locallang.xlf:settings.offsetY',
		'config' => Array (
			'type' => 'input',
			'size' => '8',
			'max' => '20',
			'eval' => 'number',
			'default' => 0,
			'range' => Array (
				'upper' => 1000,
				'lower' => 0)
		)
	),
	'tx_srpuzzle_link_after' => Array (
		'exclude' => 1, 
		'label' => 'LLL:EXT:sr_puzzle/Resources/Private/Language/locallang.xlf:settings.linkAfter',
		'config' => Array (
			'type' => 'input',
			'size' => '15',
			'max' => '256',
			'checkbox' => '',
			'eval' => 'trim',
			'wizards' => Array(
				'_PADDING' => 2,
				'link' => Array(
					'type' => 'popup',
					'title' => 'Link',
					'icon' => 'link_popup.gif',
					'script' => 'browse_links.php?mode=wizard',
					'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
				)
			)
		)
	),
	'tx_srpuzzle_popup_link' => Array (
		'exclude' => 1, 
		'label' => 'LLL:EXT:sr_puzzle/Resources/Private/Language/locallang.xlf:settings.congratulationsLink',
		'config' => Array (
			'type' => 'input',
			'size' => '15',
			'max' => '256',
			'checkbox' => '',
			'eval' => 'trim',
			'wizards' => Array(
				'_PADDING' => 2,
				'link' => Array(
					'type' => 'popup',
					'title' => 'Link',
					'icon' => 'link_popup.gif',
					'script' => 'browse_links.php?mode=wizard',
					'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
				)
			)
		)
	),
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumns, 1);

$pluginSignature = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY)) . '_puzzle';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('*', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/form.xml', $pluginSignature);

$GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = '--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.general;general';
$GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] .= ',--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.headers;headers';
$GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] .= ',--div--;LLL:EXT:sr_puzzle/Resources/Private/Language/locallang.xlf:pi1_title;;;;3-3-3, pi_flexform';
$GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] .= ',--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access, --palette--;LLL:EXT:cms/locallang_ttc.xml:palette.visibility;visibility, --palette--;LLL:EXT:cms/locallang_ttc.xml:palette.access;access';
$GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] .= ',--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.appearance, --palette--;LLL:EXT:cms/locallang_ttc.xml:palette.frames;frames';
$GLOBALS['TCA']['tt_content']['ctrl']['typeicons'][$pluginSignature] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.png';

?>