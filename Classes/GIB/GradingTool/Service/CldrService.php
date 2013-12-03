<?php
namespace GIB\GradingTool\Service;
/**
 * Created by PhpStorm.
 * User: lorenz
 * Date: 19.11.13
 * Time: 12:46
 */
use TYPO3\Flow\Annotations as Flow;

class CldrService {

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

	public function getCountryNameForIsoCode($countryCode) {

		// get the localized country names from the CLDR data
		$i18nConfiguration = $this->i18nService->getConfiguration();

		$cldrModel = $this->cldrRepository->getModel('main/' . $i18nConfiguration->getCurrentLocale()->getLanguage());
		$countries = $cldrModel->findNodesWithinPath('localeDisplayNames/territories', 'territory');

		if (array_key_exists('territory[@type="' . strtoupper($countryCode) . '"]', $countries)) {
			return $countries['territory[@type="' . strtoupper($countryCode) . '"]'];
		} else {
			return $countryCode;
		}

	}

}