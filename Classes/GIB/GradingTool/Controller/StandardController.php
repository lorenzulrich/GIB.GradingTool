<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;


use Tokk\pChartBundle\pData;
use Tokk\pChartBundle\pImage;
use Tokk\pChartBundle\pRadar;

class StandardController extends \TYPO3\Flow\Mvc\Controller\ActionController {

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
				$this->forward('dashboard');
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

	public function renderChartAction() {

		/* Create and populate the pData object */
		$MyData = new pData();

		// Data for good performance
		$MyData->addPoints(
			array(4,4,4,4,4,4,4,4,4,4),
			"GoodPerformance"
		);
		$MyData->setSerieDescription("GoodPerformance","Application A");
		$MyData->setPalette("GoodPerformance",array("R"=>255,"G"=>0,"B"=>0));

		// Data for modest performance
		$MyData->addPoints(
			array(2,1.7,3,1.25,3.2,2.8,2,1.7,1.5,3.4),
			"Modest"
		);
		$MyData->setSerieDescription("Modest","Application A");
		$MyData->setPalette("Modest",array("R"=>0,"G"=>255,"B"=>0));

		// Actual performance
		$MyData->addPoints(
			array(3.29, 4, 3, 3, 2.86, 3, 2.83, 4, 3.65, 3.5),
			"Score"
		);
		$MyData->setSerieDescription("Score","Application A");
		$MyData->setPalette("Score",array("R"=>123,"G"=>196,"B"=>22));



		/* Define the absissa serie */
		$MyData->addPoints(
			array('Customer Focus', 'Results Orientation', 'Poverty Responsiveness', 'Power-Balanced Partnership',
				'Shared Incentives', 'Sound Financial Mechanisms', 'Proactive Risk Management', 'Resource Protection',
				'Accountability', 'Transparency'),
			"Categories"
		);
		$MyData->setAbscissa("Categories");

		/* Create the pChart object */
		$myPicture = new pImage(600,600, $MyData);

		/* Set the default font properties */
		$fontForgottePath = FLOW_PATH_PACKAGES . 'Application/GIB.GradingTool/Resources/Private/Fonts/verdana.ttf';
		$myPicture->setFontProperties(array("FontName"=>$fontForgottePath,"FontSize"=>11,"R"=>0,"G"=>0,"B"=>0));

		/* Enable shadow computing */
		//$myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

		/* Create the pRadar object */
		$SplitChart = new pRadar();

		/* Draw a radar chart */
		$myPicture->setGraphArea(20,20,580,580);
		$Options = array(
			"DrawPoly"=>TRUE,
			"WriteValues"=>TRUE,
			"ValueFontSize"=>10,
			"Layout"=>RADAR_LAYOUT_CIRCLE,
			"BackgroundGradient"=>array(
				"StartR"=>255,
				"StartG"=>255,
				"StartB"=>255,
				"StartAlpha"=>100,
				"EndR"=>207,
				"EndG"=>227,
				"EndB"=>125,
				"EndAlpha"=>50
			)
		);
		$SplitChart->drawRadar($myPicture, $MyData, $Options);

		/* Render the picture (choose the best way) */
		$myPicture->autoOutput(FLOW_PATH_DATA . '/radar.png');

	}
}

?>