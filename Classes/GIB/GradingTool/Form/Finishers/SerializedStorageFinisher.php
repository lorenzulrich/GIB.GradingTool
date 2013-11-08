<?php
namespace GIB\GradingTool\Form\Finishers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * This finisher sends an email to one recipient
 *
 */

use TYPO3\Flow\Annotations as Flow;

class SerializedStorageFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher {

	/**
	 * @var array
	 */
	protected $defaultOptions = array(

	);

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectRepository
	 * @Flow\Inject
	 */
	protected $projectRepository;

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * Executes this finisher
	 * @see AbstractFinisher::execute()
	 *
	 * @return void
	 * @throws \TYPO3\Form\Exception\FinisherException
	 */
	protected function executeInternal() {
		$formRuntime = $this->finisherContext->getFormRuntime();

		$formValueArray = $formRuntime->getFormState()->getFormValues();

		$targetDomainModel = $this->parseOption('domainModel');

		$targetContentProperty = $this->parseOption('formContentProperty');
		$targetContentSetter = 'set' . ucfirst($targetContentProperty);

		$sourceLabelField = $this->parseOption('labelFormFieldIdentifier');

		$targetLabelProperty = $this->parseOption('labelDatabaseProperty');
		$targetLabelSetter = 'set' . ucfirst($targetLabelProperty);

		$project = new $targetDomainModel();
		$project->$targetLabelSetter($formValueArray[$sourceLabelField]);
		$project->$targetContentSetter(serialize($formValueArray));


		//\typo3\flow\var_dump($project->getProjectTitle());

		$this->projectRepository->add($project);
		$this->persistenceManager->persistAll();

		die();

	}

}
