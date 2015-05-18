<?php
namespace GIB\GradingTool\Service;


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;
use TYPO3\SwiftMailer\Message;

class NotificationMailService {

	/**
	 * @var \GIB\GradingTool\Domain\Repository\TemplateRepository
	 * @Flow\Inject
	 */
	protected $templateRepository;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param $templateIdentifier
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @param \GIB\GradingTool\Domain\Model\ProjectManager $projectManager
	 * @param string $recipientName
	 * @param string $recipientEmail
	 * @param string $additionalContent
	 * @param array $attachements
	 * @return bool|void
	 */
	public function sendNotificationMail($templateIdentifier, \GIB\GradingTool\Domain\Model\Project $project, \GIB\GradingTool\Domain\Model\ProjectManager $projectManager = NULL, $recipientName = '', $recipientEmail = '', $additionalContent = '', $attachements = array()) {
		if ($this->settings['email']['activateNotifications'] === FALSE) {
			return TRUE;
		}

		/** @var \GIB\GradingTool\Domain\Model\Template $template */
		$template = $this->templateRepository->findOneByTemplateIdentifier($templateIdentifier);
		$templateContentArray = unserialize($template->getContent());

		// some kind of wrapper of all e-mail templates containing the HTML structure and styles
		$beforeContent = Files::getFileContents($this->settings['email']['beforeContentTemplate']);
		$afterContent = Files::getFileContents($this->settings['email']['afterContentTemplate']);

		/** @var \TYPO3\Fluid\View\StandaloneView $emailView */
		$emailView = new \TYPO3\Fluid\View\StandaloneView();
		$emailView->setTemplateSource('<f:format.raw>' . $beforeContent . $templateContentArray['content'] . $afterContent . '</f:format.raw>');
		$emailView->setPartialRootPath(FLOW_PATH_PACKAGES . 'Application/GIB.GradingTool/Resources/Private/Partials');
		$emailView->setFormat('html');
		$emailView->assignMultiple(array(
			'beforeContent' => $beforeContent,
			'afterContent' => $afterContent,
			'project' => $project,
			'projectManager' => $projectManager,
			'dataSheetContent' => $project->getDataSheetContentArray(),
			'additionalContent' => $additionalContent,
		));
		$emailBody = $emailView->render();
		/** @var \TYPO3\SwiftMailer\Message $email */
		$email = new Message();
		$email->setFrom(array($templateContentArray['senderEmail'] => $templateContentArray['senderName']));
		// the recipient e-mail can be overridden by method arguments
		if (!empty($recipientEmail)) {
			$email->setTo(array((string)$recipientEmail => (string)$recipientName));
			// in this case, send a bcc to the GIB team
			$email->setBcc(array($templateContentArray['senderEmail'] => $templateContentArray['senderName']));
		} else {
			$email->setTo(array($templateContentArray['recipientEmail'] => $templateContentArray['recipientName']));
		}
		if (!empty($attachements)) {
			foreach ($attachements as $attachement) {
				$email->attach(
					\Swift_Attachment::fromPath($attachement['source'])->setFilename($attachement['fileName'])
				);
			}
		}
		$email->setSubject($templateContentArray['subject']);
		$email->setBody($emailBody, 'text/html');
		$email->send();

	}


}