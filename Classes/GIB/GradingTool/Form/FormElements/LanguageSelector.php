<?php
namespace GIB\GradingTool\Form\FormElements;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A generic form element
 */
class LanguageSelector extends \TYPO3\Form\Core\Model\AbstractFormElement {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Override this method in your custom FormElements if needed
	 *
	 * @return void
	 */
	public function initializeFormElement() {

		$options = array();
		foreach ($this->settings['languages'] as $isoCode => $language) {
			$options[$isoCode] = $language;
		}

		$this->setProperty('options', $options);

	}


}
