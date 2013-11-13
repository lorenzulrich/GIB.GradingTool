<?php
namespace GIB\GradingTool\Domain\Dto;

class ProjectManagerAndAccountDto {

	/**
	 * @var \GIB\GradingTool\Domain\Model\ProjectManager
	 */
	protected $projectManager;

	/**
	 * @var \TYPO3\Flow\Security\Account
	 */
	protected $account;

	/**
	 * @param \GIB\GradingTool\Domain\Model\ProjectManager $projectManager
	 * @param \TYPO3\Flow\Security\Account $account
	 */
	public function __construct(\GIB\GradingTool\Domain\Model\ProjectManager $projectManager, \TYPO3\Flow\Security\Account $account) {
		$this->projectManager = $projectManager;
		$this->account = $account;
	}

	/**
	 * @param \TYPO3\Flow\Security\Account $account
	 */
	public function setAccount($account) {
		$this->account = $account;
	}

	/**
	 * @return \TYPO3\Flow\Security\Account
	 */
	public function getAccount() {
		return $this->account;
	}

	/**
	 * @param \GIB\GradingTool\Domain\Model\ProjectManager $projectManager
	 */
	public function setProjectManager($projectManager) {
		$this->projectManager = $projectManager;
	}

	/**
	 * @return \GIB\GradingTool\Domain\Model\ProjectManager
	 */
	public function getProjectManager() {
		return $this->projectManager;
	}

}
?>