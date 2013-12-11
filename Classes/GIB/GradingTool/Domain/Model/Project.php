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
	 * @ORM\Column(type="text")
	 */
	protected $submissionContent = '';

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var string
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
	 */
	protected $cost;

	/**
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $region;

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

	public function getDataSheetContentArray() {
		if ($this->dataSheetContentArray === NULL) {
			$this->dataSheetContentArray = unserialize($this->getDataSheetContent());
		}
		return $this->dataSheetContentArray;
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

}
?>