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

		$budgetBrackets = $this->settings['projectDatabase']['filters']['budget']['brackets'];
		$stages = $this->settings['projectDatabase']['filters']['stage'];

		$this->view->assignMultiple(array(
			'categories' => $categories,
			'budgetBrackets' => $budgetBrackets,
			'stages' => $stages,
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

		// return not only the budget bracket keys, but also the minimum and maximum value
		if (is_array($demand) && is_array($demand['filter']['budgetBrackets'])) {
			$bracketsRequested = $demand['filter']['budgetBrackets'];
			unset($demand['filter']['budgetBrackets']);
			$budgetBracketSettings = $this->settings['projectDatabase']['filters']['budget']['brackets'];
			foreach ($bracketsRequested as $bracket) {
				$demand['filter']['budgetBrackets'][$bracket]['key'] = $bracket;
				$demand['filter']['budgetBrackets'][$bracket]['minimum'] = $budgetBracketSettings[$bracket]['minimum'];
				$demand['filter']['budgetBrackets'][$bracket]['maximum'] = $budgetBracketSettings[$bracket]['maximum'];
			}
		}

		$this->view->assignMultiple(array(
			'projects' => $projects,
			'demand' => $demand,
		));
	}

}

?>