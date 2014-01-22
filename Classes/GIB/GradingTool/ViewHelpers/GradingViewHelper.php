<?php
namespace GIB\GradingTool\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Fluid\Core\ViewHelper;
use TYPO3\Flow\Annotations as Flow;

class GradingViewHelper extends AbstractViewHelper {


	/**
	 * @var \GIB\GradingTool\Service\SubmissionService
	 * @Flow\Inject
	 */
	protected $submissionService;

	/**
	 * Renders alias
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $subject
	 * @return string Rendered string
	 * @api
	 */
	public function render(\GIB\GradingTool\Domain\Model\Project $subject) {
		$grading = $this->submissionService->getProcessedSubmission($subject);
		$this->templateVariableContainer->add('grading', $grading);
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove('grading');
		return $output;
	}

}
