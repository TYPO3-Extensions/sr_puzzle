<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// Include page TS configuration for new element wizard
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/PageTS/modWizards.txt">');

// Configuring the plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	// The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
	$_EXTKEY,
	// A unique name of the plugin in UpperCamelCase
	'Puzzle',
	// An array holding the controller-action-combinations that are accessible
	// The first controller and its first action will be the default
	array (
		'Puzzle' => 'show',
	),
	// An array of non-cachable controller-action-combinations (they must already be enabled)
	array(
	),
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

?>
