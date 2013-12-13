<?php
namespace GIB\GradingTool\Tests\Unit\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
use GIB\GradingTool\Utility\Arrays;

/**
 * Testcase for the Utility Array class
 *
 */
class ArraysTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function removeEmptyElementsRecursivelyRemovesNullAndEmptyValues() {
		$array = array('NullElement' => NULL, 'EmptyElement' => '', 'Foo' => array('Bar' => array('Baz' => array('NotEmpty' => 'Not empty', 'AnotherEmptyElement' => NULL))));
		$expectedResult = array('Foo' => array('Bar' => array('Baz' => array('NotEmpty' => 'Not empty'))));
		$actualResult = Arrays::removeEmptyElementsRecursively($array);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 */
	public function removeEmptyElementsRecursivelyRemovesEmptySubArrays() {
		$array = array('EmptyElement' => array(), 'Foo' => array('Bar' => array('Baz' => array('AnotherNullElement' => NULL, 'AnotherEmptyElement' => ''))), 'NotNull' => 123);
		$expectedResult = array('NotNull' => 123);
		$actualResult = Arrays::removeEmptyElementsRecursively($array);
		$this->assertEquals($expectedResult, $actualResult);
	}
}
