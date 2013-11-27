<?php
namespace GIB\GradingTool\Service;


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

class SubmissionService {

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
	 * @Flow\Inject
	 * @var \TYPO3\Form\Persistence\FormPersistenceManagerInterface
	 */
	protected $formPersistenceManager;

	/**
	 * @param \GIB\GradingTool\Domain\Model\Project $project
	 * @param bool $languageOverlay
	 * @return array
	 */
	public function getProcessedSubmission(\GIB\GradingTool\Domain\Model\Project $project, $languageOverlay = FALSE) {

		$submissionContentArray = unserialize($project->getSubmissionContent());
		// we don't overlay the form here because we need to review the submission in english
		// todo overlay if needed
		$submissionFormDefinition = $this->formPersistenceManager->load($this->settings['forms']['submission']);

		$submission = array();
		$submission['errorCount'] = 0;
		$formSections = array();
		foreach ($submissionFormDefinition['renderables'] as $page) {
			// a form page
			foreach ($page['renderables'] as $section) {

				// a form section, containing the questions
				$formSections[$section['identifier']]['label'] = $section['label'];
				$formSections[$section['identifier']]['threshold'] = $section['properties']['threshold'];
				$notApplicableAnswerCount = 0;
				$questionCount = 0;
				$optOutAcceptedCount = 0;
				$weightingsSum = 0;
				foreach ($section['renderables'] as $field) {
					if ($field['type'] === 'GIB.GradingTool:Question') {
						// selected answer and their score
						$questionCount++;
						$formSections[$section['identifier']]['questions'][$field['identifier']]['label'] =  $field['label'];
						$formSections[$section['identifier']]['questions'][$field['identifier']]['weighting'] =  $field['properties']['weighting'];
						$weightingsSum = $weightingsSum + $field['properties']['weighting'];
						$formSections[$section['identifier']]['questions'][$field['identifier']]['optOut'] =  $field['properties']['optOut'];

						$key = $submissionContentArray[$field['identifier']];
						foreach ($field['properties']['options'] as $option) {
							if ($option['_key'] == $key) {
								$formSections[$section['identifier']]['questions'][$field['identifier']]['score'] =  $option['score'];
								$formSections[$section['identifier']]['questions'][$field['identifier']]['key'] = $option['_key'];
								$formSections[$section['identifier']]['questions'][$field['identifier']]['value'] = $option['_value'];
								if ($option['score'] == 0) {
									$notApplicableAnswerCount++;
									$formSections[$section['identifier']]['questions'][$field['identifier']]['score'] = 'N/A';
								}

							}
						}
					} elseif ($field['type'] === 'GIB.GradingTool:NotApplicableMultiLineText') {
						// comment for opt-out questions
						$formSections[$section['identifier']]['questions'][$field['properties']['questionIdentifier']]['comment'] = $submissionContentArray[$field['identifier']];
					} elseif ($field['type'] === 'GIB.GradingTool:OptOutAcceptedCheckbox') {
						// opt-out question was accepted (or not)
						if ($submissionContentArray[$field['identifier']] == 1) {
							$optOutAcceptedCount++;
						}
						$formSections[$section['identifier']]['questions'][$field['properties']['questionIdentifier']]['optOutAcceptedFieldIdentifier'] = $field['identifier'];
						$formSections[$section['identifier']]['questions'][$field['properties']['questionIdentifier']]['optOutAccepted'] = $submissionContentArray[$field['identifier']];
					}
				}
				$formSections[$section['identifier']]['notApplicableAnswerCount'] = $notApplicableAnswerCount;
				$formSections[$section['identifier']]['questionCount'] = $questionCount;
				$formSections[$section['identifier']]['optOutAcceptedCount'] = $optOutAcceptedCount;
				$formSections[$section['identifier']]['weightingAverage'] = $weightingsSum / $questionCount;
				$formSections[$section['identifier']]['thresholdReached'] = $questionCount - $notApplicableAnswerCount + $optOutAcceptedCount < $section['properties']['threshold'] ? FALSE : TRUE;

				if ($formSections[$section['identifier']]['thresholdReached']) {
					$formSections[$section['identifier']]['weightedScore'] = $this->calculateScoreForSection($formSections[$section['identifier']]['questions']);
				} else {
					$formSections[$section['identifier']]['weightedScore'] = FALSE;
					$submission['errorCount']++;
				}

			}

		}

		$submission['sections'] = $formSections;

		return $submission;

	}

