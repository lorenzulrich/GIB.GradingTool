<?php
namespace GIB\GradingTool\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class TcPdf extends \TYPO3\TcPdf\Pdf {

	/**
	 * @var string
	 */
	public $projectTitle;

	/**
	 * @var string
	 */
	public $exportDate;

	/**
	 * PDF header
	 */
	public function Header() {
		$html = '';
		if ($this->PageNo() !== 1) {
			$html .= '
<style>
	table.headerTable {
		color: #555555;
		font-size: 10px;
	}
	td.headerBorderBottom {
		border-bottom: 1px solid #555555;
		font-size: 4px;
	}
</style>
<table class="headerTable" width="100%">
	<tr width="100%">
		<td width="12%" class="headerCell">
			' . $this->getAliasNumPage() . '
		</td>
		<td width="88%" class="headerCell">
			Global Infrastructure Basel Foundation, Switzerland
		</td>
	</tr>
	<tr width="100%">
		<td class="headerBorderBottom" colspan="2" width="100%">
		</td>
	</tr>
</table>
			';
			$this->writeHTML($html, TRUE, FALSE, FALSE, FALSE, '');
		} else {
			$this->writeHTML('');
		}
	}

	/**
	 * PDF footer
	 */
	public function Footer() {
		$html = '';
		if ($this->PageNo() !== 1) {
			$html .= '
<style>
	table.footerTable {
		color: #555555;
		font-size: 10px;
	}
	td.footerBorderTop {
		border-top: 1px solid #555555;
		font-size: 4px;
	}
</style>
<table class="footerTable" width="100%">
	<tr width="100%">
		<td class="footerBorderTop" colspan="2" width="100%">
		</td>
	</tr>
	<tr width="100%">
		<td width="12%" class="headerCell">
		</td>
		<td width="76%" class="headerCell">
			' . $this->projectTitle . '
		</td>
		<td width="12%" class="headerCell" align="right">
			' . $this->exportDate . '
		</td>
	</tr>
</table>
			';
			$this->writeHTML($html, TRUE, FALSE, FALSE, FALSE, '');
		} else {
			$this->writeHTML('');
		}
	}

	/**
	 * Output a Table Of Content Index (TOC) using HTML templates.
	 * This method must be called after all Bookmarks were set.
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * @param $page (int) page number where this TOC should be inserted (leave empty for current page).
	 * @param $toc_name (string) name to use for TOC bookmark.
	 * @param $templates (array) array of html templates. Use: "#TOC_DESCRIPTION#" for bookmark title, "#TOC_PAGE_NUMBER#" for page number.
	 * @param $correct_align (boolean) if true correct the number alignment (numbers must be in monospaced font like courier and right aligned on LTR, or left aligned on RTL)
	 * @param $style (string) Font style for title: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array for title (values from 0 to 255).
	 * @param $afterContent (string) Content to add after the TOC
	 * @public
	 * @author Nicola Asuni
	 * @since 5.0.001 (2010-05-06)
	 * @see addTOCPage(), endTOCPage(), addTOC()
	 */
	public function addHTMLTOC($page='', $toc_name='TOC', $templates=array(), $correct_align=true, $style='', $color=array(0,0,0), $afterContent = '') {
		$filler = ' ';
		$prev_htmlLinkColorArray = $this->htmlLinkColorArray;
		$prev_htmlLinkFontStyle = $this->htmlLinkFontStyle;
		// set new style for link
		$this->htmlLinkColorArray = array();
		$this->htmlLinkFontStyle = '';
		$page_first = $this->getPage();
		$page_fill_start = false;
		$page_fill_end = false;
		// get the font type used for numbers in each template
		$current_font = $this->FontFamily;
		foreach ($templates as $level => $html) {
			$dom = $this->getHtmlDomArray($html);
			foreach ($dom as $key => $value) {
				if ($value['value'] == '#TOC_PAGE_NUMBER#') {
					$this->SetFont($dom[($key - 1)]['fontname']);
					$templates['F'.$level] = $this->isUnicodeFont();
				}
			}
		}
		$this->SetFont($current_font);
		$maxpage = 0; //used for pages on attached documents
		foreach ($this->outlines as $key => $outline) {
			// get HTML template
			$row = $templates[$outline['l']];
			if (\TCPDF_STATIC::empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if ($templates['F'.$outline['l']]) {
					$pagenum = '{'.$pagenum.'}';
				}
				$maxpage = max($maxpage, $outline['p']);
			}
			// replace templates with current values
			$row = str_replace('#TOC_DESCRIPTION#', $outline['t'], $row);
			$row = str_replace('#TOC_DESCRIPTION#', $outline['t'], $row);
			$row = str_replace('#TOC_CHAPTERNUMBER#', $outline['cn'], $row);
			$row = str_replace('#TOC_CSSCLASS#', $outline['cssClass'], $row);
			$row = str_replace('#TOC_PAGE_NUMBER#', $pagenum, $row);
			// add link to page
			$row = '<a href="#'.$outline['p'].','.$outline['y'].'">'.$row.'</a>';
			// write bookmark entry
			$this->writeHTML($row, false, false, true, false, '');
		}
		// restore link styles
		$this->htmlLinkColorArray = $prev_htmlLinkColorArray;
		$this->htmlLinkFontStyle = $prev_htmlLinkFontStyle;
		// move TOC page and replace numbers
		$page_last = $this->getPage();
		$numpages = ($page_last - $page_first + 1);
		// account for booklet mode
		if ($this->booklet) {
			// check if a blank page is required before TOC
			$page_fill_start = ((($page_first % 2) == 0) XOR (($page % 2) == 0));
			$page_fill_end = (!((($numpages % 2) == 0) XOR ($page_fill_start)));
			if ($page_fill_start) {
				// add a page at the end (to be moved before TOC)
				$this->addPage();
				++$page_last;
				++$numpages;
			}
			if ($page_fill_end) {
				// add a page at the end
				$this->addPage();
				++$page_last;
				++$numpages;
			}
		}
		$maxpage = max($maxpage, $page_last);
		if (!\TCPDF_STATIC::empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);
				for ($n = 1; $n <= $maxpage; ++$n) {
					// update page numbers
					$a = '{#'.$n.'}';
					// get page number aliases
					$pnalias = $this->getInternalPageNumberAliases($a);
					// calculate replacement number
					if ($n >= $page) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}
					$na = \TCPDF_STATIC::formatTOCPageNumber(($this->starting_page_number + $np - 1));
					$nu = \TCPDF_FONTS::UTF8ToUTF16BE($na, false, $this->isunicode, $this->CurrentFont);
					// replace aliases with numbers
					foreach ($pnalias['u'] as $u) {
						if ($correct_align) {
							$sfill = str_repeat($filler, (strlen($u) - strlen($nu.' ')));
							if ($this->rtl) {
								$nr = $nu.\TCPDF_FONTS::UTF8ToUTF16BE(' '.$sfill, false, $this->isunicode, $this->CurrentFont);
							} else {
								$nr = \TCPDF_FONTS::UTF8ToUTF16BE($sfill.' ', false, $this->isunicode, $this->CurrentFont).$nu;
							}
						} else {
							$nr = $nu;
						}
						$temppage = str_replace($u, $nr, $temppage);
					}
					foreach ($pnalias['a'] as $a) {
						if ($correct_align) {
							$sfill = str_repeat($filler, (strlen($a) - strlen($na.' ')));
							if ($this->rtl) {
								$nr = $na.' '.$sfill;
							} else {
								$nr = $sfill.' '.$na;
							}
						} else {
							$nr = $na;
						}
						$temppage = str_replace($a, $nr, $temppage);
					}
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// append afterContent if set
			if (!empty($afterContent)) {
				$this->writeHTML($afterContent, TRUE, FALSE, TRUE);
			}

			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first, $style, $color);
			if ($page_fill_start) {
				$this->movePage($page_last, $page_first);
			}
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

