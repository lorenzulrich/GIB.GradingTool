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
class CountrySelector extends \TYPO3\Form\Core\Model\AbstractFormElement {


	/**
	 * @var \TYPO3\Flow\I18n\Service
	 * @Flow\Inject
	 */
	protected $i18nService;

	/**
	 * @var \TYPO3\Flow\I18n\Cldr\CldrRepository
	 * @Flow\Inject
	 */
	protected $cldrRepository;

	/**
	 * Override this method in your custom FormElements if needed
	 *
	 * @return void
	 */
	public function initializeFormElement() {

		// the not localized country codes and names
		$selectOptions = $this->getProperties()['options'];

		// get the localized country names from the CLDR data
		$i18nConfiguration = $this->i18nService->getConfiguration();
		$cldrModel = $this->cldrRepository->getModel('main/' . $i18nConfiguration->getCurrentLocale()->getLanguage());
		$countries = $cldrModel->findNodesWithinPath('localeDisplayNames/territories', 'territory');

		// if the country is found in the CLDR data, use the localized name
		$localizedSelectOptions = array();
		foreach ($selectOptions as $countryCode => $countryName) {
			if (array_key_exists('territory[@type="' . strtoupper($countryCode) . '"]', $countries)) {
				$localizedSelectOptions[$countryCode] = $countries['territory[@type="' . strtoupper($countryCode) . '"]'];
			} else {
				$localizedSelectOptions[$countryCode] = $countryName;
			}
		}

		$this->setProperty('options', $localizedSelectOptions);

	}


}
