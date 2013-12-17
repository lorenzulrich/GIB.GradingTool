<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class StandardController extends AbstractBaseController {

	/**
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 * @Flow\Inject
	 */
	protected $authenticationManager;

	/**
	 * @return void
	 */
	public function indexAction() {

		// check is a user is authenticated
		if ($this->authenticationManager->isAuthenticated()) {

			if ($this->authenticationManager->getSecurityContext()->hasRole('GIB.GradingTool:ProjectManager')) {
				$this->forward('dashboard', 'Standard');
			}

			if ($this->authenticationManager->getSecurityContext()->hasRole('GIB.GradingTool:Administrator')) {
				$this->forward('index', 'Admin');
			}

		}

	}

	/**
	 * Dashboard for project manager
	 */
	public function dashboardAction() {
		/** @var \GIB\GradingTool\Domain\Model\ProjectManager $projectManager */
		$projectManager = $this->authenticationManager->getSecurityContext()->getParty();

		$this->view->assignMultiple(array(
			'projects' => $projectManager->getProjects(),
		));
	}

	/**
	 * @return void
	 */
	public function loginAction() {
	}

    public function exportAction() {
		$this->controllerContext->getResponse()->setHeader('Content-Type', 'application/pdf');

		$htmlFromFluid = $this->view->render();

		$pdf = new \TYPO3\TcPdf\Pdf('A4', 'mm', 'portrait', TRUE, 'UTF-8', TRUE);

		//$pdf->SetFont('Helvetica', 'normal',  '12');
		$pdf->AddPage();


		$pdf->writeHTML($htmlFromFluid, true, false, false, false, '');

		return $pdf->Output('', 'S');
    }

}

?>