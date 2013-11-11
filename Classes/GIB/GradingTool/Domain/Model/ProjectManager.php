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
	 * @var \Doctrine\Common\Collections\Collection<\GIB\GradingTool\Domain\Model\Project>
	 * @ORM\OneToMany(mappedBy="projectManager")
	 */
	protected $projects;

	/**
	 * Constructs a new projectManager
	 */
	public function __construct() {
		$this->projects = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Adds a project to this projectManager
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @return void
	 */
	public function addProject(\GIB\GradingTool\Domain\Model\Project $project) {
		$project->setProjectManager($this);
		$this->projects->add($project);
	}

	/**
	 * Removes a project from this projectManager
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @return void
	 */
	public function removePost(\GIB\GradingTool\Domain\Model\Project $project) {
		$this->projects->removeElement($project);
	}

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