<?php
namespace GIB\GradingTool\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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

class TcPdfParamsViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * serializes and then urlencodes an array
	 *
	 * @param array $params The array or \Countable to be counted
	 * @return string the urlencoded, serialized representation of $params
	 * @throws \TYPO3\Fluid\Core\ViewHelper\Exception
	 * @api
	 */
	public function render($params = NULL) {
		if ($params === NULL) {
			$params = $this->renderChildren();
		}
		if (is_object($params) && !$params instanceof \Countable) {
			throw new \TYPO3\Fluid\Core\ViewHelper\Exception('TcPdfParamsViewHelper only supports arrays and objects implementing \Countable interface. Given: "' . get_class($params) . '"', 1279808078);
		}
		return urlencode(json_encode($params));
	}
	
}


?>
