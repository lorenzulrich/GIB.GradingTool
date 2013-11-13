<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class AdminController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectRepository
	 * @Flow\Inject
	 */
	protected $projectRepository;

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectManagerRepository
	 * @Flow\Inject
	 */
	protected $projectManagerRepository;

	/**
	 * @var \TYPO3\Form\Persistence\YamlPersistenceManager
	 * @Flow\Inject
	 */
	protected $yamlPersistenceManager;

	/**
	 * @var \TYPO3\Flow\I18n\Service
	 * @Flow\Inject
	 */
	protected $i18nService;

	/**
	 * @var \TYPO3\Flow\Security\AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @var \TYPO3\Party\Domain\Repository\PartyRepository
	 * @Flow\Inject
	 */
	protected $partyRepository;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @var \TYPO3\Flow\Security\Cryptography\HashService
	 * @Flow\Inject
	 */
	protected $hashService;

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 * @api
	 */
	protected function initializeAction() {
		$locale = new \TYPO3\Flow\I18n\Locale('en');
		$this->i18nService->getConfiguration()->setCurrentLocale($locale);
	}

	/**
	 * @return void
	 */
	public function indexAction() {
		$projects = $this->projectRepository->findAll();
		$projectManagers = $this->projectManagerRepository->findAll();
		$this->view->assignMultiple(array(
			'projects' => $projects,
			'projectManagers' => $projectManagers,
			'currentAction' => $this->request->getControllerActionName(),
		));
	}

	/**
	 * Tool settings like Forms, Languages
	 */
	public function settingsAction() {
		$forms = $this->yamlPersistenceManager->listForms();

		$this->view->assignMultiple(array(
			'currentAction' => $this->request->getControllerActionName(),
			'forms' => $forms,
		));
	}

	/**
	 * List accounts
	 */
	public function accountsAction() {
		$accounts = $this->accountRepository->findAll();
		$this->view->assignMultiple(array(
			'currentAction' => $this->request->getControllerActionName(),
			'currentAccountIdentifier' => $this->securityContext->getAccount()->getAccountIdentifier(),
			'accounts' => $accounts,
		));
	}

	/**
	 * Edit a project manager and his account
	 *
	 * @param \GIB\GradingTool\Domain\Model\ProjectManager $projectManager
	 * @param \TYPO3\Flow\Security\Account $account
	 */
	public function editProjectManagerAndAccountAction(\GIB\GradingTool\Domain\Model\ProjectManager $projectManager, \TYPO3\Flow\Security\Account $account) {
		$projectManagerAndAccount = new \GIB\GradingTool\Domain\Dto\ProjectManagerAndAccountDto($projectManager, $account);

		$this->view->assignMultiple(array(
			'projectManagerAndAccount' => $projectManagerAndAccount,
		));
	}

	/**
 	 * Update a project manager and his account
	 *
	 * @param \GIB\GradingTool\Domain\Dto\ProjectManagerAndAccountDto $projectManagerAndAccount
	 * @param \TYPO3\Flow\Security\Account $account
	 * @Flow\Validate(argumentName="account", type="UniqueEntity")
	 */
	public function updateProjectManagerAndAccountAction(\GIB\GradingTool\Domain\Dto\ProjectManagerAndAccountDto $projectManagerAndAccount, \TYPO3\Flow\Security\Account $account) {
		$this->partyRepository->update($projectManagerAndAccount->getProjectManager());
		$this->accountRepository->update($projectManagerAndAccount->getAccount());
		$this->persistenceManager->persistAll();

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The account "%s" was successfully updated.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($projectManagerAndAccount->getAccount()->getAccountIdentifier()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('accounts', 'Admin');
	}

	/**
	 * Remove a project manager and his account
	 *
	 * @param \TYPO3\Flow\Security\Account $account
	 */
	public function removeProjectManagerAndAccountAction(\TYPO3\Flow\Security\Account $account) {
		$party = $account->getParty();
		$this->partyRepository->remove($party);
		$this->accountRepository->remove($account);
		$this->persistenceManager->persistAll();

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The account "%s" was removed.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($account->getAccountIdentifier()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('accounts', 'Admin');
	}

	/**
	 * Edit password for an account
	 *
	 * @param \TYPO3\Flow\Security\Account $account
	 */
	public function editAccountPasswordAction(\TYPO3\Flow\Security\Account $account) {
		$this->view->assign('account', $account);
	}

	/**
	 * Reset password for an account
	 *
	 * @param \TYPO3\Flow\Security\Account $account
	 * @param string $newPassword
	 * @Flow\Validate(argumentName="newPassword", type="\GIB\GradingTool\Validation\Validator\PasswordValidator", options={"minimumLength"=6})
	 */
	public function resetAccountPasswordAction(\TYPO3\Flow\Security\Account $account, $newPassword) {

		$account->setCredentialsSource($this->hashService->hashPassword($newPassword, 'default'));
		$this->accountRepository->update($account);

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The password for "%s" was reset.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($account->getAccountIdentifier()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('accounts', 'Admin');
	}

	/**
	 * Remove error FlashMessage
	 * @return \TYPO3\Flow\Error\Message|void
	 */
	public function getErrorFlashMessage() {
		return FALSE;
	}

}

?>