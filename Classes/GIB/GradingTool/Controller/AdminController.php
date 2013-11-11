<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class AdminController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectRepository
	 * @Flow\Inject
	 */
	protected $projectRepository;

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectManagerRepository
	 * @Flow\Inject
	 */
	protected $projectManagerRepository;

	/**
	 * @var \TYPO3\Form\Persistence\YamlPersistenceManager
	 * @Flow\Inject
	 */
	protected $yamlPersistenceManager;

	/**
	 * @return void
	 */
	public function indexAction() {
		$projects = $this->projectRepository->findAll();
		$projectManagers = $this->projectManagerRepository->findAll();
		$this->view->assignMultiple(array(
			'projects' => $projects,
			'projectManagers' => $projectManagers,
			'currentAction' => $this->request->getControllerActionName(),
		));
	}

	/**
	 *
	 */
	public function settingsAction() {
		$forms = $this->yamlPersistenceManager->listForms();

		$this->view->assignMultiple(array(
			'currentAction' => $this->request->getControllerActionName(),
			'forms' => $forms,
		));
	}

}

?>