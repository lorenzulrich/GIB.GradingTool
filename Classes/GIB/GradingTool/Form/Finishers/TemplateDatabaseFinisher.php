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

class TemplateDatabaseFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher {

	/**
	 * @var array
	 */
	protected $defaultOptions = array();

	/**
	 * @var \GIB\GradingTool\Domain\Repository\TemplateRepository
	 * @Flow\Inject
	 */
	protected $templateRepository;

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
	 * Executes this finisher
	 * @see AbstractFinisher::execute()
	 *
	 * @return void
	 * @throws \TYPO3\Flow\Mvc\Exception\StopActionException();
	 */
	protected function executeInternal() {

		/** @var \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime */
		$formRuntime = $this->finisherContext->getFormRuntime();

		$formValueArray = $formRuntime->getFormState()->getFormValues();

		if ($formRuntime->getRequest()->getParentRequest()->getControllerActionName() == 'edit') {
			// we need to update the template

			/** @var \GIB\GradingTool\Domain\Model\Template $template */
			$template = $this->templateRepository->findByIdentifier($formRuntime->getRequest()->getParentRequest()->getArgument('template')['__identity']);
			$template->setTemplateIdentifier($formValueArray['templateIdentifier']);
			$template->setContent(serialize($formValueArray));
			//$project->setLastUpdated(new \TYPO3\Flow\Utility\Now);
			$this->templateRepository->update($template);

			// add a flash message
			$message = new \TYPO3\Flow\Error\Message('The template "%s" was successfully edited.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($template->getTemplateIdentifier()));
			$this->flashMessageContainer->addMessage($message);

		} else {
			// we need to add a new template

			/** @var \GIB\GradingTool\Domain\Model\Template $template */
			$template = new \GIB\GradingTool\Domain\Model\Template();
			$template->setTemplateIdentifier($formValueArray['templateIdentifier']);

			// serialize all form content and store it
			$template->setContent(serialize($formValueArray));
			//$project->setCreated(new \TYPO3\Flow\Utility\Now);
			$this->templateRepository->add($template);

			// add a flash message
			$message = new \TYPO3\Flow\Error\Message('The template "%s" was successfully created.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($formValueArray['templateIdentifier']));
			$this->flashMessageContainer->addMessage($message);

		}

		$this->persistenceManager->persistAll();

		// redirect to dashboard
		$formRuntime = $this->finisherContext->getFormRuntime();
		$request = $formRuntime->getRequest()->getMainRequest();

		$uriBuilder = new \TYPO3\Flow\Mvc\Routing\UriBuilder();
		$uriBuilder->setRequest($request);
		$uriBuilder->reset();
		$uri = $uriBuilder->uriFor('list', NULL, 'Template');

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
