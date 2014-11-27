<?php
namespace GIB\GradingTool\Service;


use GIB\GradingTool\Domain\Model\Project;
use TYPO3\Flow\Annotations as Flow;
use Tokk\pChartBundle\pData;
use Tokk\pChartBundle\pImage;

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
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Utility\Environment
	 */
	protected $environmentUtility;

	/**
	 * @param Project $project
	 * @param bool $languageOverlay
	 * @return array
	 */
	public function getProcessedSubmission(Project $project, $languageOverlay = FALSE) {

		$submissionContentArray = unserialize($project->getSubmissionContent());
		// we don't overlay the form here because we need to review the submission in english
		// todo overlay if needed
		$submissionFormDefinition = $this->formPersistenceManager->load($project->getSubmissionFormIdentifier());

		$submission = array();
		$submission['hasError'] = FALSE;
		$formSections = array();
		foreach ($submissionFormDefinition['renderables'] as $page) {
			// a form page
			foreach ($page['renderables'] as $section) {

				// a form section, containing the questions
				$formSections[$section['identifier']]['label'] = $section['label'];
				$formSections[$section['identifier']]['naAcceptanceLevel'] = $section['properties']['naAcceptanceLevel'];
				$notApplicableAnswerCount = 0;
				$questionCount = 0;
				$optOutAcceptedCount = 0;
				$weightingsSum = 0;
				$answeredQuestionCount = 0;
				foreach ($section['renderables'] as $field) {
					if ($field['type'] === 'GIB.GradingTool:Question') {
						// selected answer and their score
						$questionCount++;
						$formSections[$section['identifier']]['questions'][$field['identifier']]['label'] =  $field['label'];
						$formSections[$section['identifier']]['questions'][$field['identifier']]['weighting'] =  $field['properties']['weighting'];
						$weightingsSum = $weightingsSum + $field['properties']['weighting'];
						$formSections[$section['identifier']]['questions'][$field['identifier']]['optOut'] =  $field['properties']['optOut'];
						if (isset($submissionContentArray[$field['identifier']])) {
							$key = $submissionContentArray[$field['identifier']];
							foreach ($field['properties']['options'] as $option) {
								if ($option['_key'] == $key) {
									$formSections[$section['identifier']]['questions'][$field['identifier']]['identifier'] =  $field['identifier'];
									$formSections[$section['identifier']]['questions'][$field['identifier']]['score'] =  $option['score'];
									$formSections[$section['identifier']]['questions'][$field['identifier']]['key'] = $option['_key'];
									$formSections[$section['identifier']]['questions'][$field['identifier']]['value'] = $option['_value'];
									$formSections[$section['identifier']]['questions'][$field['identifier']]['bestPractiseText'] = (int)$option['score'] === 4 ? $field['properties']['abstract'] : NULL;
									$formSections[$section['identifier']]['questions'][$field['identifier']]['lowPerformanceText'] = (int)$option['score'] === 1 ? $field['properties']['abstract'] : NULL;
									if ($option['score'] == 0) {
										$notApplicableAnswerCount++;
										$formSections[$section['identifier']]['questions'][$field['identifier']]['score'] = 'N/A';
									}
								}
							}
							$answeredQuestionCount++;
						} else {
							// if a question was not answered, the submission is invalid
							$submission['hasError'] = TRUE;
						}
					} elseif ($field['type'] === 'GIB.GradingTool:NotApplicableMultiLineText') {
						if (isset($submissionContentArray[$field['identifier']]) && !empty($submissionContentArray[$field['identifier']])) {
							// comment for opt-out questions
							$formSections[$section['identifier']]['questions'][$field['properties']['questionIdentifier']]['comment'] = $submissionContentArray[$field['identifier']];
						}
					} elseif ($field['type'] === 'GIB.GradingTool:OptOutAcceptedCheckbox') {
						$formSections[$section['identifier']]['questions'][$field['properties']['questionIdentifier']]['optOutAcceptedFieldIdentifier'] = $field['identifier'];
						if (isset($submissionContentArray[$field['identifier']])) {
							// opt-out question was accepted (or not)
							if ($submissionContentArray[$field['identifier']] == 1) {
								$optOutAcceptedCount++;
							}
							$formSections[$section['identifier']]['questions'][$field['properties']['questionIdentifier']]['optOutAccepted'] = $submissionContentArray[$field['identifier']];
						}
					}
				}
				$formSections[$section['identifier']]['identifier'] = $section['identifier'];
				$formSections[$section['identifier']]['notApplicableAnswerCount'] = $notApplicableAnswerCount;
				$formSections[$section['identifier']]['questionCount'] = $questionCount;
				$formSections[$section['identifier']]['optOutAcceptedCount'] = $optOutAcceptedCount;
				$formSections[$section['identifier']]['weightingAverage'] = $weightingsSum / $questionCount;

				// how many questions must be answered based on the N/A acceptance level
				$formSections[$section['identifier']]['threshold'] = ceil($questionCount - ($questionCount * $formSections[$section['identifier']]['naAcceptanceLevel']));

				$neededAnswerCount = $questionCount - $optOutAcceptedCount;
				$answeredQuestionCountAfterOptOut = $answeredQuestionCount - $notApplicableAnswerCount + $optOutAcceptedCount;
				$answeredQuestionRatio = $answeredQuestionCountAfterOptOut / $neededAnswerCount;

				// float: how many question were answered (accepted opt-out questions don't count)
				$formSections[$section['identifier']]['answeredQuestionRatio'] = $answeredQuestionRatio;

				// float: how many questions need to be answered
				$formSections[$section['identifier']]['thresholdRatio'] = 1 - $formSections[$section['identifier']]['naAcceptanceLevel'];

				// TRUE if the threshold was reached
				$formSections[$section['identifier']]['thresholdReached'] = $answeredQuestionRatio < (1 - $formSections[$section['identifier']]['naAcceptanceLevel']) ? FALSE : TRUE;

				if ($formSections[$section['identifier']]['thresholdReached']) {
					$formSections[$section['identifier']]['weightedScore'] = $this->calculateScoreForSection($formSections[$section['identifier']]['questions']);
				} else {
					$formSections[$section['identifier']]['weightedScore'] = FALSE;
					$submission['hasError'] = TRUE;
				}

			}

		}
		$submission['sections'] = $formSections;


		$sectionCount = 0;
		$scoreSum = 0;
		foreach ($formSections as $section) {
			$sectionCount++;
			$scoreSum = $scoreSum + $section['weightedScore'];
		}
		$submission['mean'] = $scoreSum / $sectionCount;

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
			if (isset($question['score']) && $question['score'] !== 'N/A') {
				$applicableQuestionCount++;
				$questionScoreArray[] = array('score' => $question['score']);
			}
		}

		//\TYPO3\Flow\var_dump($questionScoreWeightingArray, 'questionScWeArrayAfter');

		// worst score=1
		$sectionScoreAverage = 1;
		if ($applicableQuestionCount > 0) {

			$sectionScoreSum = 0;
			foreach ($questionScoreArray as $questionScore) {
				$sectionScoreSum = $sectionScoreSum + $questionScore['score'];
			}
			$sectionScoreAverage = $sectionScoreSum / $applicableQuestionCount;

		}

		return $sectionScoreAverage;

		//\TYPO3\Flow\var_dump($sectionScore, 'sectionScore');

	}

	/**
	 * Get score basis data
	 *
	 * @param Project $project
	 * @return array
	 */
	public function getScoreData(Project $project = NULL) {
		$form = $project instanceof Project ? $project->getSubmissionFormIdentifier() : $this->settings['forms']['submission']['default'];
		$submissionFormDefinition = $this->formPersistenceManager->load($form);

		$scoreData = array();

		foreach ($submissionFormDefinition['renderables'] as $key => $page) {
			// a form page
			foreach ($page['renderables'] as $section) {

				$scoreData[$key]['categoryName'] = $section['label'];
				$scoreData[$key]['goodScore'] = $section['properties']['goodPerformanceReferenceScore'];
				$scoreData[$key]['modestScore'] = $section['properties']['modestPerformanceReferenceScore'];
				$scoreData[$key]['modestScore'] = $section['properties']['modestPerformanceReferenceScore'];
				// todo average score
				$scoreData[$key]['currentAverageScore'] = 0;

			}

		}

		return $scoreData;
	}

	/**
	 * Get the score data of a project for pChart usage
	 *
	 * @param $project Project
	 * @return pData
	 */
	public function getScoreDataForGraph($project) {
		/* Create and populate the pData object */
		$data = new pData();

		// get and process the basis score data
		$basisScoreData = $this->getScoreData($project);
		$goodScoreData = array();
		$modestScoreData = array();
		$axisLabels = array();
		foreach ($basisScoreData as $category) {
			$goodScoreData[] = $category['goodScore'];
			$modestScoreData[] = $category['modestScore'];
			$axisLabels[] = $category['categoryName'];
		}

		// get the project score
		$scoreData = $this->getProcessedSubmission($project);
		$projectScoreData = array();
		foreach ($scoreData['sections'] as $section) {
			$projectScoreData[] = number_format($section['weightedScore'], 2, '.', '\'');
		}

		// Data for good performance
		$data->addPoints(
			$goodScoreData,
			'GoodPerformance'
		);
		$data->setSerieDescription('GoodPerformance', 'Good Performance');
		$data->setPalette('GoodPerformance', array('R' => 31, 'G' => 119, 'B' => 180));

		// Data for modest performance
		$data->addPoints(
			$modestScoreData,
			'ModestPerformance'
		);
		$data->setSerieDescription('ModestPerformance', 'Modest Performance');
		$data->setPalette('ModestPerformance', array('R' => 249, 'G' => 172, 'B'=>44));

		// Actual performance
		$data->addPoints(
			$projectScoreData,
			'Score'
		);
		$data->setAxisXY(1);
		$data->setSerieDescription('Score', 'Project Performance');
		$data->setPalette('Score', array('R' => 255, 'G' => 127, 'B' => 14, 'Weight' => 100));
		$data->setSerieWeight('Score', 5);

		/* Define the abscissa serie */
		$data->addPoints(
			$axisLabels,
			'Categories'
		);
		$data->setAbscissa('Categories');

		return $data;
	}

	/**
	 * Render a radar from the project score
	 *
	 * @param $project Project
	 * @return string The filename of the radar file
	 */
	public function getRadarImage($project) {

		/** @var pData $data */
		$data = $this->getScoreDataForGraph($project);

		/* Create the pChart object */
		$image = new pImage(1980,1300, $data);

		/* Set the default font properties */
		$fontPath = FLOW_PATH_PACKAGES . 'Application/GIB.GradingTool/Resources/Private/Fonts/Cambria.ttf';
		$image->setFontProperties(array('FontName'=>$fontPath, 'FontSize'=>20, 'R'=>0, 'G'=>0, 'B'=>0));

		/* Create the pRadar object */
		$splitChart = new \GIB\GradingTool\Utility\pRadar();

		/* Draw a radar chart */
		$image->setGraphArea(300, 20, 1320, 1180);
		$radarOptions = array(
			'DrawPoly' => TRUE,
			'WriteValues' => TRUE,
			'LabelPos' => GIB_RADAR_LABELS_HORIZONTAL,
			'ValueFontSize' => 14,
			'DrawBackground' => FALSE,
			'SegmentHeight' => 1,
			'Segments' => 3,
			'AxisRotation' => -90,
			'FixedMax' => 4,
			'FixedMin' => 1,
			'BackgroundAlpha' => 0,
		);
		$splitChart->drawRadar($image, $data, $radarOptions);

		$legendOptions = array(
			'Style' => LEGEND_BOX,
			'Mode' => LEGEND_VERTICAL,
			'FontSize' => 30,
			'R' => 255,
			'G' => 255,
			'B' => 255,
			'IconAreaWidth' => 50,
			'IconAreaHeight' => 50,
			'BoxWidth' => 25,
			'BoxHeight' => 25,
		);
		$image->drawLegend(1500, 525, $legendOptions);

		$temporaryRadarFile = tempnam($this->environmentUtility->getPathToTemporaryDirectory(), 'radarchart.png');
		$image->render($temporaryRadarFile);

		return $temporaryRadarFile;

	}

	/**
	 * Render a line graph from the project score
	 *
	 * @param $project Project
	 * @return string The filename of the file
	 */
	public function getLineGraphImage($project) {

		/** @var pData $data */
		$data = $this->getScoreDataForGraph($project);

		/* Create the pChart object */
		$image = new pImage(1800, 1000, $data);

		/* Set the default font properties */
		$fontPath = FLOW_PATH_PACKAGES . 'Application/GIB.GradingTool/Resources/Private/Fonts/Cambria.ttf';
		$image->setFontProperties(array(
			'FontName' => $fontPath,
			'FontSize' => 20,
			'R' => 0,
			'G' => 0,
			'B' => 0
		));

		/* Create the chart */
		$image->setGraphArea(60, 60, 1740, 440);
		$scaleSettings = array(
			'XMargin' => 20,
			'YMargin' => 0,
			'Factors' => array(1),
			'LabelRotation' => 70,
			'Floating' => TRUE,
			'DrawSubTicks' => TRUE,
			'CycleBackground' => TRUE,
			'Mode' => SCALE_MODE_MANUAL,
			'ManualScale' => array(
				0 => array(
					'Min' => 1,
					'Max' => 4
				)
			),
		);
		$image->drawScale($scaleSettings);
		$image->setShadow(TRUE, array('X' => 1,'Y' => 1,'R' => 0,'G' => 0,'B' => 0,'Alpha' => 10));

		/* Create the area chart for the basis data */
		$data->setSerieDrawable('Score', FALSE);
		$data->setSerieDrawable('ModestPerformance', TRUE);
		$data->setSerieDrawable('GoodPerformance', TRUE);
		$image->drawAreaChart();

		/* Create the line chart for the project score */
		$data->setSerieDrawable('Score', TRUE);
		$data->setSerieDrawable('GoodPerformance', FALSE);
		$data->setSerieDrawable('ModestPerformance', FALSE);
		$image->drawLineChart(array('DisplayValues'=>TRUE, 'DisplayOffset' => 25, 'DisplayColor' => DISPLAY_AUTO));
		$image->drawPlotChart(array('PlotBorder'=>TRUE, 'PlotSize'=>10, 'BorderSize'=>1, 'Surrounding' => -60, 'BorderAlpha' => 80));
		$image->setShadow(FALSE);

		/* Create a legend */
		$data->setSerieDrawable('GoodPerformance', TRUE);
		$data->setSerieDrawable('ModestPerformance', TRUE);
		$legendOptions = array(
			'Style' => LEGEND_BOX,
			'Mode' => LEGEND_HORIZONTAL,
			'FontSize' => 30,
			'R' => 255,
			'G' => 255,
			'B' => 255,
			'IconAreaWidth' => 50,
			'IconAreaHeight' => 50,
			'BoxWidth' => 25,
			'BoxHeight' => 25,
		);
		$image->drawLegend(0, 900, $legendOptions);

		$temporaryLineGraphFile = tempnam($this->environmentUtility->getPathToTemporaryDirectory(), 'linegraph.png');
		$image->render($temporaryLineGraphFile);


		return $temporaryLineGraphFile;

	}

	/**
	 * Render a answer level bar chart from the project score
	 *
	 * @param $project Project
	 * @return string The filename of the file
	 */
	public function getAnswerLevelBarChartImage($project) {

		/* Create and populate the pData object */
		$data = new pData();

		// get and process the basis score data
		$basisScoreData = $this->getScoreData($project);
		$axisLabels = array();
		foreach ($basisScoreData as $category) {
			$axisLabels[] = $category['categoryName'];
		}

		// get the project score
		$scoreData = $this->getProcessedSubmission($project);
		$projectScoreData = array();
		$answeredQuestionRatios = array();
		$thresholdRatios = array();
		foreach ($scoreData['sections'] as $section) {
			$projectScoreData[] = number_format($section['weightedScore'], 2, '.', '\'');
			$answeredQuestionRatios[] = number_format($section['answeredQuestionRatio'] * 100, 0);
			$thresholdRatios[] = number_format($section['thresholdRatio'] * 100, 0);
		}

		// Data for good performance
		$data->addPoints(
			$answeredQuestionRatios,
			'answeredQuestionRatio'
		);
		$data->setSerieDescription('answeredQuestionRatio', 'Answer Level');
		$data->setPalette('answeredQuestionRatio', array('R' => 31, 'G' => 119, 'B' => 180));

		// Data for modest performance
		$data->addPoints(
			$thresholdRatios,
			'thresholdRatio'
		);
		$data->setSerieDescription('thresholdRatio', 'Threshold');
		$data->setPalette('thresholdRatio', array('R' => 215, 'G' => 32, 'B' => 49));
		$data->setSerieWeight('thresholdRatio', 4);

		/* Define the absissa serie */
		$data->addPoints(
			$axisLabels,
			'Categories'
		);
		$data->setAbscissa('Categories');
		$data->setAxisUnit(0, '%');

		/* Create the pChart object */
		$image = new pImage(1800, 1000, $data);

		/* Set the default font properties */
		$fontPath = FLOW_PATH_PACKAGES . 'Application/GIB.GradingTool/Resources/Private/Fonts/Cambria.ttf';
		$image->setFontProperties(array(
			'FontName' => $fontPath,
			'FontSize' => 20,
			'R' => 0,
			'G' => 0,
			'B' => 0
		));

		/* Create the chart */
		$image->setGraphArea(80, 60, 1720, 540);
		$scaleSettings = array(
			'XMargin' => 20,
			'YMargin' => 0,
			'Factors' => array(1),
			'LabelRotation' => 70,
			'Floating' => TRUE,
			'DrawSubTicks' => TRUE,
			'CycleBackground' => TRUE,
			'Mode' => SCALE_MODE_MANUAL,
			'ManualScale' => array(
				0 => array(
					'Min' => 0,
					'Max' => 100
				)
			),
		);
		$image->drawScale($scaleSettings);
		$image->setShadow(TRUE, array('X' => 1,'Y' => 1,'R' => 0,'G' => 0,'B' => 0,'Alpha' => 10));

		/* Create the bar chart for the answered question ratio */
		$data->setSerieDrawable('answeredQuestionRatio', TRUE);
		$data->setSerieDrawable('thresholdRatio', FALSE);
		$image->drawBarChart(array('Interleave' => 2));

		/* Create the line chart for the threshold ratio */
		$data->setSerieDrawable('answeredQuestionRatio', FALSE);
		$data->setSerieDrawable('thresholdRatio', TRUE);
		$image->drawLineChart(array('DisplayColor' => DISPLAY_AUTO));
		$image->setShadow(FALSE);

		/* Create a legend */
		$data->setSerieDrawable('answeredQuestionRatio', TRUE);
		$data->setSerieDrawable('thresholdRatio', TRUE);
		$legendOptions = array(
			'Style' => LEGEND_BOX,
			'Mode' => LEGEND_HORIZONTAL,
			'FontSize' => 30,
			'R' => 255,
			'G' => 255,
			'B' => 255,
			'IconAreaWidth' => 50,
			'IconAreaHeight' => 50,
			'BoxWidth' => 25,
			'BoxHeight' => 25,
		);
		$image->drawLegend(0, 900, $legendOptions);

		$temporaryBarChartFile = tempnam($this->environmentUtility->getPathToTemporaryDirectory(), 'barchart.png');
		$image->render($temporaryBarChartFile);
		return $temporaryBarChartFile;

	}

}