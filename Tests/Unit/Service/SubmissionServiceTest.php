<?php
namespace GIB\GradingTool\Tests\Unit\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for the Utility Array class
 *
 */

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

class SubmissionServiceTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \GIB\GradingTool\Service\SubmissionService
	 */
	protected $submissionService;

	/**
	 * @var \TYPO3\Form\Persistence\YamlPersistenceManager
	 */
	protected $yamlPersistenceManager;


	public function setUp() {
		$this->submissionService = $this->getAccessibleMock('GIB\GradingTool\Service\SubmissionService', array('dummy'), array(), '', FALSE);

		// get a yaml persistence manager
		$this->yamlPersistenceManager = new \TYPO3\Form\Persistence\YamlPersistenceManager();
		vfsStream::setup('someSavePath');
		$yamlPersistenceManagerSettings = array(
			'yamlPersistenceManager' =>
				array('savePath' => vfsStream::url('someSavePath'))
		);
		$this->yamlPersistenceManager->injectSettings($yamlPersistenceManagerSettings);

		// write a mock submission form
		$mockYamlFormDefinition = "type: 'TYPO3.Form:Form'
identifier: mockSubmissionForm
label: 'Mock Submission Form'
renderables:
    -
        type: 'TYPO3.Form:Page'
        identifier: accountability
        label: Accountability
        renderables:
            -
                type: 'TYPO3.Form:Section'
                identifier: sectionAccountability
                label: Accountability
                properties:
                    elementClass: hideCaption
                    displayConditionField: ''
                    displayConditionValue: ''
                    goodPerformanceReferenceScore: '3.6'
                    modestPerformanceReferenceScore: '2.7'
                    naAcceptanceLevel: '0.2'
                renderables:
                    -
                        type: 'GIB.GradingTool:Question'
                        identifier: accountability1
                        label: '1.) Does this project have a clear organisational setup?'
                        validators:
                            -
                                identifier: 'TYPO3.Flow:NotEmpty'
                        properties:
                            options:
                                -
                                    _value: 'The organisational structure of service provision makes no distinction between different roles such as strategic level (board), execution (management), oversight, arbitration (e.g. for consumer claims) etc.'
                                    _key: '1'
                                    score: '1'
                                -
                                    _value: 'Different bodies are in place and roles are separated but it is not adequate due to incompleteness of staffing, institutional overlaps, or overlapping or missing competencies, etc.'
                                    _key: '2'
                                    score: '2'
                                -
                                    _value: 'The organisational structure and role separation are in place, but the interaction between the bodies/actors does not function all the time.'
                                    _key: '3'
                                    score: '3'
                                -
                                    _value: 'An organisational structure is in place with clear role separation (separation of board and management, oversight, arbitration etc.), unambiguous allocation of responsibilities and duties. Proper interaction between actors are clearly defined and function effectively.'
                                    _key: '4'
                                    score: '4'
                                -
                                    _value: 'Not applicable/other'
                                    _key: '5'
                                    score: '0'
                            weighting: '1'
                            optOut: '0'
                            abstract: 'Clear organisational setup'
    -
        type: 'TYPO3.Form:Page'
        identifier: transparency
        label: Transparency
        renderables:
            -
                type: 'TYPO3.Form:Section'
                identifier: sectionTransparency
                label: Transparency
                properties:
                    elementClass: hideCaption
                    displayConditionField: ''
                    displayConditionValue: ''
                    goodPerformanceReferenceScore: '3.6'
                    modestPerformanceReferenceScore: '2.5'
                    naAcceptanceLevel: '0.2'
                renderables:
                    -
                        type: 'GIB.GradingTool:Question'
                        identifier: transparency1
                        label: '1.) How is public access to information concerning the project for the public organised?'
                        validators:
                            -
                                identifier: 'TYPO3.Flow:NotEmpty'
                        properties:
                            options:
                                -
                                    _value: 'There is no clear guidance/regulation that defines which documents (e.g. reports, studies or contracts) are publicly available, whether at local or national level.'
                                    _key: '1'
                                    score: '1'
                                -
                                    _value: 'Access to information requires substantial effort and persistence. Information is usually provided selectively.'
                                    _key: '2'
                                    score: '2'
                                -
                                    _value: 'All relevant documents are available to the public on demand (e.g. via written request and/or payment for service).'
                                    _key: '3'
                                    score: '3'
                                -
                                    _value: 'All relevant documents are available. There is a written policy in place and contracts contain defined transparency requirements; financing of those activities is clarified.'
                                    _key: '4'
                                    score: '4'
                                -
                                    _value: 'Not applicable/other'
                                    _key: '5'
                                    score: '0'
                            weighting: '1'
                            optOut: '0'
                            abstract: 'Public access to information'
    -
        type: 'TYPO3.Form:Page'
        identifier: customerFocus
        label: 'Customer Focus'
        renderables:
            -
                type: 'TYPO3.Form:Section'
                identifier: sectionCustomerFocus
                label: 'Customer Focus'
                properties:
                    elementClass: hideCaption
                    displayConditionField: ''
                    displayConditionValue: ''
                    goodPerformanceReferenceScore: '3.4'
                    modestPerformanceReferenceScore: '2.1'
                    naAcceptanceLevel: '0.3'
                renderables:
                    -
                        type: 'GIB.GradingTool:Question'
                        identifier: customerFocus1
                        label: '1.) Will the provided services justify the usage fees?'
                        validators:
                            -
                                identifier: 'TYPO3.Flow:NotEmpty'
                        properties:
                            options:
                                -
                                    _value: 'No, there will constantly be a significant gap: services will be far from adequate given the charges.'
                                    _key: '1'
                                    score: '1'
                                -
                                    _value: 'The service will not be able to guarantee continuity due to market uncertainty such as the fluctuation of raw materials and local market dynamism. The tariff would be reasonable if the service were continuous and good.'
                                    _key: '2'
                                    score: '2'
                                -
                                    _value: 'The service level and the charges will largely be justified.'
                                    _key: '3'
                                    score: '3'
                                -
                                    _value: 'The project has undergone careful study and scientific analysis. All possible factors on pricing and consumer affordability and expectation will be taken into account. The service quality rendered will justify the service charge in a sustainable manner.'
                                    _key: '4'
                                    score: '4'
                                -
                                    _value: 'Not applicable/other'
                                    _key: '5'
                                    score: '0'
                            weighting: '1'
                            optOut: '1'
                            abstract: 'Reasonable service charges'
                    -
                        type: 'GIB.GradingTool:NotApplicableMultiLineText'
                        identifier: customerFocusComment1
                        label: Comment
                        properties:
                            placeholder: ''
                            questionIdentifier: customerFocus1
                        defaultValue: ''
                    -
                        type: 'GIB.GradingTool:OptOutAcceptedCheckbox'
                        identifier: customerFocusOptOutAccepted1
                        label: 'Opt-out accepted'
                        properties:
                            questionIdentifier: customerFocus1
                    -
                        type: 'GIB.GradingTool:Question'
                        identifier: customerFocus2
                        label: '2.) Does the project allow its users to be informed of all relevant rights and obligations?'
                        validators:
                            -
                                identifier: 'TYPO3.Flow:NotEmpty'
                        properties:
                            options:
                                -
                                    _value: 'No special attempts are made to figure out the details of the relation between service provider and customer; service users are not seen as customers.'
                                    _key: '1'
                                    score: '1'
                                -
                                    _value: 'The majority of customers do not know their rights and obligations.'
                                    _key: '2'
                                    score: '2'
                                -
                                    _value: 'A customer charter exists and is communicated.'
                                    _key: '3'
                                    score: '3'
                                -
                                    _value: 'Satisfaction of customers and stakeholders is pursued throughout the project life cycle. Strategic and systematic customer care and stakeholder relations management is integrated into the project process management.'
                                    _key: '4'
                                    score: '4'
                                -
                                    _value: 'Not applicable/other'
                                    _key: '5'
                                    score: '0'
                            weighting: '1'
                            optOut: '0'
                            abstract: 'Transparency of roles and responsabilities'
                    -
                        type: 'GIB.GradingTool:Question'
                        identifier: customerFocus3
                        label: '3.) To what extent will customer-friendly complaint procedures exist and how will they be implemented/communicated?'
                        validators:
                            -
                                identifier: 'TYPO3.Flow:NotEmpty'
                        properties:
                            options:
                                -
                                    _value: 'No formal complaint procedures will exist.'
                                    _key: '1'
                                    score: '1'
                                -
                                    _value: 'Complaint procedures will exist but they will not be customer friendly (e.g. no customer care centre, complaints only possible through court system).'
                                    _key: '2'
                                    score: '2'
                                -
                                    _value: 'Complaint procedures will exist and they will be easily accessible, but complaints without ready solutions will not be further followed up.'
                                    _key: '3'
                                    score: '3'
                                -
                                    _value: 'Customer complaint procedures will be publicly announced and the process of filing a complaint will be easy for all customer groups. Complaints will be taken seriously and addressed in reasonable amount of time.'
                                    _key: '4'
                                    score: '4'
                                -
                                    _value: 'Not applicable/other'
                                    _key: '5'
                                    score: '0'
                            weighting: '1'
                            optOut: '0'
                            abstract: 'Customer-friendly complaint procedure'
                    -
                        type: 'GIB.GradingTool:Question'
                        identifier: customerFocus4
                        label: '4.) How comprehensible will the invoices/bills be presented to customers and the related tariff systems?'
                        validators:
                            -
                                identifier: 'TYPO3.Flow:NotEmpty'
                        properties:
                            options:
                                -
                                    _value: 'The bill with the services charged will not inform about details of the calculation and / or the tariff system will not be understandable to ordinary customers.'
                                    _key: '1'
                                    score: '1'
                                -
                                    _value: 'The bill will merely present a summary of charges with no explanations.'
                                    _key: '2'
                                    score: '2'
                                -
                                    _value: 'The tariff system will be clear and traceable and the bill will also reflect the tariff system.'
                                    _key: '3'
                                    score: '3'
                                -
                                    _value: 'The tariff system will be clear and traceable and the bill will also reflect the impacts of the tariff system. Obtaining additional information will be straightforward.'
                                    _key: '4'
                                    score: '4'
                                -
                                    _value: 'Not applicable/other'
                                    _key: '5'
                                    score: '0'
                            weighting: '1'
                            optOut: '1'
                            abstract: 'Comprehensible tariffs and bills'
                    -
                        type: 'GIB.GradingTool:NotApplicableMultiLineText'
                        identifier: customerFocusComment4
                        label: Comment
                        properties:
                            placeholder: ''
                            questionIdentifier: customerFocus4
                        defaultValue: ''
                    -
                        type: 'GIB.GradingTool:OptOutAcceptedCheckbox'
                        identifier: customerFocusOptOutAccepted4
                        label: 'Opt-out accepted'
                        properties:
                            questionIdentifier: customerFocus4";
		file_put_contents(vfsStream::url('someSavePath/mockSubmissionForm.yaml'), $mockYamlFormDefinition);
		$this->submissionService->_set('formPersistenceManager', $this->yamlPersistenceManager);
		$gradingToolSettings = array('forms' => array('submission' => 'mockSubmissionForm'));
		$this->submissionService->_set('settings', $gradingToolSettings);

	}

	/**
	 * @test
	 */
	public function assertSubmissionHasErrorIfNoQuestionWasAnswered() {
		/** @var \GIB\GradingTool\Domain\Model\Project $mockProject */
		$mockProject = $this->getAccessibleMock('GIB\GradingTool\Domain\Model\Project', array('dummy'), array(), '', FALSE);
		// empty: all questions
		$submissionContent = array();
		$mockProject->_set('submissionContent', serialize($submissionContent));

		$submission = $this->submissionService->getProcessedSubmission($mockProject);
		$this->assertEquals($submission['hasError'], TRUE);
	}

	/**
	 * @test
	 */
	public function assertSubmissionHasErrorIfNotAllQuestionsWereAnswered() {
		/** @var \GIB\GradingTool\Domain\Model\Project $mockProject */
		$mockProject = $this->getAccessibleMock('GIB\GradingTool\Domain\Model\Project', array('dummy'), array(), '', FALSE);
		// empty: customerFocus4
		$submissionContent = array(
			'accountability1' => 4,
			'transparency1' => 4,
			'customerFocus1' => 4,
			'customerFocus2' => 4,
			'customerFocus3' => 4,
		);
		$mockProject->_set('submissionContent', serialize($submissionContent));

		$submission = $this->submissionService->getProcessedSubmission($mockProject);
		$this->assertEquals($submission['hasError'], TRUE);
	}

	/**
	 * @test
	 */
	public function assertSubmissionHasErrorIfThresholdForAtLeastOneQuestionWasNotReached() {
		/** @var \GIB\GradingTool\Domain\Model\Project $mockProject */
		$mockProject = $this->getAccessibleMock('GIB\GradingTool\Domain\Model\Project', array('dummy'), array(), '', FALSE);
		// accountability1 has N/A answer, but naAcceptanceLevel is higher
		$submissionContent = array(
			'accountability1' => 5,
			'transparency1' => 4,
			'customerFocus1' => 4,
			'customerFocus2' => 4,
			'customerFocus3' => 4,
			'customerFocus4' => 4,
		);
		$mockProject->_set('submissionContent', serialize($submissionContent));

		$submission = $this->submissionService->getProcessedSubmission($mockProject);
		$this->assertEquals($submission['hasError'], TRUE);
	}

	/**
	 * @test
	 */
	public function assertSubmissionHasNoErrorIfOptOutQuestionWasAccepted() {
		/** @var \GIB\GradingTool\Domain\Model\Project $mockProject */
		$mockProject = $this->getAccessibleMock('GIB\GradingTool\Domain\Model\Project', array('dummy'), array(), '', FALSE);
		// naAcceptanceLevel = 0.7, therefore 3 questions must be answered
		// 2 questions have N/A, but one has optOutAccepted = 1, so the threshold is reached
		$submissionContent = array(
			'accountability1' => 4,
			'transparency1' => 4,
			'customerFocus1' => 5,
			'customerFocus2' => 4,
			'customerFocus3' => 4,
			'customerFocus4' => 5,
			'customerFocusOptOutAccepted4' => 1,
		);
		$mockProject->_set('submissionContent', serialize($submissionContent));

		$submission = $this->submissionService->getProcessedSubmission($mockProject);
		$this->assertEquals($submission['hasError'], FALSE);
	}

	/**
	 * @test
	 */
	public function assertSectionScore() {
		/** @var \GIB\GradingTool\Domain\Model\Project $mockProject */
		$mockProject = $this->getAccessibleMock('GIB\GradingTool\Domain\Model\Project', array('dummy'), array(), '', FALSE);
		// naAcceptanceLevel = 0.7, therefore 3 questions must be answered
		// 2 questions have N/A, but one has optOutAccepted = 1, so the threshold is reached
		$submissionContent = array(
			'accountability1' => 4,
			'transparency1' => 4,
			'customerFocus1' => 5,
			'customerFocus2' => 4,
			'customerFocus3' => 4,
			'customerFocus4' => 5,
			'customerFocusOptOutAccepted4' => 1,
		);
		$mockProject->_set('submissionContent', serialize($submissionContent));

		$submission = $this->submissionService->getProcessedSubmission($mockProject);
		$this->assertEquals($submission['hasError'], FALSE);
	}

}
