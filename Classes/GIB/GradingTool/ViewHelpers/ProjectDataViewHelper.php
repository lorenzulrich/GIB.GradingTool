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

class ProjectDataViewHelper extends AbstractViewHelper {


	/**
	 * @var \GIB\GradingTool\Service\ProjectDataService
	 * @Flow\Inject
	 */
	protected $projectDataService;

	/**
	 * Iterates through elements of $each and renders child nodes
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @param string $as The name of the iteration variable
	 * @param string $key The name of the variable to store the current array key
	 * @param string $iteration The name of the variable to store iteration information (index, cycle, isFirst, isLast, isEven, isOdd)
	 * @return string Rendered string
	 * @api
	 */
	public function render($project, $as, $key = '', $iteration = NULL) {
		$this->arguments['each'] = $this->projectDataService->getProcessedProjectData($project);
		return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 * @throws ViewHelper\Exception
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$templateVariableContainer = $renderingContext->getTemplateVariableContainer();
		if ($arguments['each'] === NULL) {
			return '';
		}
		if (!is_array($arguments['each'])) {
			throw new ViewHelper\Exception('ProjectDataViewHelper only supports arrays and objects', 1248728393);
		}

		$iterationData = array(
			'index' => 0,
			'cycle' => 1,
			'total' => count($arguments['each'])
		);

		$output = '';
		foreach ($arguments['each'] as $keyValue => $singleElement) {
			$templateVariableContainer->add($arguments['as'], $singleElement);
			if ($arguments['key'] !== '') {
				$templateVariableContainer->add($arguments['key'], $keyValue);
			}
			if ($arguments['iteration'] !== NULL) {
				$iterationData['isFirst'] = $iterationData['cycle'] === 1;
				$iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
				$iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
				$iterationData['isOdd'] = !$iterationData['isEven'];
				$templateVariableContainer->add($arguments['iteration'], $iterationData);
				$iterationData['index']++;
				$iterationData['cycle']++;
			}
			$output .= $renderChildrenClosure();
			$templateVariableContainer->remove($arguments['as']);
			if ($arguments['key'] !== '') {
				$templateVariableContainer->remove($arguments['key']);
			}
			if ($arguments['iteration'] !== NULL) {
				$templateVariableContainer->remove($arguments['iteration']);
			}
		}
		return $output;
	}
}
