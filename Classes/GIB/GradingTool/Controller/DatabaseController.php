<?php
namespace GIB\GradingTool\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use GIB\GradingTool\Utility\Arrays;

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
	 * Initialize the Project Finder
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $openProject
	 * @return void
	 */
	public function indexAction($openProject = NULL) {
		$dataSheetFormDefinition = $this->formPersistenceManager->load($this->settings['forms']['dataSheet']);
		$categories = $dataSheetFormDefinition['renderables'][0]['renderables'][3]['properties']['options'];

		$budgetBrackets = $this->settings['projectDatabase']['filters']['budget']['brackets'];
		$requiredInvestmentBrackets = $this->settings['projectDatabase']['filters']['requiredInvestment']['brackets'];
		$stages = $this->settings['projectDatabase']['filters']['stage'];

		$this->view->assignMultiple(array(
			'categories' => $categories,
			'budgetBrackets' => $budgetBrackets,
			'requiredInvestmentBrackets' => $requiredInvestmentBrackets,
			'stages' => $stages,
		));

		if ($openProject !== NULL) {
			// the project finder was requested with the uuid of a project --> display project details in modal box
			$showProjectUri = $this->uriBuilder->setCreateAbsoluteUri(TRUE)->uriFor('show', array('project' => $openProject));
			$this->view->assign('openProject', $showProjectUri);
		}

	}

	/**
	 * Open a project in the Project Finder
	 *
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @return void
	 */
	public function openAction($project) {
		$this->forward('index', 'Database', 'GIB.GradingTool', array('openProject' => $project));
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
			$demand = Arrays::removeEmptyElementsRecursively($demand);
			$projects = $this->projectRepository->findByDemand($demand);

			//\TYPO3\Flow\var_dump($demand, 'demand');

			// return not only the country code, but also the country name for filter display
			if (isset($demand['filter']['country'])) {
				$isoCode = $demand['filter']['country'];
				unset($demand['filter']['country']);
				$demand['filter']['country']['name'] = $this->cldrService->getCountryNameForIsoCode($isoCode);
				$demand['filter']['country']['isoCode'] = $isoCode;
			}

			// return not only the budget bracket keys, but also the minimum and maximum value
			if (isset($demand['filter']['budgetBrackets']) && is_array($demand['filter']['budgetBrackets'])) {
				$bracketsRequested = $demand['filter']['budgetBrackets'];
				unset($demand['filter']['budgetBrackets']);
				$bracketSettings = $this->settings['projectDatabase']['filters']['budget']['brackets'];
				foreach ($bracketsRequested as $bracket) {
					$demand['filter']['budgetBrackets'][$bracket]['key'] = $bracket;
					$demand['filter']['budgetBrackets'][$bracket]['minimum'] = $bracketSettings[$bracket]['minimum'];
					$demand['filter']['budgetBrackets'][$bracket]['maximum'] = $bracketSettings[$bracket]['maximum'];
				}
			}

			// return not only the required investment bracket keys, but also the minimum and maximum value
			if (isset($demand['filter']['requiredInvestmentBrackets']) && is_array($demand['filter']['requiredInvestmentBrackets'])) {
				$bracketsRequested = $demand['filter']['requiredInvestmentBrackets'];
				unset($demand['filter']['requiredInvestmentBrackets']);
				$bracketSettings = $this->settings['projectDatabase']['filters']['budget']['brackets'];
				foreach ($bracketsRequested as $bracket) {
					$demand['filter']['requiredInvestmentBrackets'][$bracket]['key'] = $bracket;
					$demand['filter']['requiredInvestmentBrackets'][$bracket]['minimum'] = $bracketSettings[$bracket]['minimum'];
					$demand['filter']['requiredInvestmentBrackets'][$bracket]['maximum'] = $bracketSettings[$bracket]['maximum'];
				}
			}


		} else {
			$projects = $this->projectRepository->findAll();
		}

		$this->view->assignMultiple(array(
			'projects' => $projects,
			'demand' => $demand
		));
	}

	/**
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 */
	public function showAction($project) {
		$this->view->assign('project', $project);
	}

}

?>