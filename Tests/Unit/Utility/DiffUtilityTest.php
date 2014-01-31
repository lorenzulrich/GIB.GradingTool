<?php
namespace GIB\GradingTool\Tests\Unit\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "GIB.GradingTool".       *
 *                                                                        *
 *                                                                        */
use GIB\GradingTool\Utility\DiffUtility;
use TYPO3\Flow\Utility\Algorithms;

/**
 * Testcase for Project
 */
class DiffUtilityTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function makeSureDiffUtilityReturnsDiffForEveryChangedItem() {
		//$this->markTestIncomplete('Automatically generated test case; you need to adjust this!');

		$currentData = $this->diffUtilityCurrentData();
		$newData = $this->diffUtilityNewData();

		$changes = DiffUtility::arrayDiffRecursive($currentData, $newData);
		$numberOfChangesInTestData = 15;

		$this->assertEquals($numberOfChangesInTestData, count($changes));
	}

	public function diffUtilityCurrentData() {
		return array(

			'stringToNewString' => 'Current project title',
			'stringToArray' => 'Current string',
			'stringToNull' => 'Current string',
			'stringToEmpty' => 'Current string',
			'stringUnchanged' => 'String',

			'arrayToNewArray' => array(
				0 => 'First current item',
				1 => 'Second current item',
				2 => 'Common item',
			),
			'arrayToString' => array(
				0 => 'First Item',
				1 => 'Second Item',
			),
			'arrayToNull' => array(
				0 => 'First Item',
				1 => 'Second Item',
			),
			'arrayToEmpty' => array(
				0 => 'First item',
				1 => 'Second item',
			),
			'arrayUnchanged' => array(
				0 => 'First item',
				1 => 'Second item',
			),

			'emptyToString' => '',
			'emptyToArray' => '',
			'emptyToFileResource' => '',

			'fileResourceToEmpty' => $this->getMockFileResource(),
			'fileResourceToNewFileResource' => $this->getMockFileResource('old.jpg'),
			'fileResourceUnchanged' => $this->getMockFileResource()
		);

	}

	public function diffUtilityNewData() {
		return array(

			'stringToNewString' => 'New project title',
			'stringToArray' => array(
				0 => 'First Item',
				1 => 'Second Item',
			),
			'stringToEmpty' => '',
			'stringUnchanged' => 'String',

			'arrayToNewArray' => array(
				0 => 'First new item',
				1 => 'Second new item',
				2 => 'Common item',
			),
			'arrayToString' => 'String',
			'arrayToEmpty' => '',
			'arrayUnchanged' => array(
				0 => 'First item',
				1 => 'Second item',
			),

			'emptyToString' => 'String',
			'emptyToArray' => array(
				0 => 'First item',
				1 => 'Second item',
			),
			'emptyToFileResource' => $this->getMockFileResource(),

			'nullToString' => 'String',
			'nullToArray' => array(
				0 => 'First item',
				1 => 'Second item',
			),

			'fileResourceToEmpty' => '',
			'fileResourceToNewFileResource' => $this->getMockFileResource('new.jpg'),
			'fileResourceUnchanged' => $this->getMockFileResource()

		);

	}

	public function getMockFileResource($filename = 'test.jpg') {

		$hash = sha1(Algorithms::generateRandomBytes(40));
		$resourcePointer = new \TYPO3\Flow\Resource\ResourcePointer($hash);

		$mockResource = $this->getAccessibleMock('TYPO3\Flow\Resource\Resource', array('getResourcePointer'));
		$mockResource->expects($this->any())
			->method('getResourcePointer')
			->will($this->returnValue($resourcePointer));
		$mockResource->_set('filename', $filename);

		return $mockResource;
	}
}
?>