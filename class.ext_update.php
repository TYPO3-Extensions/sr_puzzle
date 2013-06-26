<?php
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Stanislas Rolland <typo3@sjbr.ca>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
/**
* Class for updating the puzzle configuration
*/
class ext_update {
	 
	/**
	 * Main function, returning the HTML content of the module
	 *
	 * @return string  HTML
	 */
	public function main() {
		$content = array();
		$content[] = '<h3>' . LocalizationUtility::translate('update.upgradeTo6', 'SrPuzzle') . '</h3>';
		if (GeneralUtility::_GP('proceed')) {	
			$content[] = $this->updatePluginInstances();
			$content[] = $this->updateTsTemplates();
			$content[] = '<p>' . LocalizationUtility::translate('update.pleaseRead', 'SrPuzzle') . '</p>';
		} else {
			$linkThisScript = GeneralUtility::linkThisScript();
			$content[] = '<form name="sr_puzzle_ext_update_form" action="' . $linkThisScript . '" method="post">';
			$content[] = '<p><strong>' . LocalizationUtility::translate('update.warning', 'SrPuzzle') . '</strong><br />' . LocalizationUtility::translate('update.changesToDatabase', 'SrPuzzle') . '</p>';
			$content[] = '<input type="submit" name="proceed" value="' . LocalizationUtility::translate('update.update', 'SrPuzzle') . '"  onclick="this.form.action=\'' . GeneralUtility::slashJS($linkThisScript) . '\';submit();" />';
			$content[]= '</form>';
		}
		return implode(LF, $content);
	}

	/**
	 * Updates the instances of the plugin in table tt_content
	 *
	 * @return string  HTML
	 */
	protected function updatePluginInstances() {

		$pluginInstances = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tt_content',
			'CType = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('sr_puzzle_pi1', 'tt_content')
		);

		foreach ($pluginInstances as $row) {
			$update = array(
                    		'CType' => 'srpuzzle_puzzle',
                    		'list_type' => '',
                    		'pi_flexform' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
    <data>
        <sheet index="sDEF">
            <language index="lDEF">
                <field index="settings.image">
                    <value index="vDEF">' . $row['image'] . '</value>
                </field>
                <field index="settings.puzzleWidth">
                    <value index="vDEF">' . $row['imagewidth'] . '</value>
                </field>
                <field index="settings.puzzleHeight">
                    <value index="vDEF">' . $row['imageheight'] . '</value>
                </field>
                <field index="settings.pieceSize">
                    <value index="vDEF">' . $row['tx_srpuzzle_piece_size'] . '</value>
                </field>
                <field index="settings.puzzleX">
                    <value index="vDEF">' . $row['tx_srpuzzle_offsetX'] . '</value>
                </field>
                <field index="settings.puzzleY">
                    <value index="vDEF">' . $row['tx_srpuzzle_offsetY'] . '</value>
                </field>
                <field index="settings.linkAfter">
                    <value index="vDEF">' . intval($row['tx_srpuzzle_link_after']) . '</value>
                </field>
                <field index="settings.congratulationsLink">
                    <value index="vDEF">' . intval($row['tx_srpuzzle_popup_link']) . '</value>
                </field>
            </language>
        </sheet>
        <sheet index="sTemplate">
            <language index="lDEF">
                <field index="view.templateRootPath">
                    <value index="vDEF">EXT:sr_puzzle/Resources/Private/Templates/</value>
                </field>
                <field index="view.partialRootPath">
                    <value index="vDEF">EXT:sr_puzzle/Resources/Private/Partials/</value>
                </field>
                <field index="view.layoutRootPath">
                    <value index="vDEF">EXT:sr_puzzle/Resources/Private/Layouts/</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>'
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid=' . intval($row['uid']), $update);
		}

		$message = LocalizationUtility::translate('update.elementsUpdated', 'SrPuzzle', array(count($pluginInstances)));
		return '<p>' . $message . '</p>';
	}

	/**
	 * Updates the TypoScript templates replacing tx_srpuzzle_pi1 by tx_srpuzzle
	 *
	 * @return string  HTML
	 */
	protected function updateTsTemplates() {

		$tsTemplates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'sys_template',
			'1=1'
		);
		$count = 0;			
		foreach ($tsTemplates as $row) {
			if (strstr($row['constants'], 'tx_srpuzzle_pi1') !== FALSE || strstr($row['config'], 'tx_srpuzzle_pi1') !== FALSE) {
				$update = array();
				$update['constants'] = str_replace('tx_srpuzzle_pi1', 'tx_srpuzzle', $row['constants']);
				$update['config'] = str_replace('$plugin.tx_srpuzzle_pi1', '$plugin.tx_srpuzzle', $row['config']);
				$update['config'] = str_replace('plugin.tx_srpuzzle_pi1', 'plugin.tx_srpuzzle.settings', $update['config']);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_template', 'uid=' . intval($row['uid']), $update);
				$count++;
			}
		}
		$message = LocalizationUtility::translate('update.templatesUpdated', 'SrPuzzle', array($count));
		return '<p>' . $message . '</p>';
	}

	public function access() {
		return TRUE;
	}
}
?>