	public function setBookmarkWithChapterNumberAndClass($txt, $level=0, $chapterNumber='', $class='') {
		$this->Bookmark($txt, $level, -1, '', '', array(0,0,0), -1, '', $chapterNumber, $class);
	}

	/**
	 * Adds a bookmark.
	 * @param $txt (string) Bookmark description.
	 * @param $level (int) Bookmark level (minimum value is 0).
	 * @param $y (float) Y position in user units of the bookmark on the selected page (default = -1 = current position; 0 = page start;).
	 * @param $page (int|string) Target page number (leave empty for current page). If you prefix a page number with the * character, then this page will not be changed when adding/deleting/moving pages.
	 * @param $style (string) Font style: B = Bold, I = Italic, BI = Bold + Italic.
	 * @param $color (array) RGB color array (values from 0 to 255).
	 * @param $x (float) X position in user units of the bookmark on the selected page (default = -1 = current position;).
	 * @param $link (mixed) URL, or numerical link ID, or named destination (# character followed by the destination name), or embedded file (* character followed by the file name).
	 * @param $chapterNumber (string) Chapter number
	 * @param $class (string) CSS class for styling the TOC entry generated by the Bookmark
	 * @public
	 * @since 2.1.002 (2008-02-12)
	 */
	public function Bookmark($txt, $level=0, $y=-1, $page='', $style='', $color=array(0,0,0), $x=-1, $link='', $chapterNumber='', $class='') {
		if ($level < 0) {
			$level = 0;
		}
		if (isset($this->outlines[0])) {
			$lastoutline = end($this->outlines);
			$maxlevel = $lastoutline['l'] + 1;
		} else {
			$maxlevel = 0;
		}
		if ($level > $maxlevel) {
			$level = $maxlevel;
		}
		if ($y == -1) {
			$y = $this->GetY();
		} elseif ($y < 0) {
			$y = 0;
		} elseif ($y > $this->h) {
			$y = $this->h;
		}
		if ($x == -1) {
			$x = $this->GetX();
		} elseif ($x < 0) {
			$x = 0;
		} elseif ($x > $this->w) {
			$x = $this->w;
		}
		$fixed = false;
		if (!empty($page) AND ($page[0] == '*')) {
			$page = intval(substr($page, 1));
			// this page number will not be changed when moving/add/deleting pages
			$fixed = true;
		}
		if (empty($page)) {
			$page = $this->PageNo();
			if (empty($page)) {
				return;
			}
		}
		$this->outlines[] = array('t' => $txt, 'l' => $level, 'x' => $x, 'y' => $y, 'p' => $page, 'f' => $fixed, 's' => strtoupper($style), 'c' => $color, 'u' => $link, 'cn' => $chapterNumber, 'cssClass' => $class);
	}



}
