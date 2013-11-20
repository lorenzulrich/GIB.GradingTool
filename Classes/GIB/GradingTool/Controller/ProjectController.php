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

		$dataSheetContentArray = unserialize($project->getDataSheetContent());

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($this->settings['forms']['dataSheet']);
		$overrideConfiguration = $this->formPersistenceManager->load($formName);
		$formDefinition = $factory->build($overrideConfiguration);

		foreach ($dataSheetContentArray as $dataSheetField => $dataSheetContent) {
			$formDefinition->addElementDefaultValue($dataSheetField, $dataSheetContent);
		}

		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);

		$renderedForm = $form->render();



		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
			'project' => $project,
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

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($this->settings['forms']['submission']);
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

		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
			'project' => $project,
		));


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
		$this->notificationMailService->sendNotificationMail('submissionActivatedNotification', $project, $project->getProjectManager(), $project->getProjectManager()->getName(), $project->getProjectManager()->getPrimaryElectronicAddress());

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

}

?>