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
class Project {

	/**
	 * The blog
	 * @var \GIB\GradingTool\Domain\Model\ProjectManager
	 * @ORM\ManyToOne(inversedBy="projects")
	 */
	protected $projectManager;

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $dataSheetContent;

	/**
	 * @var string
	 */
	protected $projectTitle;

	/**
	 * Sets the project manager of a project
	 *
	 * @param \GIB\GradingTool\Domain\Model\ProjectManager $projectManager The projectManager
	 * @return void
	 */
	public function setProjectManager(\GIB\GradingTool\Domain\Model\ProjectManager $projectManager) {
		$this->projectManager = $projectManager;
	}

	/**
	 * Returns the project manager of the project
	 *
	 * @return \GIB\GradingTool\Domain\Model\ProjectManager The projectManager this project is owned by
	 */
	public function getProjectManager() {
		return $this->projectManager;
	}

	/**
	 * @return string
	 */
	public function getDataSheetContent() {
		return $this->dataSheetContent;
	}

	/**
	 * @param string, $dataSheetContent
	 * @return void
	 */
	public function setDataSheetContent($dataSheetContent) {
		$this->dataSheetContent = $dataSheetContent;
	}

	/**
	 * @return string
	 */
	public function getProjectTitle() {
		return $this->projectTitle;
	}

	/**
	 * @param string $projectTitle
	 * @return void
	 */
	public function setProjectTitle($projectTitle) {
		$this->projectTitle = $projectTitle;
	}

}
?>