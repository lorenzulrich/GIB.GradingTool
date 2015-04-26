<?php
namespace GIB\GradingTool\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Utility\MediaTypes;
use TYPO3\Media\Domain\Model\Image;

/**
 * @Flow\Scope("singleton")
 */
class ProjectCommandController extends CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var \GIB\GradingTool\Domain\Repository\ProjectRepository
	 * @Flow\Inject
	 */
	protected $projectRepository;

	/**
	 * Migrate Primary Sector
	 *
	 * Migrates the dataSheet field "singleselectdropdown1" in the serialized dataSheetContent field of a project.
	 * Changes field name to "primarySector" and field values to speaking values.
	 *
	 * @param boolean $simulate If set, this command will only tell what it would do instead of doing it right away
	 * @return void
	 */
	public function migratePrimarySectorCommand($simulate = FALSE) {
		$affectedProjects = $this->projectRepository->findByDataSheetFormIdentifier('dataSheetFormV2');

		foreach ($affectedProjects as $project) {
			/** @var $project \GIB\GradingTool\Domain\Model\Project */
			$dataSheetContentArray = $project->getDataSheetContentArray();
			if (array_key_exists('singleselectdropdown1', $dataSheetContentArray)) {
				$value = $dataSheetContentArray['singleselectdropdown1'];
				$newValue = '';
				switch ($value) {
					case 'transport':
						$newValue = 'Transportation (Railway, Road, BRT, etc.)';
						break;
					case 'energy':
						$newValue = 'Energy generation and distribution';
						break;
					case 'water':
						$newValue = 'Water and waste processing';
						break;
					case 'social':
						$newValue = 'Social Infrastructure (schools, hospitals, state housing)';
						break;
					case 'telecom':
						$newValue = 'ICT';
						break;
					default:
						break;
				}
				unset($dataSheetContentArray['singleselectdropdown1']);
				$dataSheetContentArray['primarySector'] = $newValue;
				$project->setDataSheetContent($dataSheetContentArray);
				$status = '[SIMULATE] ';
				if (!$simulate) {
					$this->projectRepository->update($project);
					$this->persistenceManager->persistAll();
					$status = '';
				}
				$message = $status . 'Project "' . $project->getProjectTitle() . '": Changed singleselectdropdown "' . $value . '" to primarySector "' . $newValue . '".';
				$this->outputLine($message);
			}
		}

		$this->quit();

	}

}
