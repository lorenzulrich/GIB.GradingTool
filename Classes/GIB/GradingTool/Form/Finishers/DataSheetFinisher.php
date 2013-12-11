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

use GIB\GradingTool\Utility\DiffUtility;
use GIB\GradingTool\Utility\StringDiffUtility;
use GIB\GradingTool\Utility\ArrayDiffUtility;
use TYPO3\Flow\Annotations as Flow;

class DataSheetFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher {

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
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 * @Flow\Inject
	 */
	protected $authenticationManager;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @var \TYPO3\Flow\Security\AccountFactory
	 * @Flow\Inject
	 */
	protected $accountFactory;

	/**
	 * @var \TYPO3\Flow\Security\AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @var \TYPO3\Party\Domain\Repository\PartyRepository
	 * @Flow\Inject
	 */
	protected $partyRepository;

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
	 * The flash messages. Use $this->flashMessageContainer->addMessage(...) to add a new Flash
	 * Message.
	 *
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Mvc\FlashMessageContainer
	 */
	protected $flashMessageContainer;

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
		$sourceLabelField = $this->parseOption('labelFormFieldIdentifier');


		if ($formRuntime->getRequest()->getParentRequest()->getControllerActionName() == 'editDataSheet') {
			// we need to update the data sheet, we assume that the person is authenticated because a data sheet can only be edited by a authenticated user

			/** @var \GIB\GradingTool\Domain\Model\Project $project */
			$project = $this->projectRepository->findByIdentifier($formRuntime->getRequest()->getParentRequest()->getArgument('project'));

			$currentDataSheetContent = unserialize($project->getDataSheetContent());

			// make a HTML representation of a diff of the old and new data
			$diffContent = DiffUtility::arrayDiffRecursive($currentDataSheetContent, $formValueArray);

			// store changes to project
			$project->setProjectTitle($formValueArray[$sourceLabelField]);
			$project->setLanguage($formValueArray['language']);
			// TODO $project->setRegion($formValueArray['language']);
			if (!empty($formValueArray['categories'])) {
				$project->setCategories(implode($formValueArray['categories']));
			}
			$project->setCost($formValueArray['cost']);
			$project->setCountryCode($formValueArray['country']);
			$project->setDataSheetContent(serialize($formValueArray));
			$project->setLastUpdated(new \TYPO3\Flow\Utility\Now);
			$this->projectRepository->update($project);

			// send a notification mail to the Administrator containing the changes
			$templateIdentifierOverlay = $this->templateService->getTemplateIdentifierOverlay('editDataSheetNotification', $project);
			$this->notificationMailService->sendNotificationMail($templateIdentifierOverlay, $project, NULL, '', '', $diffContent);

			// add a flash message
			$message = new \TYPO3\Flow\Error\Message('Your data sheet for project "%s" was successfully edited.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($project->getProjectTitle()));
			$this->flashMessageContainer->addMessage($message);

		} else {
			// we need to add a new data sheet

			/** @var \GIB\GradingTool\Domain\Model\Project $project */
			$project = new \GIB\GradingTool\Domain\Model\Project();
			$project->setProjectTitle($formValueArray[$sourceLabelField]);
			$project->setLanguage($formValueArray['language']);
			// TODO $project->setRegion($formValueArray['language']);
			if (!empty($formValueArray['categories'])) {
				$project->setCategories(implode($formValueArray['categories']));
			}
			$project->setCost($formValueArray['cost']);
			$project->setCountryCode($formValueArray['country']);

			// store identifier=userName and password for later usage
			$identifier = $formValueArray['userName'];
			$password = $formValueArray['password'];

			// remove userName and password from data array so it doesn't get saved unencrypted
			unset($formValueArray['userName']);
			unset($formValueArray['password']);

			// serialize all form content and set dataSheetContent
			$project->setDataSheetContent(serialize($formValueArray));
			$project->setCreated(new \TYPO3\Flow\Utility\Now);
			$this->projectRepository->add($project);

			// add a flash message
			$message = new \TYPO3\Flow\Error\Message('Your data sheet for project "%s" was successfully submitted.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($formValueArray[$sourceLabelField]));
			$this->flashMessageContainer->addMessage($message);

			if (!$this->authenticationManager->isAuthenticated() || ($this->authenticationManager->isAuthenticated() && $this->authenticationManager->getSecurityContext()->hasRole('GIB.GradingTool:Administrator'))) {
				// the product manager (supposedly) doesn't have an account yet, so we create one

				$projectManager = new \GIB\GradingTool\Domain\Model\ProjectManager();
				$projectManagerName = new \TYPO3\Party\Domain\Model\PersonName('', $formValueArray['projectManagerFirstName'], '', $formValueArray['projectManagerLastName']);
				$projectManager->setName($projectManagerName);
				$projectManagerElectronicAddress = new \TYPO3\Party\Domain\Model\ElectronicAddress();
				$projectManagerElectronicAddress->setIdentifier($formValueArray['projectManagerEmail']);
				$projectManagerElectronicAddress->setType(\TYPO3\Party\Domain\Model\ElectronicAddress::TYPE_EMAIL);
				$projectManager->addElectronicAddress($projectManagerElectronicAddress);
				$projectManager->setPrimaryElectronicAddress($projectManagerElectronicAddress);

				// add account
				$roles = array('GIB.GradingTool:ProjectManager');
				$authenticationProviderName = 'DefaultProvider';
				$account = $this->accountFactory->createAccountWithPassword($identifier, $password, $roles, $authenticationProviderName);
				$this->accountRepository->add($account);

				// add account to ProjectManager
				$projectManager->addAccount($account);
				// add project to ProjectManager
				$projectManager->addProject($project);

				// finally add the complete ProjectManager
				$this->partyRepository->add($projectManager);

				if (!$this->authenticationManager->getSecurityContext()->hasRole('GIB.GradingTool:Administrator')) {
					// authenticate user if no Administrator is authenticated
					$authenticationTokens = $this->securityContext->getAuthenticationTokensOfType('TYPO3\Flow\Security\Authentication\Token\UsernamePassword');
					if (count($authenticationTokens) === 1) {
						$authenticationTokens[0]->setAccount($account);
						$authenticationTokens[0]->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);
					}
					// add a flash message
					$message = new \TYPO3\Flow\Error\Message('The account "%s" was created and you were successfully logged in.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($identifier));
					$this->flashMessageContainer->addMessage($message);
				}

			} elseif ($this->authenticationManager->isAuthenticated() && $this->authenticationManager->getSecurityContext()->hasRole('GIB.GradingTool:ProjectManager')) {
				// a productManager is adding a new project to his account
				/** @var \GIB\GradingTool\Domain\Model\ProjectManager $projectManager */
				$projectManager = $this->authenticationManager->getSecurityContext()->getParty();
				$projectManager->addProject($project);
				$this->partyRepository->update($projectManager);
			}

			// send notification mail
			$templateIdentifierOverlay = $this->templateService->getTemplateIdentifierOverlay('newDataSheetNotification', $project);
			$this->notificationMailService->sendNotificationMail($templateIdentifierOverlay, $project, $projectManager, $formValueArray['projectManagerFirstName'] . ' ' . $formValueArray['projectManagerLastName'], $formValueArray['projectManagerEmail']);

		}

		$this->persistenceManager->persistAll();



		// redirect to dashboard
		$formRuntime = $this->finisherContext->getFormRuntime();
		$request = $formRuntime->getRequest()->getMainRequest();

		$uriBuilder = new \TYPO3\Flow\Mvc\Routing\UriBuilder();
		$uriBuilder->setRequest($request);
		$uriBuilder->reset();
		$uri = $uriBuilder->uriFor('index', NULL, 'Standard');
		$uri = $request->getHttpRequest()->getBaseUri() . $uri;

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
