<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class AdminController extends AbstractBaseController {

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
	 * @var \TYPO3\Flow\Security\AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @var \TYPO3\Flow\Security\AccountFactory
	 * @Flow\Inject
	 */
	protected $accountFactory;

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
	 * @var \GIB\GradingTool\Service\SubmissionService
	 * @Flow\Inject
	 */
	protected $submissionService;

	/**
	 * @var \TYPO3\Flow\Security\Policy\PolicyService
	 * @Flow\Inject
	 */
	protected $policyService;

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

		$excelExportFilePathAndFileName =  \TYPO3\Flow\Utility\Files::concatenatePaths(array($this->settings['export']['excel']['templatePath'], $this->settings['export']['excel']['templateFileName']));

		$this->view->assignMultiple(array(
			'currentAction' => $this->request->getControllerActionName(),
			'forms' => $forms,
			'languages' => $this->settings['languages'],
			'scoreData' => $this->submissionService->getScoreData(),
			'excelExportTemplateFound' => is_file($excelExportFilePathAndFileName),
		));
	}

	/**
	 * Create or upload an Excel export template
	 *
	 * @param \TYPO3\Flow\Resource\Resource $excelExportTemplateFile
	 */
	public function createUpdateExcelExportTemplateFileAction(\TYPO3\Flow\Resource\Resource $excelExportTemplateFile = NULL) {
		if ($excelExportTemplateFile === NULL) {
			// Go back to settings if no file was uploaded
			$message = new \TYPO3\Flow\Error\Message('No file was uploaded. The Excel export template was not replaced.', \TYPO3\Flow\Error\Message::SEVERITY_WARNING);
			$this->flashMessageContainer->addMessage($message);
			$this->redirect('settings');
		}

		$fileContent = file_get_contents('resource://' . $excelExportTemplateFile);
		// Create templates folder if it doesn't exist
		if (!is_dir($this->settings['export']['excel']['templatePath'])) {
			\TYPO3\Flow\Utility\Files::createDirectoryRecursively($this->settings['export']['excel']['templatePath']);
		}
		$targetFilePathAndFileName =  \TYPO3\Flow\Utility\Files::concatenatePaths(array($this->settings['export']['excel']['templatePath'], $this->settings['export']['excel']['templateFileName']));
		file_put_contents($targetFilePathAndFileName, $fileContent);

		$message = new \TYPO3\Flow\Error\Message('The Excel export template has been replaced successfully.', \TYPO3\Flow\Error\Message::SEVERITY_OK);
		$this->flashMessageContainer->addMessage($message);
		$this->redirect('settings');
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


	public function newAccountAction() {
		$roles = $this->policyService->getRoles();
		// unset all roles that are not related to our package
		foreach ($roles as $key => $role) {
			if (explode(':', $key)[0] !== 'GIB.GradingTool') {
				unset($roles[$key]);
			}
		}
		$this->view->assign('roles', $roles);
	}

	/**
	 * Create a new account
	 *
	 * @param string $firstName
	 * @Flow\Validate(argumentName="firstName", type="NotEmpty")
	 * @param string $lastName
	 * @Flow\Validate(argumentName="lastName", type="NotEmpty")
	 * @param string $primaryElectronicAddress
	 * @Flow\Validate(argumentName="primaryElectronicAddress", type="EmailAddress")
	 * @Flow\Validate(argumentName="primaryElectronicAddress", type="NotEmpty")
	 * @param string $identifier
	 * @Flow\Validate(argumentName="identifier", type="NotEmpty")
	 * @Flow\Validate(argumentName="identifier", type="\TYPO3\AccountManagement\Validation\Validator\AccountExistsValidator")
	 * @param string $password
	 * @Flow\Validate(argumentName="password", type="\GIB\GradingTool\Validation\Validator\PasswordValidator", options={"minimumLength"=6})
	 * @Flow\Validate(argumentName="password", type="NotEmpty")
	 * @param string $role
	 */
	public function createAccountAction($firstName, $lastName, $primaryElectronicAddress, $identifier, $password, $role) {
		// we use the projectManager also as model for an admin account, even if the admin won't have any projects
		$projectManager = new \GIB\GradingTool\Domain\Model\ProjectManager();
		$projectManagerName = new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName);
		$projectManager->setName($projectManagerName);
		$projectManagerElectronicAddress = new \TYPO3\Party\Domain\Model\ElectronicAddress();
		$projectManagerElectronicAddress->setIdentifier($primaryElectronicAddress);
		$projectManagerElectronicAddress->setType(\TYPO3\Party\Domain\Model\ElectronicAddress::TYPE_EMAIL);
		$projectManager->addElectronicAddress($projectManagerElectronicAddress);
		$projectManager->setPrimaryElectronicAddress($projectManagerElectronicAddress);

		// add account
		$roles = array($role);
		$authenticationProviderName = 'DefaultProvider';
		$account = $this->accountFactory->createAccountWithPassword($identifier, $password, $roles, $authenticationProviderName);
		$this->accountRepository->add($account);

		// add account to ProjectManager
		$projectManager->addAccount($account);

		// finally add the complete ProjectManager
		$this->partyRepository->add($projectManager);

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('An administrator account "%s" was created.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($identifier));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('accounts');

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

}

?>