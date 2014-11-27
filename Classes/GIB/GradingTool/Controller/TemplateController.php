<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class TemplateController extends AbstractBaseController {

	/**
	 * @var \GIB\GradingTool\Domain\Repository\TemplateRepository
	 * @Flow\Inject
	 */
	protected $templateRepository;

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

		/** @var \TYPO3\Form\Factory\ArrayFormFactory $factory */
		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($this->settings['forms']['emailTemplate']);
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
	 * Edit a template
	 *
	 * @param \GIB\GradingTool\Domain\Model\Template $template
	 */
	public function editAction(\GIB\GradingTool\Domain\Model\Template $template) {

		$templateContentArray = unserialize($template->getContent());

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($this->settings['forms']['emailTemplate']);
		$overrideConfiguration = $this->formPersistenceManager->load($formName);
		$formDefinition = $factory->build($overrideConfiguration);

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
		//$this->templateRepository->remove($template);
		//$this->persistenceManager->persistAll();

		// add a flash message
		//$message = new \TYPO3\Flow\Error\Message('The template "%s" was successfully removed.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($template->getTemplateIdentifier()));
		//$this->flashMessageContainer->addMessage($message);

		// disabled removeAction because of accidentally removed templates in the past

		$this->redirect('list', 'Template');
	}

}

?>