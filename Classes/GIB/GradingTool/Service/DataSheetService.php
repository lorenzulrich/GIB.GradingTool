<?php
namespace GIB\GradingTool\Service;

use TYPO3\Flow\Annotations as Flow;

class DataSheetService {

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
	 * @Flow\Inject
	 * @var \TYPO3\Form\Persistence\FormPersistenceManagerInterface
	 */
	protected $formPersistenceManager;

	/**
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @param bool $languageOverlay
	 * @return array
	 */
	public function getProcessedDataSheet(\GIB\GradingTool\Domain\Model\Project $project, $languageOverlay = FALSE) {

		/** @var \TYPO3\Form\Factory\ArrayFormFactory $factory */
		$factory = new \TYPO3\Form\Factory\ArrayFormFactory;
		// todo overlay if needed
		$overrideConfiguration = $this->formPersistenceManager->load($this->settings['forms']['dataSheet']);
		/** @var \TYPO3\Form\Core\Model\FormDefinition $formDefinition */
		$formDefinition = $factory->build($overrideConfiguration);

		$flatDataSheetArray = array();

		foreach ($project->getDataSheetContentArray() as $key => $value) {
			$flatDataSheetArray[$key]['label'] = $formDefinition->getElementByIdentifier($key)->getLabel();
			$flatDataSheetArray[$key]['value'] = $value;
		}

		return $flatDataSheetArray;

	}

}