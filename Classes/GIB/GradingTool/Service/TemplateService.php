<?php
namespace GIB\GradingTool\Service;


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\SwiftMailer\Message;

class TemplateService {

	/**
	 * @var \GIB\GradingTool\Domain\Repository\TemplateRepository
	 * @Flow\Inject
	 */
	protected $templateRepository;

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
	 * Returns the templateIdentifier of a template in the laguage of a project if available,
	 * fall back to default language if not available
	 *
	 * @param string $templateIdentifier
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @return string
	 */
	public function getTemplateIdentifierOverlay($templateIdentifier, \GIB\GradingTool\Domain\Model\Project $project) {
		$availableLanguages = $this->settings['languages'];
		$defaultLangage = $this->settings['defaultLanguage'];

		$projectLanguage = $project->getLanguage();

		if ($projectLanguage === $defaultLangage) {
			// the language for the default template has no suffix, therefore return the unchanged templateIdentifier
			return $templateIdentifier;
		}

		if (array_key_exists($projectLanguage, $availableLanguages)) {
			// language is available, so we try to overlay
			if (count($this->templateRepository->findByTemplateIdentifier($templateIdentifier . ucfirst($projectLanguage)))) {
				// localized template was found, so we use it
				return $templateIdentifier . ucfirst($projectLanguage);
			} else {
				// localized template was not found, we fall back to default language
				return $templateIdentifier;
			}
		}

	}


}