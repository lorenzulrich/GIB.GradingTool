<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Now;

class ProjectController extends AbstractBaseController {

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
	 * @var \GIB\GradingTool\Service\NotificationMailService
	 * @Flow\Inject
	 */
	protected $notificationMailService;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @var \GIB\GradingTool\Service\TemplateService
	 * @Flow\Inject
	 */
	protected $templateService;

	/**
	 * @var \GIB\GradingTool\Service\SubmissionService
	 * @Flow\Inject
	 */
	protected $submissionService;

	/**
	 * @var \GIB\GradingTool\Service\DataSheetService
	 * @Flow\Inject
	 */
	protected $dataSheetService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * @return void
	 */
	public function indexAction() {
	}

	/**
	 * Add a new data sheet
	 *
	 * The create action is missing because the project is added in the
	 * DataSheetFinisher (see Form/Finishers)
	 *
	 */
	public function newDataSheetAction() {
		/** @var \TYPO3\Form\Factory\ArrayFormFactory $factory */
		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($this->settings['forms']['dataSheet']['default']);
		$overrideConfiguration = $this->formPersistenceManager->load($formName);

		$formDefinition = $factory->build($overrideConfiguration);

		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
		$renderedForm = $form->render();

		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
		));

	}

	/**
	 * Edit a project data sheet
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function editDataSheetAction(\GIB\GradingTool\Domain\Model\Project $project) {

		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($project->getDataSheetFormIdentifier());
		$overrideConfiguration = $this->formPersistenceManager->load($formName);
		$formDefinition = $factory->build($overrideConfiguration);

		foreach ($project->getDataSheetContentArray() as $dataSheetField => $dataSheetContent) {
			$formDefinition->addElementDefaultValue($dataSheetField, $dataSheetContent);
		}

		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
		$renderedForm = $form->render();

		// uri for autosave
		$ajaxUri = $this->controllerContext->getUriBuilder()->setCreateAbsoluteUri(TRUE)->uriFor('saveProjectDataValue', array('project' => $project, 'form' => 'dataSheet'), 'Project');

		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
			'project' => $project,
			'ajaxUri' => $ajaxUri,
		));

	}

	/**
	 * Review a data sheet
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function reviewDataSheetAction(\GIB\GradingTool\Domain\Model\Project $project) {

		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		$dataSheet = $this->dataSheetService->getProcessedDataSheet($project);

		$this->view->assignMultiple(array(
			'dataSheet' => $dataSheet,
			'project' => $project,
		));

	}

	/**
	 * Edit the (administrator-only) project data
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function editProjectDataAction(\GIB\GradingTool\Domain\Model\Project $project) {

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($this->settings['forms']['projectData']);
		$overrideConfiguration = $this->formPersistenceManager->load($formName);
		$formDefinition = $factory->build($overrideConfiguration);

		if (is_array($project->getProjectDataArray())) {
			// we already have form data, so we apply it
			foreach ($project->getProjectDataArray() as $projectDataField => $projectDataContent) {
				$formDefinition->addElementDefaultValue($projectDataField, $projectDataContent);
			}
		}
		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
		$renderedForm = $form->render();

		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
			'project' => $project,
		));

	}

	/**
	 * Edit/create a submission
	 *
	 * The create action is missing because the project is added in the
	 * SubmissionFinisher (see Form/Finishers)
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function submissionAction(\GIB\GradingTool\Domain\Model\Project $project) {

		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		if (empty($project->getSubmissionFormIdentifier())) {
			$project->setSubmissionFormIdentifier($this->settings['forms']['submission']['default']);
			$this->projectRepository->update($project);
			$this->persistenceManager->persistAll();
		}

		$factory = $this->objectManager->get('TYPO3\Form\Factory\ArrayFormFactory');
		$formName = $this->getFormNameRespectingLocale($project->getSubmissionFormIdentifier(), $project->getLanguage());
		$overrideConfiguration = $this->formPersistenceManager->load($formName);
		$formDefinition = $factory->build($overrideConfiguration);

		// populate form with existing data
		$submissionContent = $project->getSubmissionContent();
		if (!empty($submissionContent)) {
			$submissionContentArray = unserialize($submissionContent);
			foreach ($submissionContentArray as $submissionField => $submissionContent) {
				$formDefinition->addElementDefaultValue($submissionField, $submissionContent);
			}
		}

		$response = new \TYPO3\Flow\Http\Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
		$renderedForm = $form->render();

		// uri for autosave
		$ajaxUri = $this->controllerContext->getUriBuilder()->setCreateAbsoluteUri(TRUE)->uriFor('saveProjectDataValue', array('project' => $project, 'form' => 'submission'), 'Project');

		$this->view->assignMultiple(array(
			'renderedForm' => $renderedForm,
			'project' => $project,
			'ajaxUri' => $ajaxUri,
		));

	}

	/**
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @param string $form
	 * @param string $formData
	 * @return string
	 */
	public function saveProjectDataValueAction(\GIB\GradingTool\Domain\Model\Project $project, $form, $formData) {
		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		if (!in_array($form, array('submission'))) {
			// security: only allow data changes to submission and dataSheet form
			return FALSE;
		}

		$decodedFormData = urldecode($formData);
		preg_match("/\[(.*?)\]/", $decodedFormData, $matches);
		$fieldIdentifier = $matches[1];

		$fieldValue = explode('=', $decodedFormData)[1];

		$contentGetter = 'get' . ucfirst($form) . 'Content';
		$contentSetter = 'set' . ucfirst($form) . 'Content';
		$contentArray = unserialize($project->$contentGetter());
		$contentArray[$fieldIdentifier] = $fieldValue;
		$project->$contentSetter(serialize($contentArray));
		if ($form === 'submission') {
			$project->setSubmissionLastUpdated(new \TYPO3\Flow\Utility\Now);
		}
		$this->projectRepository->update($project);
		$this->persistenceManager->persistAll();

		return 'Saved.';

	}

	/**
	 * Review a submission
	 *
	 * The create action is missing because the project is added in the
	 * SubmissionFinisher (see Form/Finishers)
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function reviewSubmissionAction(\GIB\GradingTool\Domain\Model\Project $project) {

		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		$submission = $this->submissionService->getProcessedSubmission($project);

		$radarChartImagePathAndFilename = $this->submissionService->getRadarImage($project);
		$radarChartImageResource = $this->resourceManager->importResource($radarChartImagePathAndFilename);
		$radarChartUri = $this->resourcePublisher->getPersistentResourceWebUri($radarChartImageResource);


		$this->view->assignMultiple(array(
			'submission' => $submission,
			'project' => $project,
			'radarChartUri' => $radarChartUri
		));

	}

	/**
	 * Change the state of a field in a submission
	 * This is used for accepting opt-out question comments.
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @param string $fieldIdentifier
	 * @param string $newState
	 */
	public function changeFieldStateForProjectSubmissionAction(\GIB\GradingTool\Domain\Model\Project $project, $fieldIdentifier, $newState) {

		// access check
		$this->checkOwnerOrAdministratorAndDenyIfNeeded($project);

		$submissionContentArray = unserialize($project->getSubmissionContent());
		$submissionContentArray[$fieldIdentifier] = $newState;
		$project->setSubmissionContent(serialize($submissionContentArray));
		$this->projectRepository->update($project);
		$this->persistenceManager->persistAll();

		$this->redirect('reviewSubmission', NULL, NULL, array('project' => $project));
	}

	/**
	 * Remove a project
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function removeAction(\GIB\GradingTool\Domain\Model\Project $project) {
		$this->projectRepository->remove($project);
		$this->persistenceManager->persistAll();

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The project "%s" was successfully removed.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($project->getProjectTitle()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('index', 'Admin');
	}

	/**
	 * Activate the submission form for a project
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function activateSubmissionFormAction(\GIB\GradingTool\Domain\Model\Project $project) {
		$project->setSubmissionFormAccess(TRUE);
		$this->projectRepository->update($project);
		$this->persistenceManager->persistAll();

		// notify user that he was accepted for submission
		$templateIdentifierOverlay = $this->templateService->getTemplateIdentifierOverlay('submissionActivatedNotification', $project);
		$this->notificationMailService->sendNotificationMail($templateIdentifierOverlay, $project, $project->getProjectManager(), $project->getProjectManager()->getName(), $project->getProjectManager()->getPrimaryElectronicAddress());

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The submission form for the project "%s" is now active and the project manager was informed.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($project->getProjectTitle()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('index', 'Admin');
	}

	/**
	 * Deactivate the submission form for a project
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function deactivateSubmissionFormAction(\GIB\GradingTool\Domain\Model\Project $project) {
		$project->setSubmissionFormAccess(FALSE);
		$this->projectRepository->update($project);
		$this->persistenceManager->persistAll();

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('The submission form for the project "%s" is now inactive.', \TYPO3\Flow\Error\Message::SEVERITY_OK, array($project->getProjectTitle()));
		$this->flashMessageContainer->addMessage($message);

		$this->redirect('index', 'Admin');
	}

	/**
	 * Check if an administrator is logged in or the owner of a project and deny access if someone else is trying to access
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function checkOwnerOrAdministratorAndDenyIfNeeded(\GIB\GradingTool\Domain\Model\Project $project) {

		// check if the user has access to this project
		if ($this->securityContext->getParty() !== $project->getProjectManager() &&
			!array_key_exists('GIB.GradingTool:Administrator', $this->securityContext->getRoles())) {
			// add a flash message
			$message = new \TYPO3\Flow\Error\Message('Access denied.', \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->flashMessageContainer->addMessage($message);
			$this->redirect('index', 'Standard');
		}
	}

	/**
	 * Iterates through all the projects and updates them with the current state of the data sheet
	 * This is a helper method needed if the the separately persisted properties changed in the model
	 */
	public function updateAllProjectsAction() {
		$projects = $this->projectRepository->findAll();
		$i = 0;
		foreach ($projects as $project) {
			$i++;
			/** @var \GIB\GradingTool\Domain\Model\Project $project */
			$project->setDataSheetContent($project->getDataSheetContentArray());
			$project->setProjectData($project->getProjectDataArray());
			$this->projectRepository->update($project);
			if ($i % 20 == 0) {
				// persist after each 20th project
				$this->persistenceManager->persistAll();
			}
		}
		// persist after the last bunch of projects
		$this->persistenceManager->persistAll();

		// add a flash message
		$message = new \TYPO3\Flow\Error\Message('All projects updated.', \TYPO3\Flow\Error\Message::SEVERITY_OK);
		$this->flashMessageContainer->addMessage($message);
		$this->redirect('settings', 'Admin');
	}

	/**
	 * Export a PDF report for a project
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function exportReportAction(\GIB\GradingTool\Domain\Model\Project $project) {

		// The processed submission
		$submission = $this->submissionService->getProcessedSubmission($project);

		if ($submission['hasError']) {
			// Don't export the Grading if is has errors
			$message = new \TYPO3\Flow\Error\Message('The Grading has errors and therefore it cannot be exported. Review and correct the Grading.', \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->flashMessageContainer->addMessage($message);
			$this->redirect('index', 'Standard');
		}

		// The flat data sheet
		$dataSheet = $this->dataSheetService->getFlatProcessedDataSheet($project);

		$pdf = new \GIB\GradingTool\Utility\TcPdf();

		// set font
		\TCPDF_FONTS::addTTFfont('resource://GIB.GradingTool/Private/Fonts/Cambria.ttf', 'TrueTypeUnicode');
		\TCPDF_FONTS::addTTFfont('resource://GIB.GradingTool/Private/Fonts/Cambria Bold.ttf', 'TrueTypeUnicode');
		\TCPDF_FONTS::addTTFfont('resource://GIB.GradingTool/Private/Fonts/Cambria Italic.ttf', 'TrueTypeUnicode');
		\TCPDF_FONTS::addTTFfont('resource://GIB.GradingTool/Private/Fonts/Cambria Bold Italic.ttf', 'TrueTypeUnicode');

		// set margins
		$pdf->SetMargins(20, 45);
		$pdf->SetHeaderMargin(20);
		$pdf->SetFooterMargin(20);

		$pdf->SetFont('Cambria', '', 10);
		$pdf->SetHeaderFont(array('Cambria', '', 10));
		$pdf->SetFooterFont(array('Cambria', '', 10));
		$pdf->setHtmlVSpace(array(
			'h1' => array(
				array(
					'h' => 0,
					'n' => 0
				),
				array(
					'h' => 0,
					'n' => 0
				)
			),
			'h2' => array(
				array(
					'h' => 0,
					'n' => 0
				),
				array(
					'h' => 0,
					'n' => 0
				)
			),
			'h3' => array(
				array(
					'h' => 0,
					'n' => 0
				),
				array(
					'h' => 1,
					'n' => 3
				)
			),
			'h6' => array(
				array(
					'h' => 0,
					'n' => 0
				),
				array(
					'h' => 0,
					'n' => 0
				)
			),
			'p' => array(
				array(
					'h' => 0,
					'n' => 0
				),
				array(
					'h' => 1,
					'n' => 2.5
				)
			),
			'ul' => array(
				array(
					'h' => 0,
					'n' => 0
				),
				array(
					'h' => 1,
					'n' => 2.5
				)
			),
		));
		$pdf->setListIndentWidth(3);

		$pdf->SetPrintHeader(TRUE);
		$pdf->SetPrintFooter(TRUE);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Global Infrastructure Basel Foundation');
		$pdf->SetTitle($project->getProjectTitle());
		$pdf->projectTitle = $project->getProjectTitle();
		$pdf->exportDate = strftime('%Y-%m-%d');

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// Must be an Illustrator 3 file
		$epsLogoResource = 'resource://GIB.GradingTool/Private/Images/logo_gib_print.eps';

		// one pixel png
		$onePixelResource = 'resource://GIB.GradingTool/Private/Images/one-pixel.png';

		// partners png
		$gibPartnersResource = 'resource://GIB.GradingTool/Private/Images/gib-partners.png';

		/*** FRONT PAGE ***/
		$pdf->addPage();
		$arguments = array(
			'dataSheet' => $dataSheet,
			'project' => $project,
			'epsLogoResource' => $epsLogoResource,
			'onePixelResource' => $onePixelResource
		);
		$pdf->writeHTML($this->pdfTemplateRenderer('Front', $arguments), TRUE, FALSE, TRUE);

		/*** PARTNERS PAGE ***/
		$pdf->addPage();
		$arguments = array(
			'gibPartnersResource' => $gibPartnersResource,
			'onePixelResource' => $onePixelResource
		);
		$pdf->writeHTML($this->pdfTemplateRenderer('Partners', $arguments), TRUE, FALSE, TRUE);

		/*** TOC PAGE IS INSERTED AT PAGE 3 ***/

		/*** DATA SHEET FRONT ***/
		$pdf->addPage();
		$pdf->SetAutoPageBreak(FALSE);
		$arguments = array(
			'onePixelResource' => $onePixelResource
		);
		$pdf->writeHTML($this->pdfTemplateRenderer('DataSheetFront', $arguments), TRUE, FALSE, TRUE);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		/*** DATA SHEET ***/
		$pdf->addPage();
		$arguments = array(
			'dataSheet' => $dataSheet,
			'project' => $project,
			'onePixelResource' => $onePixelResource
		);
		$pdf->writeHTML($this->pdfTemplateRenderer('DataSheet', $arguments), TRUE, FALSE, TRUE);

		/*** GRADING FRONT ***/
		$pdf->addPage();
		$pdf->SetAutoPageBreak(FALSE);
		$arguments = array(
			'onePixelResource' => $onePixelResource
		);
		$pdf->writeHTML($this->pdfTemplateRenderer('GradingFront', $arguments), TRUE, FALSE, TRUE);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		/*** GRADING TOOL ***/
		$pdf->addPage();
		$arguments = array(
			'submission' => $submission,
			'project' => $project,
			'scoreData' => $this->submissionService->getScoreData(),
			'onePixelResource' => $onePixelResource
		);
		$pdf->writeHTML($this->pdfTemplateRenderer('Grading', $arguments), TRUE, FALSE, TRUE);

		/*** ANALYSIS FRONT ***/
		$pdf->addPage();
		$pdf->SetAutoPageBreak(FALSE);
		$arguments = array(
			'onePixelResource' => $onePixelResource
		);
		$pdf->writeHTML($this->pdfTemplateRenderer('AnalysisFront', $arguments), TRUE, FALSE, TRUE);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		/*** ANALYSIS ***/
		$pdf->addPage();
		$radarChartFileName = $this->submissionService->getRadarImage($project);
		$lineGraphFileName = $this->submissionService->getLineGraphImage($project);
		$answerLevelGraphFileName = $this->submissionService->getAnswerLevelBarChartImage($project);
		$arguments = array(
			'radarChartFileName' => $radarChartFileName,
			'lineGraphFileName' => $lineGraphFileName,
			'answerLevelGraphFileName' => $answerLevelGraphFileName,
			'onePixelResource' => $onePixelResource,
			'submission' => $submission,
		);
		$pdf->writeHTML($this->pdfTemplateRenderer('Analysis', $arguments), TRUE, FALSE, TRUE);

		/** This was the last page */
		$pdf->lastPage();

		/*** TOC PAGE ***/
		$pdf->addTOCPage();
		$arguments = array(
			'dataSheet' => $dataSheet,
			'project' => $project,
			'onePixelResource' => $onePixelResource
		);
		$pdf->writeHTML($this->pdfTemplateRenderer('TOCBeforeTOC', $arguments), TRUE, FALSE, TRUE);

		$arguments = array(
			'onePixelResource' => $onePixelResource
		);
		$afterContent = $this->pdfTemplateRenderer('TOCAfterTOC', $arguments);

		$bookmarkTemplates = array();
		$bookmarkTemplates[0] = '<style>td.blue { color: #0f4fa2; }	td.orange { color: #f36e21; } td.red { color: #c92938; } td.black { color: #000000; } td.grey { color: #555555; }</style><table border="0" cellpadding="0" cellspacing="0"><tr><td class="#TOC_CSSCLASS#" width="12%"><strong>#TOC_CHAPTERNUMBER#</strong></td><td class="black" width="78%"><strong>#TOC_DESCRIPTION#</strong></td><td width="10%"><span style="font-weight:bold;font-size:10pt;text-align:right;" align="right">#TOC_PAGE_NUMBER#</span></td></tr></table>';
		$bookmarkTemplates[1] = '<style>td.blue { color: #0f4fa2; }	td.orange { color: #f36e21; } td.red { color: #c92938; } td.black { color: #000000; } td.grey { color: #555555; }</style><table border="0" cellpadding="0" cellspacing="0"><tr><td class="#TOC_CSSCLASS#" width="12%"><strong>#TOC_CHAPTERNUMBER#</strong></td><td class="grey" width="78%">#TOC_DESCRIPTION#</td><td width="10%"><span style="font-size:10pt;text-align:right;" align="right">#TOC_PAGE_NUMBER#</span></td></tr></table>';
		$pdf->addHTMLTOC(3, 'INDEX', $bookmarkTemplates, TRUE, 'B', array(128,0,0), $afterContent);

		$pdf->endTOCPage();

		$pdf->Output('export.pdf', 'I');
	}

	/**
	 * @param string $templateName
	 * @param array $arguments
	 * @return string
	 */
	public function pdfTemplateRenderer($templateName, $arguments) {
		$standAloneView = new \TYPO3\Fluid\View\StandaloneView();
		$standAloneView->setTemplatePathAndFilename('resource://GIB.GradingTool/Private/Templates/PdfExport/' . $templateName . '.html');
		$standAloneView->assignMultiple($arguments);
		return $standAloneView->render();
	}

	/**
	 * Export all projects as a single XML file meeting the FMPXMLRESULT grammar
	 *
	 * @param string $authToken
	 */
	public function exportProjectsAction($authToken = '') {
		if (empty($authToken) || $authToken !== $this->settings['export']['xml']['requestToken']) {
			$message = new \TYPO3\Flow\Error\Message('Permission denied. Authentication token missing or wrong.', \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->flashMessageContainer->addMessage($message);
			$this->redirect('index', 'Standard');
		}

		$projects = $this->projectRepository->findAll();

		$this->view->assignMultiple(array(
			'projects' => $projects
		));
	}

	/**
	 * Export all project managers as a single XML file meeting the FMPXMLRESULT grammar
	 *
	 * @param string $authToken
	 */
	public function exportProjectManagersAction($authToken = '') {
		if (empty($authToken) || $authToken !== $this->settings['export']['xml']['requestToken']) {
			$message = new \TYPO3\Flow\Error\Message('Permission denied. Authentication token missing or wrong.', \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->flashMessageContainer->addMessage($message);
			$this->redirect('index', 'Standard');
		}

		$projectManagers = $this->projectManagerRepository->findAll();

		$this->view->assignMultiple(array(
			'projectManagers' => $projectManagers
		));

	}

	/**
	 * Export to the Excel template using the PHPExcel library
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function exportExcelGradingAction(\GIB\GradingTool\Domain\Model\Project $project) {

		// Write Grading results
		$grading = $this->submissionService->getProcessedSubmission($project);

		if ($grading['hasError']) {
			// Don't export the Grading if is has errors
			$message = new \TYPO3\Flow\Error\Message('The Grading has errors and therefore it cannot be exported. Review and correct the Grading.', \TYPO3\Flow\Error\Message::SEVERITY_ERROR);
			$this->flashMessageContainer->addMessage($message);
			$this->redirect('index', 'Standard');
		}

		// the uploaded export template
		$templateFilePathAndFileName =  \TYPO3\Flow\Utility\Files::concatenatePaths(array($this->settings['export']['excel']['templatePath'], $this->settings['export']['excel']['templateFileName']));
		$excelReader = new \PHPExcel_Reader_Excel2007();
		$excelReader->setLoadSheetsOnly($this->settings['export']['excel']['worksheetLabel']);
		$phpExcel = $excelReader->load($templateFilePathAndFileName);
		$worksheet = $phpExcel->getSheetByName($this->settings['export']['excel']['worksheetLabel']);

		$firstSectionColumn = $this->settings['export']['excel']['firstSectionColumn'];
		// we need to subtract 1 because of https://github.com/PHPOffice/PHPExcel/issues/307
		$columnNumber = \PHPExcel_Cell::columnIndexFromString($firstSectionColumn) - 1;
		foreach ($grading['sections'] as $section) {
			$row = $this->settings['export']['excel']['sectionLabelFirstRow'];
			$column = \PHPExcel_Cell::stringFromColumnIndex($columnNumber);
			$worksheet->getCell($column . $row)->setValue($section['label']);
			foreach ($section['questions'] as $question) {
				$row++;
				if (isset($question['score'])) {
					if ($question['score'] === 'N/A') {
						// N/A is value 5 in Excel tool
						$worksheet->getCell($column . $row)->setValue('5');
					} else {
						$worksheet->getCell($column . $row)->setValue($question['score']);
					}
				}
			}
			$columnNumber++;
		}

		// Write project title
		$worksheet->getCell($this->settings['export']['excel']['projectTitleCell'])->setValue($project->getProjectTitle());

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="grading.xlsx"');
		header('Cache-Control: max-age=0');

		/** @var \PHPExcel_Writer_Excel2007 $excelWriter */
		$excelWriter = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
		$excelWriter->save('php://output');

	}

}

?>