<?php
namespace GIB\GradingTool\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class ProjectManager extends \TYPO3\Party\Domain\Model\Person {

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	protected $projects;

	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getProjects() {
		return $this->projects;
	}

	/**
	 * @param \Doctrine\Common\Collections\Collection $projects
	 * @return void
	 */
	public function setProjects(\Doctrine\Common\Collections\Collection $projects) {
		$this->projects = $projects;
	}

}
?>