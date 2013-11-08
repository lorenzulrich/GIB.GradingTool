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
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $dataSheetContent;

	/**
	 * @var string
	 */
	protected $projectTitle;


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