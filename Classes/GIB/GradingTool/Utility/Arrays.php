<?php
namespace GIB\GradingTool\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * The array functions from the good old t3lib_div plus new code.
 *
 * @Flow\Scope("singleton")
 */
class Arrays {

	/**
	 * Recursively removes empty array elements.
	 *
	 * @param array $array
	 * @return array the modified array
	 */
	static public function removeEmptyElementsRecursively(array $array) {
		$result = $array;
		foreach ($result as $key => $value) {
			if (is_array($value)) {
				$result[$key] = self::removeEmptyElementsRecursively($value);
				if ($result[$key] === array()) {
					unset($result[$key]);
				}
			} elseif ($value === NULL || empty($value)) {
				unset($result[$key]);
			}
		}
		return $result;
	}

}
