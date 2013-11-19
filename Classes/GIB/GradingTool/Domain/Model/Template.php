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
class Template {

	/**
	 * @var string
	 */
	protected $templateIdentifier;

	/**
	 * Serialized representation of Data Sheet Content
	 *
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $content;

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $templateIdentifier
	 */
	public function setTemplateIdentifier($templateIdentifier) {
		$this->templateIdentifier = $templateIdentifier;
	}

	/**
	 * @return string
	 */
	public function getTemplateIdentifier() {
		return $this->templateIdentifier;
	}

}
?>