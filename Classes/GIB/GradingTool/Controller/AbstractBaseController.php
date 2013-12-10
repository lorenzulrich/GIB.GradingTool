<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

abstract class AbstractBaseController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\Flow\I18n\Service
	 * @Flow\Inject
	 */
	protected $i18nService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Form\Persistence\FormPersistenceManagerInterface
	 */
	protected $formPersistenceManager;

	/**
	 * @Flow\Inject
	 * @var \GIB\GradingTool\Domain\Repository\TemplateRepository
	 */
	protected $templateRepository;

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 * @api
	 */
	protected function initializeAction() {
		$httpRequest = $this->request->getHttpRequest();
		if ($httpRequest->hasCookie('GIB_GradingTool_Language')) {
			$cookie = $httpRequest->getCookie('GIB_GradingTool_Language');
			$language = $cookie->getValue();
			if (array_key_exists($language, $this->settings['languages'])) {
				// requested language is valid, therefore we use it
				$locale = new \TYPO3\Flow\I18n\Locale($language);
			} else {
				// requested language is invalid, we fall back to default language
				$locale = new \TYPO3\Flow\I18n\Locale($this->settings['defaultLanguage']);
			}
		} else {
			$locale = new \TYPO3\Flow\I18n\Locale($this->settings['defaultLanguage']);
		}

		$this->i18nService->getConfiguration()->setCurrentLocale($locale);


	}

	protected function initializeView(\TYPO3\Flow\Mvc\View\ViewInterface $view) {
		// assign all languages to each view
		$this->view->assign('languages', $this->settings['languages']);

		$currentLocale = $this->i18nService->getConfiguration()->getCurrentLocale()->getLanguage();
		$currentLanguage = $this->settings['languages'][$currentLocale];

		$this->view->assign('currentLanguage', $currentLanguage);
	}

	/**
	 * Remove error FlashMessage
	 * @return \TYPO3\Flow\Error\Message|void
	 */
	public function getErrorFlashMessage() {
		return FALSE;
	}

	/**
	 * Check if a form has a localized version and deliver it if available
	 *
	 * @param $formName
	 * @param $localeOverride
	 * @return string
	 */
	public function getFormNameRespectingLocale($formName, $localeOverride = '') {
		// if we override the locale anyway, we return early
		if (!empty($localeOverride)) {
			return $formName . ucfirst($localeOverride);
		}

		$currentLanguage = $this->i18nService->getConfiguration()->getCurrentLocale()->getLanguage();

		/*
		 * a localized version has the language iso code as uppercased suffix, e.g. dataSheetFormFr
		 * english is the default language and has no suffix, therefore we return the unchanged name if
		 * no translation was found
		 */
		if ($this->formPersistenceManager->exists($formName . ucfirst($currentLanguage))) {
			$formName = $formName . ucfirst($currentLanguage);
		}

		return $formName;

	}

}