<?php
namespace GIB\GradingTool\Form\FormElements;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A password with confirmation form element
 */
class IfNotAuthenticatedRequiredPasswordWithConfirmation extends \TYPO3\Form\Core\Model\AbstractFormElement {

	/**
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 * @Flow\Inject
	 */
	protected $authenticationManager;

	/**
	 * Executed before the current element is outputted to the client
	 *
	 * @param \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime
	 */
	public function beforeRendering(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime) {
		$this->requireIfTriggerIsSet($formRuntime);

		if ($formRuntime->getRequest()->getParentRequest()->getControllerActionName() == 'newDataSheet') {
			if ($this->authenticationManager->isAuthenticated() && $this->authenticationManager->getSecurityContext()->hasRole('GIB.GradingTool:ProjectManager')) {
				$this->setRenderingOption('templatePathPattern', 'resource://GIB.GradingTool/Private/Form/NoOutput.html');
			}
		} elseif ($formRuntime->getRequest()->getParentRequest()->getControllerActionName() == 'editDataSheet') {
			$this->setRenderingOption('templatePathPattern', 'resource://GIB.GradingTool/Private/Form/NoOutput.html');
		}

	}

	/**
	 * Executed after the page containing the current element has been submitted
	 *
	 * @param \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime
	 * @param $elementValue raw value of the submitted element
	 */
	public function onSubmit(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime, &$elementValue) {
		if ($elementValue['password'] !== $elementValue['confirmation']) {
			$processingRule = $this->getRootForm()->getProcessingRule($this->getIdentifier());
			$processingRule->getProcessingMessages()->addError(new \TYPO3\Flow\Error\Error('Password doesn\'t match confirmation', 1334768052));
		}
		$elementValue = $elementValue['password'];
		$this->requireIfTriggerIsSet($formRuntime);
	}

	/**
	 * Adds a NotEmptyValidator to the current element if the "trigger" value is not empty.
	 * The trigger can be configured with $this->properties['triggerPropertyPath']
	 *
	 * @param \TYPO3\Form\Core\Runtime\FormRunTime $formRuntime
	 * @return void
	 */
	protected function requireIfTriggerIsSet(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime) {
		if ($formRuntime->getRequest()->getParentRequest()->getControllerActionName() == 'newDataSheet') {
			if ($this->authenticationManager->isAuthenticated() && $this->authenticationManager->getSecurityContext()->hasRole('GIB.GradingTool:Administrator')) {
				$this->addValidator(new \TYPO3\Flow\Validation\Validator\NotEmptyValidator());
			} elseif (!$this->authenticationManager->isAuthenticated()) {
				$this->addValidator(new \TYPO3\Flow\Validation\Validator\NotEmptyValidator());
			} else {
				return;
			}

		} elseif ($formRuntime->getRequest()->getParentRequest()->getControllerActionName() == 'editDataSheet') {
			return;
		}
	}

}
