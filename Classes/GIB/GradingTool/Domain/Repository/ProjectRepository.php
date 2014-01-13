<?php
namespace GIB\GradingTool\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class ProjectRepository extends Repository {

	protected $defaultOrderings = array(
		'created' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING,
		'projectTitle' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING
	);

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
	 * Find all countries and return an array with their ISO code and the number of projects
	 *
	 * @param array $demand
	 * @return array
	 */
	public function findCountriesWithProjects($demand) {

		if (is_array($demand)) {
			/** @var \TYPO3\Flow\Persistence\QueryResultInterface $projects */
			$projects = $this->findByDemand($demand);
		} else {
			/** @var \TYPO3\Flow\Persistence\QueryResultInterface $projects */
			$projects = $this->createQuery()->execute();
		}

		$countries = array();
		foreach ($projects as $project) {
			$currentCountry = $project->getDataSheetContentArray()['country'];

			if (!isset($countries[$currentCountry]['value'])) {
				$countries[$currentCountry]['value'] = 1;
			} else {
				$countries[$currentCountry]['value']++;
			}
			$countries[$currentCountry]['id'] = $currentCountry;
			$countries[$currentCountry]['balloonText'] = '<strong>[[title]]</strong><br /> [[value]] projects';
		}

		return array_values($countries);
	}

	/**
	 * Find all projects by demand
	 *
	 * @param array $demand
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface
	 */
	public function findByDemand($demand) {

		//\TYPO3\Flow\var_dump($demand);

		$query = $this->createQuery();
		$constraints = array();

		// Filter by country
		if (isset($demand['filter']['country']) && !empty($demand['filter']['country'])) {
			$constraints[] = $query->like('countryCode', $demand['filter']['country'], FALSE);
		}

		// Filter by categories
		if (isset($demand['filter']['categories']) && !empty($demand['filter']['categories'])) {
			$categories = array();
			foreach($demand['filter']['categories'] as $category) {
				$categories[] = $query->like('categories', '%' . $category . '%');
			}
			$constraints[] = $query->logicalOr(
				$categories
			);
		}

		// Filter by stages
		if (isset($demand['filter']['stages']) && !empty($demand['filter']['stages'])) {
			$stages = array();
			foreach($demand['filter']['stages'] as $stage) {
				$stages[] = $query->like('stage', '%' . strtoupper($stage) . '%');
			}
			$constraints[] = $query->logicalOr(
				$stages
			);
		}

		// Filter by budget brackets
		if (isset($demand['filter']['budgetBrackets']) && !empty($demand['filter']['budgetBrackets'])) {
			$budgetBrackets = array();
			$budgetBracketSettings = $this->settings['projectDatabase']['filters']['budget']['brackets'];
			foreach($demand['filter']['budgetBrackets'] as $bracket) {
				$budgetBrackets[] = $query->logicalAnd(
					$query->greaterThanOrEqual('cost', $budgetBracketSettings[$bracket]['minimum']),
					$query->lessThanOrEqual('cost', $budgetBracketSettings[$bracket]['maximum'])
				);
			}
			$constraints[] = $query->logicalOr(
				$budgetBrackets
			);
		}

		// Filter by required investment brackets
		if (isset($demand['filter']['requiredInvestmentBrackets']) && !empty($demand['filter']['requiredInvestmentBrackets'])) {
			$requiredInvestmentBrackets = array();
			$requiredInvestmentBracketSettings = $this->settings['projectDatabase']['filters']['requiredInvestment']['brackets'];
			foreach($demand['filter']['requiredInvestmentBrackets'] as $requiredInvestmentBracket) {
				\TYPO3\Flow\var_dump($requiredInvestmentBracket, 'reqInBracket');
				$minValue = (float)$requiredInvestmentBracketSettings[(int)$requiredInvestmentBracket]['minimum'];
				$maxValue = (float)$requiredInvestmentBracketSettings[(int)$requiredInvestmentBracket]['maximum'];
				$requiredInvestmentBrackets[] = $query->logicalAnd(
					$query->greaterThanOrEqual('requiredInvestment', $minValue),
					$query->lessThanOrEqual('requiredInvestment', $maxValue)
				);
				//\TYPO3\Flow\var_dump($requiredInvestmentBracketSettings, 'reqInBracketSettings');
			}
			$constraints[] = $query->logicalOr(
				$requiredInvestmentBrackets
			);
		}

		// build query from constraints
		if (!empty($constraints)) {
			$query->matching(
				$query->logicalAnd($constraints)
			);
		}

		// apply sorting
		if (isset($demand['sorting']) && !empty($demand['sorting'])) {
			$validSortingProperties = array('cost');
			if (in_array($demand['sorting']['property'], $validSortingProperties)) {
				$validOrderings = array('ascending', 'descending');
				if (in_array($demand['sorting']['order'], $validOrderings)) {
					$sortingOrder = \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING;
					if ($demand['sorting']['order'] === 'ascending') {
						$sortingOrder = \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING;
					}
					$query->setOrderings(array(
						$demand['sorting']['property'] => $sortingOrder
					));
				}
			}
		}

		return $query->execute();

	}

}
?>