<?php
namespace GIB\GradingTool\Service;

use TYPO3\Flow\Annotations as Flow;

class ProjectDataService {

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

	protected $fieldTypesToBeIgnored = array(
		'TYPO3.Form:StaticText',
		'GIB.GradingTool:DataSheetConditionalUserName',
		'GIB.GradingTool:DataSheetConditionalPassword',
		'GIB.GradingTool:DataSheetConditionalPasswordWithConfirmation'
	);

	/**
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @return array
	 */
	public function getProcessedProjectData(\GIB\GradingTool\Domain\Model\Project $project) {
		/** @var \TYPO3\Form\Core\Model\FormDefinition $formDefinition */
		$formDefinition = $this->formPersistenceManager->load($this->settings['forms']['projectData']);
		$fieldArray = $this->buildFieldArray($formDefinition['renderables'], $project->getProjectDataArray());
		return $fieldArray;
	}

	/**
	 * Recursively builds an array containing type/label/value of all fields in a form
	 *
	 * @param array $renderables
	 * @param array $data
	 * @return array
	 */
	public function buildFieldArray($renderables, $data) {

		$renderablesArray = '';

		foreach ($renderables as $renderable) {
			if (is_array($renderable) && array_key_exists('identifier', $renderable)) {
				if (in_array($renderable['type'], $this->fieldTypesToBeIgnored)) {
					// ignore static text fields
					continue;
				}
				$renderablesArray[$renderable['identifier']] = $this->buildFieldArray($renderable, $data);
				$renderablesArray[$renderable['identifier']]['identifier'] = $renderable['identifier'];
				$renderablesArray[$renderable['identifier']]['label'] = $renderable['label'];
				$renderablesArray[$renderable['identifier']]['type'] = $renderable['type'];
			} elseif (is_array($renderable)) {
				foreach ($renderable as $subRenderable) {
					if (is_array($subRenderable) && array_key_exists('identifier', $subRenderable) && array_key_exists('type', $subRenderable)) {
						if (in_array($subRenderable['type'], $this->fieldTypesToBeIgnored)) {
							// ignore static text fields
							continue;
						} elseif ($subRenderable['type'] === 'TYPO3.Form:Section') {
							// we have another layer of subs
							$renderablesArray['items'][$subRenderable['identifier']] = $this->buildFieldArray($subRenderable, $data);
							$renderablesArray['items'][$subRenderable['identifier']]['identifier'] = $subRenderable['identifier'];
							$renderablesArray['items'][$subRenderable['identifier']]['label'] = $subRenderable['label'];
							$renderablesArray['items'][$subRenderable['identifier']]['type'] = $subRenderable['type'];
						} else {
							// last layer
							$renderablesArray['items'][$subRenderable['identifier']]['label'] = $subRenderable['label'];
							$renderablesArray['items'][$subRenderable['identifier']]['identifier'] = $subRenderable['identifier'];
							$renderablesArray['items'][$subRenderable['identifier']]['type'] = $subRenderable['type'];
							if (is_array($data) && array_key_exists($subRenderable['identifier'], $data)) {
								$renderablesArray['items'][$subRenderable['identifier']]['value'] = $data[$subRenderable['identifier']];
							}
						}
					}
				}
			}

		}
		return $renderablesArray;
	}

}