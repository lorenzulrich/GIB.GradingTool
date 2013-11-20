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
	 * @var \GIB\GradingTool\Domain\Model\ProjectManager
	 * @ORM\ManyToOne(inversedBy="projects")
	 */
	protected $projectManager;

	/**
	 * Serialized representation of Data Sheet Content
	 *
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $dataSheetContent;

	/**
	 * @var string
	 */
	protected $projectTitle;

	/**
	 * @ORM\Column(nullable=true)
	 * @var \DateTime
	 */
	protected $created;

	/**
	 * @ORM\Column(nullable=true)
	 * @var \DateTime
	 */
	protected $lastUpdated;

	/**
	 * @var boolean
	 */
	protected $submissionFormAccess = FALSE;

	/**
	 * Serialized representation of Submission
	 *
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $submissionContent = '';

	/**
	 * @var string
	 */
	protected $language;

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

	/**
	 * @param \DateTime $created
	 */
	public function setCreated($created) {
		$this->created = $created;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @param \DateTime $lastUpdated
	 */
	public function setLastUpdated($lastUpdated) {
		$this->lastUpdated = $lastUpdated;
	}

	/**
	 * @return \DateTime
	 */
	public function getLastUpdated() {
		return $this->lastUpdated;
	}

	/**
	 * @param boolean $submissionFormAccess
	 */
	public function setSubmissionFormAccess($submissionFormAccess) {
		$this->submissionFormAccess = $submissionFormAccess;
	}

	/**
	 * @return boolean
	 */
	public function getSubmissionFormAccess() {
		return $this->submissionFormAccess;
	}

	/**
	 * @param mixed $submissionContent
	 */
	public function setSubmissionContent($submissionContent) {
		$this->submissionContent = $submissionContent;
	}

	/**
	 * @return mixed
	 */
	public function getSubmissionContent() {
		return $this->submissionContent;
	}

	/**
	 * @param string $language
	 */
	public function setLanguage($language) {
		$this->language = $language;
	}

	/**
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}

}
?>