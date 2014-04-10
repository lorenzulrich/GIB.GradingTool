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
	 * @var int
	 * @ORM\Column(columnDefinition="INT(11) NOT NULL AUTO_INCREMENT UNIQUE")
	 */
	protected $uid;

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
	 * Serialized representation of Project Data Content
	 *
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $projectData;

	/**
	 * Project Data Content
	 *
	 * @var array
	 * @Flow\Transient
	 */
	protected $projectDataArray;

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
	 * @ORM\Column(nullable=true)
	 * @var \DateTime
	 */
	protected $submissionLastUpdated;

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
	 * @ORM\Column(nullable=true)
	 */
	protected $regionCode;

	/**
	 * @var string
	 * @Flow\Transient
	 */
	protected $region;

	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $categories;

	/**
	 * @var float
	 * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
	 */
	protected $cost;

	/**
	 * @var float
	 * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
	 */
	protected $requiredInvestment;

	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $stage;

	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $status;

	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $gibYear;

	/**
	 * @var boolean
	 */
	protected $isVisibleInProjectFinder = FALSE;

	/**
	 * @param int $uid
	 */
	public function setUid($uid) {
		$this->uid = $uid;
	}

	/**
	 * @return int
	 */
	public function getUid() {
		return $this->uid;
	}

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
			// take these properties from the project model to make debugging the decimals easier
			$this->dataSheetContentArray['cost'] = $this->getCost();
			$this->dataSheetContentArray['requiredInvestment'] = $this->getRequiredInvestment();
		}
		return $this->dataSheetContentArray;
	}

	/**
	 * Set dataSheetContent but also the flattened part of it
	 *
	 * @param array $dataSheetContent
	 * @return void
	 */
	public function setDataSheetContent($dataSheetContent) {
		if (isset($dataSheetContent['projectTitle'])) {
			$this->setProjectTitle($dataSheetContent['projectTitle']);
		}
		if (isset($dataSheetContent['language'])) {
			$this->setLanguage($dataSheetContent['language']);
		}
		if (isset($dataSheetContent['stage']) && is_array($dataSheetContent['stage'])) {
			$this->setStage(implode($dataSheetContent['stage']));
		}
		if (isset($dataSheetContent['categories']) && is_array($dataSheetContent['categories'])) {
			$this->setCategories(implode($dataSheetContent['categories']));
		}
		if (isset($dataSheetContent['cost'])) {
			$this->setCost($dataSheetContent['cost']);
		}
		if (isset($dataSheetContent['requiredInvestment'])) {
			$this->setRequiredInvestment($dataSheetContent['requiredInvestment']);
		}
		if (isset($dataSheetContent['country'])) {
			$this->setCountryCode($dataSheetContent['country']);
			$this->setRegionCode($this->cldrService->getRegionIsoCodeForCountryIsoCode($dataSheetContent['country']));
		}
		$this->dataSheetContent = serialize($dataSheetContent);
	}

	/**
	 * @return string
	 */
	public function getProjectData() {
		return $this->projectData;
	}

	/**
	 * Return the unserialized data sheet content
	 *
	 * @return array|mixed
	 */
	public function getProjectDataArray() {
		$projectData = $this->getProjectData();
		if ($this->projectDataArray === NULL && !empty($projectData)) {
			$this->projectDataArray = unserialize($this->getProjectData());
		}
		return $this->projectDataArray;
	}

	/**
	 * Set projectData but also the flattened part of it
	 *
	 * @param array $projectData
	 * @return void
	 */
	public function setProjectData($projectData) {
		if (!empty($projectData['listProject'])) {
			$this->setIsVisibleInProjectFinder(TRUE);
		} else {
			$this->setIsVisibleInProjectFinder(FALSE);
		}
		if (isset($projectData['status'])) {
			$this->setStatus($projectData['status']);
		}
		if (isset($projectData['gib'])) {
			$this->setGibYear($projectData['gib']);
		}
		$this->projectData = serialize($projectData);
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
	 * @return \DateTime
	 */
	public function getSubmissionLastUpdated() {
		return $this->submissionLastUpdated;
	}

	/**
	 * @param \DateTime $submissionLastUpdated
	 */
	public function setSubmissionLastUpdated($submissionLastUpdated) {
		$this->submissionLastUpdated = $submissionLastUpdated;
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
	 * @return string
	 */
	public function getRegionCode() {
		return $this->regionCode;
	}

	/**
	 * @param string $regionCode
	 */
	public function setRegionCode($regionCode) {
		$this->regionCode = $regionCode;
	}

	/**
	 * The textual representation of a country
	 *
	 * @return string
	 */
	public function getCountry() {
		$countryName = $this->cldrService->getTerritoryNameForIsoCode($this->getCountryCode());
		if ($countryName !== $this->getCountryCode()) {
			return $countryName;
		} elseif ($this->getCountryCode() === 'XK') {
			// workaround because Kosovo isn't formally a country and therefore not in the CLDR repository
			return 'Kosovo';
		} else {
			return $this->getCountryCode();
		}

	}

	/**
	 * @param float $cost
	 */
	public function setCost($cost)	{
		$this->cost = $cost;
	}

	/**
	 * @return float
	 */
	public function getCost()	{
		return $this->cost;
	}

	/**
	 * @param float $requiredInvestment
	 */
	public function setRequiredInvestment($requiredInvestment) {
		$this->requiredInvestment = $requiredInvestment;
	}

	/**
	 * @return float
	 */
	public function getRequiredInvestment() {
		return $this->requiredInvestment;
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

	/**
	 * @param string $gibYear
	 */
	public function setGibYear($gibYear) {
		$this->gibYear = $gibYear;
	}

	/**
	 * @return string
	 */
	public function getGibYear() {
		return $this->gibYear;
	}

	/**
	 * @param boolean $isVisibleInProjectFinder
	 */
	public function setIsVisibleInProjectFinder($isVisibleInProjectFinder) {
		$this->isVisibleInProjectFinder = $isVisibleInProjectFinder;
	}

	/**
	 * @return boolean
	 */
	public function getIsVisibleInProjectFinder() {
		return $this->isVisibleInProjectFinder;
	}

	/**
	 * @param string $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

}
?>