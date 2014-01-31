<?php
namespace GIB\GradingTool\Utility;

use TYPO3\Flow\Reflection\ObjectAccess;

class DiffUtility {

	/**
	 * @param $current
	 * @param $new
	 * @return array
	 */
	public static function arrayDiffRecursive($current, $new) {

		$changes = array();

		foreach ($new as $key => $newValue) {
			if (is_string($newValue)) {
				$currentValue = array_key_exists($key, $current) ? $current[$key] : NULL;
				$result = self::diffString($currentValue, $newValue, $key);
				if (is_array($result)) {
					$changes[] = $result;
				}
				// Remove the entry from the current data to see which data isn't present anymore
				unset($current[$key]);
			} elseif (is_array($newValue)) {
				$currentValue = array_key_exists($key, $current) ? $current[$key] : NULL;
				$result = self::diffArray($currentValue, $newValue, $key);
				if (is_array($result)) {
					$changes[] = $result;
				}
				// Remove the entry from the current data to see which data isn't present anymore
				unset($current[$key]);
			} elseif (is_object($newValue)) {
				// currently we only diff objects of type Resource
				$currentValue = array_key_exists($key, $current) ? $current[$key] : NULL;
				if (ObjectAccess::isPropertyGettable($newValue, 'filename')) {
					if (is_object($currentValue) && ObjectAccess::isPropertyGettable($currentValue, 'filename')) {
						$currentValue = ObjectAccess::getProperty($currentValue, 'filename');
					}
					$newFilename = ObjectAccess::getProperty($newValue, 'filename');
					$result = self::diffString($currentValue, $newFilename, $key);
					if (is_array($result)) {
						$changes[] = $result;
					}
					// Remove the entry from the current data to see which data isn't present anymore
					unset($current[$key]);
				}
			}
		}

		// $current now only holds values that were present before the changes, but not afterwards
		foreach ($current as $key => $currentValue) {
			if (is_string($currentValue)) {
				// string to NULL
				$changes[] = array(
					'reason' => 'H',
					'key' => ucfirst($key),
					'current' => $currentValue
				);
				// Remove the entry from the current data to see which data isn't present anymore
				unset($current[$key]);
			} elseif (is_array($currentValue)) {
				// array to NULL
				$changes[] = array(
					'reason' => 'I',
					'key' => ucfirst($key),
					'current' => implode("\n", $currentValue)
				);
				// Remove the entry from the current data to see which data isn't present anymore
				unset($current[$key]);
			}
		}

		return $changes;

	}

	/**
	 * @param $current
	 * @param $new
	 * @param $key
	 * @return array|bool
	 */
	public static function diffString($current, $new, $key) {
		$result = array();
		if ($current === $new) {
			// unchanged string, we don't need a diff
			return FALSE;
		} else {
			// values are not identical
			if (is_string($current) && is_string($new)) {
				// string to new string
				$result['reason'] = 'A';
				$result['key'] = ucfirst($key);
				$result['current'] = $current;
				$result['new'] = $new;
			} elseif (!$current && is_string($new)) {
				// NULL to string
				$result['reason'] = 'C';
				$result['key'] = ucfirst($key);
				$result['new'] = $new;
			} elseif (is_array($current)) {
				// array to empty, array to string
				$result['reason'] = 'D';
				$result['key'] = ucfirst($key);
				$result['current'] = implode("\n", $current);
				$result['new'] = $new;
			} elseif (is_object($current)) {
				if (ObjectAccess::isPropertyGettable($current, 'filename')) {
					$filename = ObjectAccess::getProperty($current, 'filename');
					// file resource to empty
					$result['reason'] = 'J';
					$result['key'] = ucfirst($key);
					$result['current'] = $filename;
					$result['new'] = $new;
				}
			} else {
				return FALSE;
			}

		}

		return $result;
	}

	/**
	 * @param $current
	 * @param $new
	 * @param $key
	 * @return array|bool
	 */
	public static function diffArray($current, $new, $key) {
		$result = array();
		if ($current === $new) {
			// unchanged array, we don't need a diff
			return FALSE;
		} else {
			// values are not identical
			if (is_array($current) && is_array($new)) {
				// array to new array
				$result['reason'] = 'E';
				$result['key'] = ucfirst($key);
				$removedItems = array_diff($current, $new);
				$addedItems = array_diff($new, $current);
				$result['current'] = implode("\n", $removedItems);
				$result['new'] = implode("\n", $addedItems);
			} elseif (is_string($current) && is_array($new)) {
				// string to array, empty to array
				$result['reason'] = 'F';
				$result['key'] = ucfirst($key);
				$result['current'] = $current;
				$result['new'] = implode("\n", $new);
			} elseif (!$current && is_array($new)) {
				// NULL to array
				$result['reason'] = 'G';
				$result['key'] = ucfirst($key);
				$result['new'] = implode("\n", $new);
			} else {
				return FALSE;
			}

		}

		return $result;
	}

}