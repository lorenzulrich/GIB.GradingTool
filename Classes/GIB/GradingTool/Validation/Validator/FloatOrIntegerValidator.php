<?php
namespace GIB \GradingTool\Validation\Validator;

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
 * Validator for floats.
 *
 * @api
 * @Flow\Scope("singleton")
 */
class FloatOrIntegerValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator {

	/**
	 * The given value is valid if it is of type float or a string matching the regular expression [0-9.e+-]
	 *
	 * @param mixed $value The value that should be validated
	 * @return void
	 * @api
	 */
	protected function isValid($value) {
		if (is_float($value) || is_integer($value) || is_numeric($value)) {
			return;
		}
		if (!is_string($value) || strpos($value, '.') === FALSE || preg_match('/^[0-9.e+-]+$/', $value) !== 1) {
			$this->addError('A valid float number or an integer is expected.', 1221560289);
		}
	}
}
