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

/**
 * Class SubmissionFinisher
 */
class SubmissionFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher {

	/**
	 * @var array
	 */
	protected $defaultOptions = array();

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectRepository
	 * @Flow\Inject
	 */
	protected $projectRepository;

	/**
	 * @var \GIB\GradingTool\Service\SubmissionService
	 * @Flow\Inject
	 */
	protected $submissionService;

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * The flash messages. Use $this->flashMessageContainer->addMessage(...) to add a new Flash
	 * Message.
	 *
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Mvc\FlashMessageContainer
	 */
	protected $flashMessageContainer;

	/**
	 * @var \GIB\GradingTool\Service\NotificationMailService
	 * @Flow\Inject
	 */
	protected $notificationMailService;

	/**
	 * @var \GIB\GradingTool\Service\TemplateService
	 * @Flow\Inject
	 */
	protected $templateService;

	/**
	 * Executes this finisher
	 * @see AbstractFinisher::execute()
	 *
	 * @return void
	 * @throws \TYPO3\Flow\Mvc\Exception\StopActionException();
	 */
	protected function executeInternal() {

		/** @var \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime */
		$formRuntime = $this->finisherContext->getFormRuntime();

		// The corresponding project
		$projectIdentifier = $formRuntime->getRequest()->getParentRequest()->getArgument('project');
		/** @var \GIB\GradingTool\Domain\Model\Project $project */
		$project = $this->projectRepository->findByIdentifier($projectIdentifier);

		$sendGradingToProjectManager = FALSE;
		if (is_null($project->getSubmissionLastUpdated())) {
			$sendGradingToProjectManager = TRUE;
		}

		// update the project with the data from the form
		$formValueArray = $formRuntime->getFormState()->getFormValues();
		$project->setSubmissionContent(serialize($formValueArray));
		$project->setSubmissionLastUpdated(new \TYPO3\Flow\Utility\Now);
		$this->projectRepository->update($project);

		$this->persistenceManager->persistAll();

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('Thank you for submitting the data for your project "%s".', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($project->getProjectTitle()));
		$this->flashMessageContainer->addMessage($message);

		// send notification mail
		$templateIdentifierOverlay = $this->templateService->getTemplateIdentifierOverlay('newSubmissionNotification', $project);
		$this->notificationMailService->sendNotificationMail($templateIdentifierOverlay, $project, $project->getProjectManager());

		if ($sendGradingToProjectManager) {
			// The grading was completed for the first time, so we send the grading to the project manager
			$this->submissionService->sendGradingToProjectManager($project);
		}

		// redirect to dashboard
		$formRuntime = $this->finisherContext->getFormRuntime();
		$request = $formRuntime->getRequest()->getMainRequest();

		$uriBuilder = new \TYPO3\Flow\Mvc\Routing\UriBuilder();
		$uriBuilder->setRequest($request);
		$uriBuilder->reset();
		$uri = $uriBuilder->uriFor('index', NULL, 'Standard');

		$response = $formRuntime->getResponse();
		$mainResponse = $response;
		while ($response = $response->getParentResponse()) {
			$mainResponse = $response;
		};
		$mainResponse->setStatus(303);
		$mainResponse->setHeader('Location', (string)$uri);
		throw new \TYPO3\Flow\Mvc\Exception\StopActionException();

	}

}