	/**
	 * It was decided not to use weighting of scores when it was already developed. In case the product owner changes his mind again, we leave the stuff here
	 *
	 * @param $questions
	 * @return float
	 */
	public function calculateWeightedScoreForSection($questions) {
		$missingWeighting = 0;
		$questionScoreWeightingArray = array();
		$applicableQuestionCount = 0;
		foreach ($questions as $question) {
			if ($question['score'] === 'N/A') {
				$missingWeighting = $missingWeighting + $question['weighting'];
			} else {
				$applicableQuestionCount++;
				$questionScoreWeightingArray[] = array('score' => $question['score'], 'weighting' => $question['weighting']);
			}
		}

		//\TYPO3\Flow\var_dump($questionScoreWeightingArray, 'questionScWeArrayBefore');

		if ($missingWeighting !== 0) {
			// if we have missing weighting, we allocate it !EQUALLY! to the other questions
			$additionalWeightingForEachQuestion = $missingWeighting / $applicableQuestionCount;

			foreach ($questionScoreWeightingArray as $key => $questionScoreAndWeighting) {
				$questionScoreWeightingArray[$key]['weighting'] = $questionScoreWeightingArray[$key]['weighting'] + $additionalWeightingForEachQuestion;
			}

		}

		//\TYPO3\Flow\var_dump($questionScoreWeightingArray, 'questionScWeArrayAfter');


		// now that the weighting is correct (total sum of weightings is now 1), we calculate the section score
		$sectionScore = 0;

		foreach ($questionScoreWeightingArray as $questionScoreAndWeighting) {
			$sectionScore = $sectionScore + ($questionScoreAndWeighting['score'] * $questionScoreAndWeighting['weighting']);
		}

		return $sectionScore / $applicableQuestionCount;

		//\TYPO3\Flow\var_dump($sectionScore, 'sectionScore');


	}

	/**
	 * @param $questions
	 * @return float
	 */
	public function calculateScoreForSection($questions) {
		$questionScoreArray = array();
		$applicableQuestionCount = 0;
		foreach ($questions as $question) {
			if ($question['score'] !== 'N/A') {
				$applicableQuestionCount++;
				$questionScoreArray[] = array('score' => $question['score']);
			}
		}

		//\TYPO3\Flow\var_dump($questionScoreWeightingArray, 'questionScWeArrayAfter');

		$sectionScoreSum = 0;
		foreach ($questionScoreArray as $questionScore) {
			$sectionScoreSum = $sectionScoreSum + $questionScore['score'];
		}
		$sectionScoreAverage = $sectionScoreSum / $applicableQuestionCount;

		return $sectionScoreAverage;

		//\TYPO3\Flow\var_dump($sectionScore, 'sectionScore');


	}

	public function getScoreData() {
		$submissionFormDefinition = $this->formPersistenceManager->load($this->settings['forms']['submission']);

		$scoreData = array();

		foreach ($submissionFormDefinition['renderables'] as $key => $page) {
			// a form page
			foreach ($page['renderables'] as $section) {

				$scoreData[$key]['categoryName'] = $section['label'];
				$scoreData[$key]['goodScore'] = $section['properties']['goodPerformanceReferenceScore'];
				$scoreData[$key]['modestScore'] = $section['properties']['modestPerformanceReferenceScore'];
				$scoreData[$key]['currentAverageScore'] = 0;

			}

		}

		return $scoreData;
	}

}