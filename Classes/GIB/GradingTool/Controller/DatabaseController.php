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

class DatabaseController extends AbstractBaseController {

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectRepository
	 * @Flow\Inject
	 */
	protected $projectRepository;

	/**
	 * @var \GIB\GradingTool\Service\CldrService
	 * @Flow\Inject
	 */
	protected $cldrService;

	/**
	 * @return void
	 */
	public function indexAction() {
		$dataSheetFormDefinition = $this->formPersistenceManager->load($this->settings['forms']['dataSheet']);
		$categories = $dataSheetFormDefinition['renderables'][0]['renderables'][3]['properties']['options'];

		$this->view->assignMultiple(array(
			'categories' => $categories,
		));
	}

	/**
	 * @param array $demand
	 * @return string
	 */
	public function getMapDataAction($demand = NULL) {
		$countries = $this->projectRepository->findCountriesWithProjects($demand);
		return json_encode($countries);
	}

	/**
	 * @param array $demand
	 */
	public function listAction($demand = NULL) {

		if (is_array($demand)) {
			$projects = $this->projectRepository->findByDemand($demand);
		} else {
			$projects = $this->projectRepository->findAll();
		}

		// return not only the country code, but also the country name for filter display
		if (is_array($demand) && isset($demand['filter']['country'])) {
			$isoCode = $demand['filter']['country'];
			unset($demand['filter']['country']);
			$demand['filter']['country']['name'] = $this->cldrService->getCountryNameForIsoCode($isoCode);
			$demand['filter']['country']['isoCode'] = $isoCode;
		}

		$this->view->assignMultiple(array(
			'projects' => $projects,
			'demand' => $demand,
		));
	}

}

?>