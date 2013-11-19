<?php
namespace GIB\GradingTool\Utility;
/**
 * Created by PhpStorm.
 * User: lorenz
 * Date: 19.11.13
 * Time: 12:46
 */

class ArrayDiffUtility {

	public static function compare($old, $new) {

		$content = '';

		if (empty($old) && !empty($new)) {
			// we had no values before, therefore all values are new
			foreach ($new as $newItem) {
				$content .= '
					<tr>
						<td class="diffDeleted"></td>
						<td class="diffInserted">' . $newItem . '</td>
					</tr>';
			}
			return $content;
		} elseif (!empty($old) && empty($new)) {
			// all values were removed
			foreach ($old as $oldItem) {
				$content .= '
					<tr>
						<td class="diffDeleted">' . $oldItem . '</td>
						<td class="diffInserted"></td>
					</tr>';
			}
			return $content;
		} else {
			// some items maybe have been deleted, some maybe have been added
			$content = '';

			$deleted = array_diff($old, $new);
			if (count($deleted)) {
				foreach ($deleted as $deletedItem) {
					$content .= '
						<tr>
							<td class="diffDeleted">' . $deletedItem . '</td>
							<td class="diffInserted"></td>
						</tr>';
				}
			}

			$added = array_diff($new, $old);
			if (count($added)) {
				foreach ($added as $addedItem) {
					$content .= '
						<tr>
							<td class="diffDeleted"></td>
							<td class="diffInserted">' . $addedItem . '</td>
						</tr>';
				}
			}

			return $content;

		}



	}

}