<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class ProjectController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectRepository
	 * @Flow\Inject
	 */
	protected $projectRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Form\Persistence\FormPersistenceManagerInterface
	 */
	protected $formPersistenceManager;

	/**
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * Add a new data sheet
	 */
	public function newDataSheetAction() {
	}

	public function editDataSheetAction() {

		/** @var \GIB\GradingTool\Domain\Model\Project $project */
		$project = $this->projectRepository->findByIdentifier($this->request->getArgument('project')['__identity']);
		$dataSheetContentArray = unserialize($project->getDataSheetContent());

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$overrideConfiguration = $this->formPersistenceManager->load('dataSheetForm');
		$formDefinition = $factory->build($overrideConfiguration, 'gibdatasheet');

		foreach ($dataSheetContentArray as $dataSheetField => $dataSheetContent) {
			$formDefinition->addElementDefaultValue($dataSheetField, $dataSheetContent);
		}

		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);

		$renderedForm = $form->render();



		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
		));

	}

}

?>