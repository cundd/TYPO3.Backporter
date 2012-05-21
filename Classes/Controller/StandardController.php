<?php
declare(ENCODING = 'utf-8');
namespace TYPO3\Backporter\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Backporter".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Packporter Default Controller
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class StandardController extends \TYPO3\FLOW3\Mvc\Controller\ActionController {

	/*
	 * @param string $sourcePath
	 * @param string $targetPath
	 * @param string $extensionKey
	 */
	public function indexAction($sourcePath, $targetPath, $extensionKey) {
		$backporter = $this->objectManager->getObject('TYPO3\Backporter\Backporter');
		$backporter->setExtensionKey($extensionKey);
		$backporter->processFiles($sourcePath, $targetPath);
		return 'files backported.';
	}
}
?>