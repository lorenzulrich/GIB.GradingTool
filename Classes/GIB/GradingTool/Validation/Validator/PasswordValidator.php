<?php
namespace GIB\GradingTool\Validation\Validator;

use TYPO3\Flow\Annotations as Flow;

/**
 * Password validator for the Layh.Events package
 * Checks the password if it has a minimum length and compares password-1
 * with password-2
 *
 */
class PasswordValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator {

	/**
	 * @var array
	 */
	protected $supportedOptions = array(
		'minimumLength' => array(0, 'Minimum length for a valid string', 'integer'),
	);

	/**
	 * Check if $value is valid. If it is not valid, needs to add an error
	 * to Result.
	 *
	 * @param array $value
	 * @return void
	 */
	protected function isValid($value) {

		// get the option for the validation
		$minimumLength = $this->options['minimumLength'];

		// check for empty password
		if ($value === '') {
			$this->addError('The password is to weak.', 1221560718);
		}

		// check for password length
		if(strlen($value) < $minimumLength) {
			$this->addError('The password is too short, minimum length is ' . $minimumLength . '.', 1221560718);
		}

	}

}