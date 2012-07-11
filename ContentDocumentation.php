<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */



class ContentDocumentation extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_documentation';

	/**
	 * Absolute base URL to GitHub
	 * @var string
	 */
	protected $strAbsoluteBase;

	/**
	 * Relative base URL to local Contao site
	 * @var string
	 */
	protected $strRelativeBase;


	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### DOCUMENTATION ###';
			$objTemplate->id = $this->github_user . '/' . $this->github_project;
			$objTemplate->link = 'View on GitHub';
			$objTemplate->href = 'http://github.com/' . $this->github_user . '/' . $this->github_project;

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	protected function compile()
	{
		global $objPage;

		$strFile = $this->Environment->request;
		$strFile = str_replace('index.php/', '', $strFile);
		$strFile = str_replace($GLOBALS['TL_CONFIG']['urlSuffix'], '', $strFile);
		$strFile = str_replace('/'.$objPage->alias, '', $strFile);

		if (strlen($strFile) < 4)
		{
			$this->error404();
		}

		$objRequest = new Request();
		$objRequest->send('https://raw.github.com/' . $this->github_user . '/' . $this->github_project . '/' . $this->github_branch . '/' . $this->github_path . $strFile . '.md');

		if ($objRequest->hasError())
		{
			$this->error404();
		}

		// Generate absolute and relative URLs
		$this->strAbsoluteBase = 'https://github.com/' . $this->github_user . '/' . $this->github_project . '/raw/' . $this->github_branch . '/' . $this->github_path . dirname($strFile);
		$this->strRelativeBase = dirname(str_replace($GLOBALS['TL_LANGUAGE'].'/', '', $strFile));

		require_once(TL_ROOT . '/system/modules/documentation/markdown.php');
		$GLOBALS['TL_JAVASCRIPT'][] = 'plugins/highlighter/shCore.css';

		$strBuffer = Markdown($objRequest->response);

		// Update all hyperlinks from GitHub
		$strBuffer = preg_replace_callback('{(<img src="|<a href=")(.*?)(".*?>)}uis', array($this, 'replaceRelativeLinks'), $strBuffer);

		// Enable syntax hightlighter
		$strBuffer = preg_replace_callback('{(<pre><code class="([a-z0-9]+)">)(.+?)(</code></pre>)}is', array($this, 'addSyntaxHighlighter'), $strBuffer);

		$this->Template->buffer = $strBuffer;
	}


	private function replaceRelativeLinks($matches)
	{
		// Do not change absolute URLs
		if (substr($matches[2], 0, 4) == 'http')
		{
			$strUrl = $matches[2];
		}

		// Asset patch is absolute to GitHub
		elseif (strpos($matches[2], 'assets') !== false)
		{
			$strUrl = $this->strAbsoluteBase . dirname($strFile) . '/' . $matches[2];
		}

		// All markdown files are relative to Contao base tag
		elseif (substr($matches[2], -3) == '.md')
		{
			global $objPage;

			$strUrl = $this->generateFrontendUrl($objPage->row(), '/' . $this->strRelativeBase . '/' . substr($matches[2], 0, -3));
		}

		else
		{
			$strUrl = $matches[2];
		}

		return $matches[1] . $strUrl . $matches[3];
	}


	private function addSyntaxHighlighter($matches)
	{
		$arrMapper = array
		(
			'as3'        => 'shBrushAS3',
			'bash'       => 'shBrushBash',
			'c'          => 'shBrushCpp',
			'csharp'     => 'shBrushCSharp',
			'css'        => 'shBrushCss',
			'delphi'     => 'shBrushDelphi',
			'diff'       => 'shBrushDiff',
			'groovy'     => 'shBrushGroovy',
			'java'       => 'shBrushJava',
			'javafx'     => 'shBrushJavaFX',
			'javascript' => 'shBrushJScript',
			'perl'       => 'shBrushPerl',
			'php'        => 'shBrushPhp',
			'powershell' => 'shBrushPowerShell',
			'python'     => 'shBrushPython',
			'ruby'       => 'shBrushRuby',
			'scala'      => 'shBrushScala',
			'sql'        => 'shBrushSql',
			'text'       => 'shBrushPlain',
			'vb'         => 'shBrushVb',
			'chtml'      => 'shBrushXml',
			'xml'        => 'shBrushXml'
		);

		$highlight = strtolower($matches[2]);

		if (!isset($arrMapper[$highlight]))
		{
			return $matches[0];
		}

		// Add the style sheet
		$GLOBALS['TL_CSS'][] = 'plugins/highlighter/shCore.css';

		// Add the core scripts
		$objCombiner = new Combiner();
		$objCombiner->add('plugins/highlighter/XRegExp.js', HIGHLIGHTER);
		$objCombiner->add('plugins/highlighter/shCore.js', HIGHLIGHTER);
		$GLOBALS['TL_JAVASCRIPT'][] = $objCombiner->getCombinedFile(TL_PLUGINS_URL);

		// Add the brushes separately in case there are multiple code elements
		$GLOBALS['TL_JAVASCRIPT'][] = 'plugins/highlighter/' . $arrMapper[$highlight] . '.js';

		global $objPage;

		// Initialization
		if ($objPage->outputFormat == 'xhtml')
		{
			$strInit  = '<script type="text/javascript">' . "\n";
			$strInit .= '/* <![CDATA[ */' . "\n";
		}
		else
		{
			$strInit  = '<script>' . "\n";
		}

		$strInit .= 'SyntaxHighlighter.defaults.toolbar = false;' . "\n";
		$strInit .= 'SyntaxHighlighter.all();' . "\n";

		if ($objPage->outputFormat == 'xhtml')
		{
			$strInit .= '/* ]]> */' . "\n";
		}

		$strInit .= '</script>';

		// Add the initialization script to the head section and not (!) to TL_JAVASCRIPT
		$GLOBALS['TL_HEAD'][] = $strInit;

		return '<pre class="brush: ' . $highlight . '">' . $matches[3] . '</pre>';
	}


	private function error404()
	{
		$objError = new PageError404();
		$objError->generate($objPage->id);
	}
}

