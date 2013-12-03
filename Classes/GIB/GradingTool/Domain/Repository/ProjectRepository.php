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
		$countryIndex = 0;
		$lastUsedCountry = '';
		foreach ($projects as $project) {
			$dataSheetContent = unserialize($project->getDataSheetContent());
			$currentCountry = $dataSheetContent['country'];

			if ($currentCountry !== $lastUsedCountry && !empty($lastUsedCountry)) {
				$countryIndex++;
			}
			$lastUsedCountry = $currentCountry;
			if (isset($countries[$countryIndex]['value'])) {
				$countries[$countryIndex]['value']++;
			} else {
				$countries[$countryIndex]['value'] = 1;
			}
			$countries[$countryIndex]['id'] = $dataSheetContent['country'];
			$countries[$countryIndex]['balloonText'] = '<strong>[[title]]</strong><br /> [[value]] projects';
		}

		return $countries;
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

		// build query from constraints
		if (!empty($constraints)) {
			$query->matching(
				$query->logicalAnd($constraints)
			);
		}

		return $query->execute();

	}

}
?>