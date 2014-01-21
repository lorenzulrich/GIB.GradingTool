<?php
namespace GIB\GradingTool\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class TemplateRepository extends Repository {

	protected $defaultOrderings = array(
		'templateIdentifier' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING
	);
}
?>