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

class ProjectSerializedStorageFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher {

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

		/** @var \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime */
		$formRuntime = $this->finisherContext->getFormRuntime();

		$formValueArray = $formRuntime->getFormState()->getFormValues();
		$sourceLabelField = $this->parseOption('labelFormFieldIdentifier');


		if ($formRuntime->getRequest()->getParentRequest()->getControllerActionName() == 'editDataSheet') {
			// we need to update the form
			/** @var \GIB\GradingTool\Domain\Model\Project $project */
			$project = $this->projectRepository->findByIdentifier($formRuntime->getRequest()->getParentRequest()->getArgument('project')['__identity']);
			$project->setProjectTitle($formValueArray[$sourceLabelField]);
			$project->setDataSheetContent(serialize($formValueArray));
			$this->projectRepository->update($project);

		} else {
			// we need to add a new form
			/** @var \GIB\GradingTool\Domain\Model\Project $project */
			$project = new \GIB\GradingTool\Domain\Model\Project();
			$project->setProjectTitle($formValueArray[$sourceLabelField]);
			$project->setDataSheetContent(serialize($formValueArray));
			$this->projectRepository->add($project);
		}

		$this->persistenceManager->persistAll();

	}

}
