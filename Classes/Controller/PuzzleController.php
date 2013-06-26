<?php
namespace SJBR\SrPuzzle\Controller;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Stanislas Rolland <typo3(arobas)sjbr.ca>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Controls the rendering of the puzzle as a normal content element
 */
class PuzzleController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	
	/**
	 * @var string Name of the extension this controller belongs to
	 */
	protected $extensionName = 'SrPuzzle';

	/**
	 * @var string Name of the extension this controller belongs to
	 */
	protected $extensionKey = 'sr_puzzle';

	/**
	 * @var \TYPO3\CMS\Core\Resource\FileRepository
	 */
	protected $fileRepository;

 	/**
	 * Dependency injection of the Image Processor
 	 *
	 * @param \TYPO3\CMS\Core\Resource\FileRepository $fileRepository
 	 * @return void
	 */
	public function injectFileRepository(\TYPO3\CMS\Core\Resource\FileRepository $fileRepository) {
		$this->fileRepository = $fileRepository;
	}

	/**
	 * Show the puzzle
	 *
	 * @return string empty string
	 */
	public function showAction() {
		$puzzle = array();

		// Adjust settings
		$this->processSettings();
		
		// Initialize image processor
		$imageProcessor = $this->objectManager->get('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions');
		$imageProcessor->init();
		$imageProcessor->mayScaleUp = 0;
		
		// Add JavaScript file
		$this->response->addAdditionalHeaderData($this->wrapJavaScriptFile(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extensionKey) . 'Resources/Public/JavaScript/SrPuzzle.js'));
		
		// Get the puzzle image
		$contentObject = $this->configurationManager->getContentObject();
		$image = $this->fileRepository->findByRelation('tt_content', 'image', $contentObject->data['uid']);
		// We care only for the first image
		$image = $image[0];
		if ($image instanceof \TYPO3\CMS\Core\Resource\FileReference) {
			// Set puzzle image properties
			$puzzle['width'] = intval($this->settings['puzzleWidth']) ? intval($this->settings['puzzleWidth']) : $image->getProperty('width');
			$puzzle['height'] = intval($this->settings['puzzleHeight']) ? intval($this->settings['puzzleHeight']) : $image->getProperty('height');
			$puzzle['solutionImageTitle'] = $image->getTitle();
			$puzzle['solutionImageAlternateText'] = $image->getAlternative();
			$scaledImage = $imageProcessor->imageMagickConvert($image->getPublicUrl(), 'web', $puzzle['width'] . 'm', $puzzle['height'] . 'm', '', '', '', 1);
			$puzzle['imageExtension'] = $scaledImage[2];

			// Set number of puzzle pieces
			$puzzle['nbPiecesHorizontal'] = intval($scaledImage[0]/$this->settings['pieceSize']);
			$puzzle['nbPiecesVertical'] = intval($scaledImage[1]/$this->settings['pieceSize']);

			// Adjust puzzle dimensions and set puzzle image
			$puzzle['width'] = $this->settings['pieceSize'] * $puzzle['nbPiecesHorizontal'];
			$puzzle['height'] = $this->settings['pieceSize'] * $puzzle['nbPiecesVertical'];
			$puzzleCropParameters = ' -crop ' . $puzzle['width'] . 'x'  . $puzzle['height'] . '+0+0';
			$puzzle['imagePath'] = 'typo3temp/' . $this->extensionKey . '/puzzle_' . GeneralUtility::shortMD5('puzzle' . $scaledImage[3] . filemtime($scaledImage[3]) . $this->settings['pieceSize']);
			if (!is_dir(PATH_site.'typo3temp/'.$this->extensionKey)) {
				GeneralUtility::mkdir(PATH_site . 'typo3temp/' . $this->extensionKey);
			}
			if (!$imageProcessor->file_exists_typo3temp_file($puzzle['imagePath'] . '_puzzle.' . $puzzle['imageExtension'], $scaledImage[3])) {
				$imageProcessor->imageMagickExec($scaledImage[3], $puzzle['imagePath'] . '_puzzle.' . $puzzle['imageExtension'], $puzzleCropParameters);
			}
			$imageInfo = $imageProcessor->getImageDimensions($puzzle['imagePath'] . '_puzzle.' . $puzzle['imageExtension']);
			$puzzle['image'] = $imageInfo[3];
			
			// Height of the wrapping div
			$puzzle['totalHeight'] = $puzzle['height'] + 120;

			// Create the puzzle pieces!!
			$pieceIndex = 1;
			$geometryVertical = 0;
			$pieceCropParameters = ' -crop ' . $this->settings['pieceSize'] . 'x' . $this->settings['pieceSize'];
			for ($j = 0; $j < $puzzle['nbPiecesVertical']; $j++) {
				$geometryHorizontal = 0;
				for ($i = 0; $i < $puzzle['nbPiecesHorizontal']; $i++) {
					$params = $pieceCropParameters . '+' . trim($geometryHorizontal) . '+' . trim($geometryVertical);
					if (!$imageProcessor->file_exists_typo3temp_file($puzzle['imagePath'] . $pieceIndex . '.' . $puzzle['imageExtension'], $puzzle['image'])) {
						$imageProcessor->imageMagickExec($puzzle['image'], $puzzle['imagePath'] . $pieceIndex . '.' . $puzzle['imageExtension'], $params);
					}
					$geometryHorizontal += $this->settings['pieceSize'];
					$pieceIndex++;
				}
				$geometryVertical += $this->settings['pieceSize'];
			}
			$puzzle['firstPiece'] = $puzzle['imagePath'] . '1.' . $puzzle['imageExtension'];
		} else {
			$puzzle['imageNotFound'] = TRUE;
		}

		// Render the puzzle
		$this->view->assign('settings', $this->settings);
		$this->view->assign('puzzle', $puzzle);
	}

	/**
	 * Reviews and adjusts plugin settings
	 *
	 * @return void
	 * @api
	 */
	protected function processSettings() {

		// Set dimensions of puzzle pieces
		$this->settings['pieceSize'] = intval($this->settings['pieceSize']) ? intval($this->settings['pieceSize']) : 60;
		
		// Set puzzle horizontal and vertical offsets
		$this->settings['puzzleX'] = intval($this->settings['puzzleX']) ? intval($this->settings['puzzleX']) : 250;
		$this->settings['puzzleY'] = intval($this->settings['puzzleY']) ? intval($this->settings['puzzleY']) : 100;
		
		// Set solution image dimensions
		if (intval($this->settings['solutionWidth'])) {
			$this->settings['solutionWidth'] = intval($this->settings['solutionWidth']);
		}
		if (intval($this->settings['solutionHeight'])) {
			$this->settings['solutionHeight'] = intval($this->settings['solutionHeight']);
		}
		if (!($this->settings['solutionWidth'] || $this->settings['solutionHeight'])) {
			$this->settings['solutionWidth'] = 100;
		}

		// Set enabled links options
		$this->settings['enablePopUp'] = isset($this->settings['enablePopUp']) ? intval($this->settings['enablePopUp']) : 1;
		$this->settings['congratulationsLink'] = ($this->settings['enablePopUp'] && intval($this->settings['congratulationsLink'])) ? intval($this->settings['congratulationsLink']) : 0;
		$this->settings['enableAlert'] = isset($this->settings['enableAlert']) ? intval($this->settings['enableAlert']) : 0;
		if (!intval($this->settings['congratulationsLink']) || $this->settings['enableAlert']) {
			$this->settings['enablePopUp'] = 0;
		}
		$this->settings['enableLinkAfter'] = (isset($this->settings['enableLinkAfter']) && isset($this->settings['linkAfter'])) ? intval($this->settings['enableLinkAfter']) : 0;
		$this->settings['linkAfter'] = ($this->settings['enableLinkAfter'] && intval($this->settings['linkAfter'])) ? intval($this->settings['linkAfter']) : 0;

		// Set congratulations popup window dimensions and position
		$this->settings['congratulationsWidth'] = intval($this->settings['congratulationsWidth']) ? intval($this->settings['congratulationsWidth']) : 150;
		$this->settings['congratulationsHeight'] = intval($this->settings['congratulationsHeight']) ? intval($this->settings['congratulationsHeight']) : 150;
		$this->settings['congratulationsVertOffset'] = intval($this->settings['congratulationsVertOffset']) ? intval($this->settings['congratulationsVertOffset']) : 250;
		$this->settings['congratulationsHorOffset'] = intval($this->settings['congratulationsHorOffset']) ? intval($this->settings['congratulationsHorOffset']) : 250;
	}

	/**
	 * Wrap JavaScript file inside <link> tag
	 *
	 * @param string $javaScriptFile Path to file
	 * @return string <link.. string ready for <head> part
	 */
	public function wrapJavaScriptFile($javaScriptFile) {
		$javaScriptFile = GeneralUtility::resolveBackPath($javaScriptFile);
		$javaScriptFile = GeneralUtility::createVersionNumberedFilename($javaScriptFile);
		return '<script src="' . $javaScriptFile . '" type="text/javascript"></script>';
	}
}
class_alias('SJBR\SrPuzzle\Controller\PuzzleController', 'Tx_SrPuzzle_Controller_PuzzleController');
?>