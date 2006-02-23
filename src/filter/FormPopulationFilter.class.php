<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * FormPopulationFilter automatically populates a form that is re-posted, which
 * usually happens when a View::INPUT is returned again after a POST request
 * because an error occured during validation.
 * That means that developers don't have to fill in request parameters into
 * form elements in their templates anymore. Text inputs, selects, radios, they
 * all get set to the value the user selected before submitting the form.
 * If you would like to set default values, you still have to do that in your
 * template. The filter will recognize this situation and automatically remove
 * the default value you assigned after receiving a POST request.
 * This filter only works with POST requests, and compares the form's URL and
 * the requested URL to decide if it's appropriate to fill in a specific form
 * it encounters while processing the output document sent back to the browser.
 * Since this form is executed very late in the process, it works independently
 * of any template language.
 *
 * @package    agavi
 * @subpackage filter
 *
 * @author     David Zuelke <dz@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id$
 */
class FormPopulationFilter extends Filter
{
	/**
	 * Execute this filter.
	 *
	 * @param      FilterChain The filter chain.
	 *
	 * @return     void
	 *
	 * @throws     <b>FilterException</b> If an error occurs during execution.
	 *
	 * @author     David Zuelke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute($filterChain)
	{
		static $loaded;

		if(!isset($loaded)) {
			$loaded = true;
			
			$req = $this->getContext()->getRequest();
			
			if($req->getMethod() == Request::POST) {
				ob_start();
				$filterChain->execute();
				$output = ob_get_contents();
				ob_end_clean();
				$doc = DOMDocument::loadHTML($output);
				$forms = $doc->getElementsByTagName('form');
				$baseHref = '';
				foreach($doc->getElementsByTagName('base') as $base) {
					if($base->hasAttribute('href')) {
						$baseHref = parse_url($base->getAttribute('href'));
						$baseHref = $baseHref['path'];
						break;
					}
				}
				foreach($forms as $form) {
					$action = $form->getAttribute('action');
					if(!($form->getAttribute('method') == 'post' && ($baseHref . $action == $_SERVER['REQUEST_URI'] || $baseHref . '/' . $action == $_SERVER['REQUEST_URI'] || (strpos($action, '/') == 0 && $action == $_SERVER['REQUEST_URI'])))) {
						continue;
					}
					$inputs = $form->getElementsByTagName('input');
					foreach($inputs as $input) {
						if($input->hasAttribute('name')) {
							if(!$input->hasAttribute('type') || $input->getAttribute('type') == 'text') {
								if($input->hasAttribute('value')) {
									$input->removeAttribute('value');
								}
								if($req->hasParameter($input->getAttribute('name'))) {
									$input->setAttribute('value', $req->getParameter($input->getAttribute('name')));
								}
							} elseif(($input->getAttribute('type') == 'checkbox' || $input->getAttribute('type') == 'radio') && $req->hasParameter($input->getAttribute('name'))) {
								$input->removeAttribute('checked');
								if($input->hasAttribute('value')) {
									if($input->getAttribute('value') == $req->getParameter($input->getAttribute('name'))) {
										$input->setAttribute('checked', 'checked');
									}
								} else {
									$input->setAttribute('checked', 'checked');
								}
							}
						}
					}
					$selects = $form->getElementsByTagName('select');
					foreach($selects as $select) {
						if($select->hasAttribute('name') && $req->hasParameter($select->getAttribute('name'))) {
							foreach($select->getElementsByTagName('option') as $option) {
								$option->removeAttribute('selected');
								if($option->hasAttribute('value') && $option->getAttribute('value') == $req->getParameter($select->getAttribute('name'))) {
									$option->setAttribute('selected', 'selected');
								}
							}
						}
					}
					$textareas = $form->getElementsByTagName('textarea');
					foreach($textareas as $textarea) {
						if($textarea->hasAttribute('name') && $req->hasParameter($textarea->getAttribute('name'))) {
							foreach($textarea->childNodes as $cn) {
								$textarea->removeChild($cn);
							}
							$textarea->appendChild($doc->createTextNode($req->getParameter($textarea->getAttribute('name'))));
						}
					}
				}
				if($doc->doctype && strpos($doc->doctype->publicId, '-//W3C//DTD XHTML') == 0) {
					echo $doc->saveXML();
				} else {
					echo $doc->saveHTML();
				}
			} else {
				$filterChain->execute();
			}
		} else {
			// we already loaded this filter, skip to the next filter
			$filterChain->execute();
		}
	}
}

?>