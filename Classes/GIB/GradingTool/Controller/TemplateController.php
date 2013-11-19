<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class TemplateController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var \GIB\GradingTool\Domain\Repository\TemplateRepository
	 * @Flow\Inject
	 */
	protected $templateRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Form\Persistence\FormPersistenceManagerInterface
	 */
	protected $formPersistenceManager;

	/**
	 * List action
	 */
	public function listAction() {
		$templates = $this->templateRepository->findAll();
		$this->view->assignMultiple(array(
			'currentAction' => $this->request->getControllerActionName(),
			'templates' => $templates,
		));
	}

	/**
	 * Add a new data sheet
	 *
	 * The create action is missing because the project is added in the
	 * DataSheetFinisher (see Form/Finishers)
	 *
	 */
	public function newAction() {
	}

	/**
	 * Edit a template
	 *
	 * @param \GIB\GradingTool\Domain\Model\Template $template
	 */
	public function editAction(\GIB\GradingTool\Domain\Model\Template $template) {

		$templateContentArray = unserialize($template->getContent());

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$overrideConfiguration = $this->formPersistenceManager->load('emailTemplate');
		$formDefinition = $factory->build($overrideConfiguration, 'gibdatasheet');

		foreach ($templateContentArray as $templateField => $templateContent) {
			$formDefinition->addElementDefaultValue($templateField, $templateContent);
		}

		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);

		$renderedForm = $form->render();

		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
			'template' => $template,
		));

	}

	/**
	 * Remove a template
	 *
	 * @param \GIB\GradingTool\Domain\Model\Template $template
	 */
	public function removeAction(\GIB\GradingTool\Domain\Model\Template $template) {
		$this->templateRepository->remove($template);
		$this->persistenceManager->persistAll();

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The template "%s" was successfully removed.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($template->getTemplateIdentifier()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('list', 'Template');
	}

}

?>