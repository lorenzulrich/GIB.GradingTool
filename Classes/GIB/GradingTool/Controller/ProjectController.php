<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class ProjectController extends AbstractBaseController {

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectRepository
	 * @Flow\Inject
	 */
	protected $projectRepository;

	/**
	 * @var \GIB\GradingTool\Service\NotificationMailService
	 * @Flow\Inject
	 */
	protected $notificationMailService;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @var \GIB\GradingTool\Service\TemplateService
	 * @Flow\Inject
	 */
	protected $templateService;

	/**
	 * @var \GIB\GradingTool\Service\SubmissionService
	 * @Flow\Inject
	 */
	protected $submissionService;

	/**
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * Add a new data sheet
	 *
	 * The create action is missing because the project is added in the
	 * DataSheetFinisher (see Form/Finishers)
	 *
	 */
	public function newDataSheetAction() {
		/** @var \TYPO3\Form\Factory\ArrayFormFactory $factory */
		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($this->settings['forms']['dataSheet']);
		$overrideConfiguration = $this->formPersistenceManager->load($formName);

		$formDefinition = $factory->build($overrideConfiguration);

		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
		$renderedForm = $form->render();

		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
		));

	}

	/**
	 * Edit a project data sheet
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function editDataSheetAction(\GIB\GradingTool\Domain\Model\Project $project) {

		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($this->settings['forms']['dataSheet']);
		$overrideConfiguration = $this->formPersistenceManager->load($formName);
		$formDefinition = $factory->build($overrideConfiguration);
		foreach ($project->getDataSheetContentArray() as $dataSheetField => $dataSheetContent) {
			$formDefinition->addElementDefaultValue($dataSheetField, $dataSheetContent);
		}

		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
		$renderedForm = $form->render();

		// uri for autosave
		$ajaxUri = $this->controllerContext->getUriBuilder()->setCreateAbsoluteUri(TRUE)->uriFor('saveProjectDataValue', array('project' => $project, 'form' => 'dataSheet'), 'Project');

		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
			'project' => $project,
			'ajaxUri' => $ajaxUri,
		));

	}

	/**
	 * Edit/create a submission
	 *
	 * The create action is missing because the project is added in the
	 * SubmissionFinisher (see Form/Finishers)
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function submissionAction(\GIB\GradingTool\Domain\Model\Project $project) {

		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($this->settings['forms']['submission'], $project->getLanguage());
		$overrideConfiguration = $this->formPersistenceManager->load($formName);
		$formDefinition = $factory->build($overrideConfiguration);

		// populate form with existing data
		$submissionContent = $project->getSubmissionContent();
		if (!empty($submissionContent)) {
			$submissionContentArray = unserialize($submissionContent);
			foreach ($submissionContentArray as $submissionField => $submissionContent) {
				$formDefinition->addElementDefaultValue($submissionField, $submissionContent);
			}
		}

		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
		$renderedForm = $form->render();

		// uri for autosave
		$ajaxUri = $this->controllerContext->getUriBuilder()->setCreateAbsoluteUri(TRUE)->uriFor('saveProjectDataValue', array('project' => $project, 'form' => 'submission'), 'Project');

		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
			'project' => $project,
			'ajaxUri' => $ajaxUri,
		));

	}

	/**
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @param string $form
	 * @param string $formData
	 * @return string
	 */
	public function saveProjectDataValueAction(\GIB\GradingTool\Domain\Model\Project $project, $form, $formData) {
		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		if (!in_array($form, array('submission', 'dataSheet'))) {
			// security: only allow data changes to submission and dataSheet form
			return FALSE;
		}

		$decodedFormData = urldecode($formData);
		preg_match("/\[(.*?)\]/", $decodedFormData, $matches);
		$fieldIdentifier = $matches[1];

		$fieldValue = explode('=', $decodedFormData)[1];

		$contentGetter = 'get' . ucfirst($form) . 'Content';
		$contentSetter = 'set' . ucfirst($form) . 'Content';
		$contentArray = unserialize($project->$contentGetter());
		$contentArray[$fieldIdentifier] = $fieldValue;
		$project->$contentSetter(serialize($contentArray));
		$this->projectRepository->update($project);
		$this->persistenceManager->persistAll();

		return 'Saved.';

	}

	/**
	 * Review a submission
	 *
	 * The create action is missing because the project is added in the
	 * SubmissionFinisher (see Form/Finishers)
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function reviewSubmissionAction(\GIB\GradingTool\Domain\Model\Project $project) {

		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		$submission = $this->submissionService->getProcessedSubmission($project);

		$this->view->assignMultiple(array(
			'submission' => $submission,
			'project' => $project,
			'scoreData' => $this->submissionService->getScoreData(),
		));

	}

	/**
	 * Change the state of a field in a submission
	 * This is used for accepting opt-out question comments.
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @param string $fieldIdentifier
	 * @param string $newState
	 */
	public function changeFieldStateForProjectSubmissionAction(\GIB\GradingTool\Domain\Model\Project $project, $fieldIdentifier, $newState) {

		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		$submissionContentArray = unserialize($project->getSubmissionContent());
		$submissionContentArray[$fieldIdentifier] = $newState;
		$project->setSubmissionContent(serialize($submissionContentArray));
		$this->projectRepository->update($project);
		$this->persistenceManager->persistAll();

		$this->redirect('reviewSubmission', NULL, NULL, array('project' => $project));
	}

	/**
	 * Remove a project
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function removeAction(\GIB\GradingTool\Domain\Model\Project $project) {
		$this->projectRepository->remove($project);
		$this->persistenceManager->persistAll();

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The project "%s" was successfully removed.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($project->getProjectTitle()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('index', 'Admin');
	}

	/**
	 * Activate the submission form for a project
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function activateSubmissionFormAction(\GIB\GradingTool\Domain\Model\Project $project) {
		$project->setSubmissionFormAccess(TRUE);
		$this->projectRepository->update($project);
		$this->persistenceManager->persistAll();

		// notify user that he was accepted for submission
		$templateIdentifierOverlay = $this->templateService->getTemplateIdentifierOverlay('submissionActivatedNotification', $project);
		$this->notificationMailService->sendNotificationMail($templateIdentifierOverlay, $project, $project->getProjectManager(), $project->getProjectManager()->getName(), $project->getProjectManager()->getPrimaryElectronicAddress());

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The submission form for the project "%s" is now active and the project manager was informed.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($project->getProjectTitle()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('index', 'Admin');
	}

	/**
	 * Deactivate the submission form for a project
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function deactivateSubmissionFormAction(\GIB\GradingTool\Domain\Model\Project $project) {
		$project->setSubmissionFormAccess(FALSE);
		$this->projectRepository->update($project);
		$this->persistenceManager->persistAll();

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The submission form for the project "%s" is now inactive.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($project->getProjectTitle()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('index', 'Admin');
	}

	/**
	 * Check if an administrator is logged in or the owner of a project and deny access if someone else is trying to access
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function checkOwnerOrAdministratorAndDenyIfNeeded(\GIB\GradingTool\Domain\Model\Project $project) {

		// check if the user has access to this project
		if ($this->securityContext->getParty() !== $project->getProjectManager() &&
			!array_key_exists('GIB.GradingTool:Administrator', $this->securityContext->getRoles())) {
			// add a flash message
			$message = new \TYPO3\Flow\Error\Message('Access denied.', \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->flashMessageContainer->addMessage($message);
			$this->redirect('index', 'Standard');
		}
	}

}

?>