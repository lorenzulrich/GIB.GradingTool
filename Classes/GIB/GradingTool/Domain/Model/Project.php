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
	 * @var \GIB\GradingTool\Service\CldrService
	 * @Flow\Inject
	 */
	protected $cldrService;

	/**
	 * @var \TYPO3\Flow\I18n\Cldr\CldrRepository
	 * @Flow\Inject
	 */
	protected $cldrRepository;

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
	 * Data Sheet Content
	 *
	 * @var array
	 * @Flow\Transient
	 */
	protected $dataSheetContentArray;

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
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $submissionContent = '';

	/**
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $language;

	/**
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $countryCode;

	/**
	 * @var string
	 * @Flow\Transient
	 */
	protected $country;

	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $categories;

	/**
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $cost;

	/**
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $region;

	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $stage;

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
	 * Return the unserialized data sheet content
	 * 
	 * @return array|mixed
	 */
	public function getDataSheetContentArray() {
		if ($this->dataSheetContentArray === NULL) {
			$this->dataSheetContentArray = unserialize($this->getDataSheetContent());
		}
		return $this->dataSheetContentArray;
	}

	/**
	 * Set dataSheetContent but also the flattened part of it
	 *
	 * @param string, $dataSheetContent
	 * @return void
	 */
	public function setDataSheetContent($dataSheetContent) {
		if (isset($dataSheetContent['projectTitle'])) {
			$this->setProjectTitle($dataSheetContent['projectTitle']);
		}
		if (isset($dataSheetContent['language'])) {
			$this->setLanguage($dataSheetContent['language']);
		}
		// todo setRegion
		if (isset($dataSheetContent['stage'])) {
			$this->setStage(implode($dataSheetContent['stage']));
		}
		if (isset($dataSheetContent['categories'])) {
			$this->setCategories(implode($dataSheetContent['categories']));
		}
		if (isset($dataSheetContent['cost'])) {
			$this->setCost($dataSheetContent['cost']);
		}
		if (isset($dataSheetContent['country'])) {
			$this->setCountryCode($dataSheetContent['country']);
		}
		$this->dataSheetContent = serialize($dataSheetContent);
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

	/**
	 * @param string $countryCode
	 */
	public function setCountryCode($countryCode) {
		$this->countryCode = $countryCode;
	}

	/**
	 * @return string
	 */
	public function getCountryCode() {
		return $this->countryCode;
	}

	/**
	 * The textual representation of a country
	 *
	 * @return string
	 */
	public function getCountry() {
		return $this->cldrService->getCountryNameForIsoCode($this->getCountryCode());
	}

	/**
	 * @param string $cost
	 */
	public function setCost($cost)	{
		$this->cost = $cost;
	}

	/**
	 * @return string
	 */
	public function getCost()	{
		return $this->cost;
	}

	/**
	 * @param string $categories
	 */
	public function setCategories($categories)	{
		$this->categories = $categories;
	}

	/**
	 * @return string
	 */
	public function getCategories()	{
		return $this->categories;
	}

	/**
	 * @param mixed $region
	 */
	public function setRegion($region)	{
		$this->region = $region;
	}

	/**
	 * @return mixed
	 */
	public function getRegion()	{
		return $this->region;
	}

	/**
	 * @param string $stage
	 */
	public function setStage($stage) {
		$this->stage = $stage;
	}

	/**
	 * @return string
	 */
	public function getStage() {
		return $this->stage;
	}

}
?>