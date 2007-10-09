<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2007 the Agavi Project.                                |
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
 * AgaviFormPopulationFilter automatically populates a form that is re-posted,
 * which usually happens when a View::INPUT is returned again after a POST
 * request because an error occured during validation.
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
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviFormPopulationFilter extends AgaviFilter implements AgaviIGlobalFilter, AgaviIActionFilter
{
	const ENCODING_UTF_8 = 'utf-8';
	
	const ENCODING_ISO_8859_1 = 'iso-8859-1';
	
	protected $doc;
	protected $xpath;
	protected $ns;
	
	/**
	 * Execute this filter.
	 *
	 * @param      AgaviFilterChain        The filter chain.
	 * @param      AgaviExecutionContainer The current execution container.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during execution.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function executeOnce(AgaviFilterChain $filterChain, AgaviExecutionContainer $container)
	{
		$filterChain->execute($container);

		$response = $container->getResponse();

		if(!$response->isContentMutable() || !($output = $response->getContent())) {
			return;
		}

		$rq = $this->getContext()->getRequest();

		$vm = $container->getValidationManager();

		$cfg = $rq->getAttributes('org.agavi.filter.FormPopulationFilter');

		$ot = $container->getOutputType();

		if(is_array($cfg['output_types']) && !in_array($ot->getName(), $cfg['output_types'])) {
			return;
		}

		if(is_array($cfg['populate']) || $cfg['populate'] instanceof AgaviParameterHolder) {
			$populate = $cfg['populate'];
		} elseif($cfg['populate'] === true || (in_array($rq->getMethod(), $cfg['methods']) && $cfg['populate'] !== false)) {
			$populate = $rq->getRequestData();
		} else {
			return;
		}

		$skip = null;
		if($cfg['skip'] instanceof AgaviParameterHolder) {
			$cfg['skip'] = $cfg['skip']->getParameters();
		} elseif($cfg['skip'] !== null && !is_array($cfg['skip'])) {
			$cfg['skip'] = null;
		}
		if($cfg['skip'] !== null && count($cfg['skip'])) {
			$skip = '/(\A' . str_replace('\[\]', '\[[^\]]*\]', implode('|\A', array_map('preg_quote', $cfg['skip']))) . ')/';
		}

		if($cfg['force_request_uri'] !== false) {
			$ruri = $cfg['force_request_uri'];
		} else {
			$ruri = $rq->getRequestUri();
		}
		if($cfg['force_request_url'] !== false) {
			$rurl = $cfg['force_request_url'];
		} else {
			$rurl = $rq->getUrl();
		}
		
		$errorMessageRules = array();
		if(isset($cfg['error_messages']) && is_array($cfg['error_messages'])) {
			$errorMessageRules = $cfg['error_messages'];
		}
		$fieldErrorMessageRules = $errorMessageRules;
		if(isset($cfg['field_error_messages']) && is_array($cfg['field_error_messages']) && count($cfg['field_error_messages'])) {
			$fieldErrorMessageRules = $cfg['field_error_messages'];
		}
		$multiFieldErrorMessageRules = $fieldErrorMessageRules;
		if(isset($cfg['multi_field_error_messages']) && is_array($cfg['multi_field_error_messages']) && count($cfg['multi_field_error_messages'])) {
			$multiFieldErrorMessageRules = $cfg['multi_field_error_messages'];
		}
		
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();

		$this->doc = new DOMDocument();

		$this->doc->substituteEntities = $cfg['dom_substitute_entities'];
		$this->doc->resolveExternals   = $cfg['dom_resolve_externals'];
		$this->doc->validateOnParse    = $cfg['dom_validate_on_parse'];
		$this->doc->preserveWhiteSpace = $cfg['dom_preserve_white_space'];
		$this->doc->formatOutput       = $cfg['dom_format_output'];

		$hasXmlProlog = false;
		if(preg_match('/^<\?xml[^\?]*\?>/', $output)) {
			$hasXmlProlog = true;
		} elseif(preg_match('/charset=(.+)\s*$/i', $ot->getParameter('http_headers[Content-Type]'), $matches)) {
			// add an XML prolog with the char encoding, works around issues with ISO-8859-1 etc
			$output = "<?xml version='1.0' encoding='" . $matches[1] . "' ?>\n" . $output;
		}

		$xhtml = (preg_match('/<!DOCTYPE[^>]+XHTML[^>]+/', $output) > 0 && strtolower($cfg['force_output_mode']) != 'html') || strtolower($cfg['force_output_mode']) == 'xhtml';
		if($xhtml && $cfg['parse_xhtml_as_xml']) {
			$this->doc->loadXML($output);
			$this->xpath = new DomXPath($this->doc);
			if($this->doc->documentElement && $this->doc->documentElement->namespaceURI) {
				$this->xpath->registerNamespace('html', $this->doc->documentElement->namespaceURI);
				$this->ns = 'html:';
			} else {
				$this->ns = '';
			}
		} else {
			$this->doc->loadHTML($output);
			$this->xpath = new DomXPath($this->doc);
			$this->ns = '';
		}

		if(libxml_get_last_error() !== false) {
			$errors = array();
			foreach(libxml_get_errors() as $error) {
				$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($luie);
			$emsg = sprintf(
				'Form Population Filter could not parse the document due to the following error%s: ' . "\n\n%s",
				count($errors) > 1 ? 's' : '',
				implode("\n", $errors)
			);
			if(AgaviConfig::get('core.use_logging') && $cfg['log_parse_errors']) {
				$lmsg = $emsg . "\n\nResponse content:\n\n" . $response->getContent();
				$lm = $this->context->getLoggerManager();
				$mc = $lm->getDefaultMessageClass();
				$m = new $mc($lmsg, $cfg['logging_severity']);
				$lm->log($m, $cfg['logging_logger']);
			}
			throw new AgaviParseException($emsg);
		}

		libxml_clear_errors();
		libxml_use_internal_errors($luie);

		$properXhtml = false;
		foreach($this->xpath->query('//' . $this->ns . 'head/' . $this->ns . 'meta') as $meta) {
			if(strtolower($meta->getAttribute('http-equiv')) == 'content-type') {
				if($this->doc->encoding === null) {
					if(preg_match('/charset=(.+)\s*$/i', $meta->getAttribute('content'), $matches)) {
						$this->doc->encoding = $matches[1];
					} else {
						$this->doc->encoding = "utf-8";
					}
				}
				if(strpos($meta->getAttribute('content'), 'application/xhtml+xml') !== false) {
					$properXhtml = true;
				}
				break;
			}
		}

		if(($encoding = $cfg['force_encoding']) === false) {
			if($this->doc->actualEncoding) {
				$encoding = $this->doc->actualEncoding;
			} elseif($this->doc->encoding) {
				$encoding = $this->doc->encoding;
			} else {
				$encoding = $this->doc->encoding = self::ENCODING_UTF_8;
			}
		} else {
			$this->doc->encoding = $encoding;
		}
		$encoding = strtolower($encoding);
		$utf8 = $encoding == self::ENCODING_UTF_8;
		if(!$utf8 && $encoding != self::ENCODING_ISO_8859_1 && !function_exists('iconv')) {
			throw new AgaviException('No iconv module available, input encoding "' . $encoding . '" cannot be handled.');
		}

		$base = $this->xpath->query('/' . $this->ns . 'html/' . $this->ns . 'head/' . $this->ns . 'base[@href]');
		if($base->length) {
			$baseHref = $base->item(0)->getAttribute('href');
		} else {
			$baseHref = $rq->getUrl();
		}
		$baseHref = substr($baseHref, 0, strrpos($baseHref, '/') + 1);
		
		$forms = array();
		if(is_array($populate)) {
			$query = array();
			foreach(array_keys($populate) as $id) {
				if(is_string($id)) {
					$query[] = '@id="' . $id . '"';
				}
			}
			if($query) {
				$forms = $this->xpath->query('//' . $this->ns . 'form[' . implode(' or ', $query) . ']');
			}
		} else {
			$forms = $this->xpath->query('//' . $this->ns . 'form[@action]');
		}
		foreach($forms as $form) {
			if($populate instanceof AgaviParameterHolder) {
				$action = preg_replace('/#.*$/', '', trim($form->getAttribute('action')));
				if(!(
					$action == $rurl ||
					(strpos($action, '/') === 0 && preg_replace(array('#/\./#', '#/\.$#', '#[^\./]+/\.\.(/|\z)#', '#/{2,}#'), array('/', '/', '', '/'), $action) == $ruri) ||
					$baseHref . preg_replace(array('#/\./#', '#/\.$#', '#[^\./]+/\.\.(/|\z)#', '#/{2,}#'), array('/', '/', '', '/'), $action) == $rurl
				)) {
					continue;
				}
				$p = $populate;
			} else {
				if(isset($populate[$form->getAttribute('id')])) {
					if($populate[$form->getAttribute('id')] instanceof AgaviParameterHolder) {
						$p = $populate[$form->getAttribute('id')];
					} elseif($populate[$form->getAttribute('id')] === true) {
						$p = $rq->getRequestData();
					} else {
						continue;
					}
				} else {
					continue;
				}
			}

			// our array for remembering foo[] field's indices
			$remember = array();

			// an array of all validation incidents; errors inserted for fields or multiple fields will be removed in here
			$allIncidents = $vm->getIncidents();
			
			// build the XPath query
			$query = 'descendant::' . $this->ns . 'textarea[@name] | descendant::' . $this->ns . 'select[@name] | descendant::' . $this->ns . 'input[@name and (not(@type) or @type="text" or (@type="checkbox" and not(contains(@name, "[]"))) or (@type="checkbox" and contains(@name, "[]") and @value) or @type="radio" or @type="password" or @type="file"';
			if($cfg['include_hidden_inputs']) {
				$query .= ' or @type="hidden"';
			}
			$query .= ')]';
			foreach($this->xpath->query($query, $form) as $element) {

				$pname = $name = $element->getAttribute('name');

				$multiple = $element->nodeName == 'select' && $element->hasAttribute('multiple');

				$checkValue = false;
				if($element->getAttribute('type') == 'checkbox' || $element->getAttribute('type') == 'radio') {
					if(($pos = strpos($pname, '[]')) && ($pos + 2 != strlen($pname))) {
						// foo[][3] checkboxes etc not possible, [] must occur only once and at the end
						continue;
					} elseif($pos !== false) {
						$checkValue = true;
						$pname = substr($pname, 0, $pos);
					}
				}
				if(preg_match_all('/([^\[]+)?(?:\[([^\]]*)\])/', $pname, $matches)) {
					$pname = $matches[1][0];

					if($multiple) {
						$count = count($matches[2]) - 1;
					} else {
						$count = count($matches[2]);
					}
					for($i = 0; $i < $count; $i++) {
						$val = $matches[2][$i];
						if((string)$matches[2][$i] === (string)(int)$matches[2][$i]) {
							$val = (int)$val;
						}
						if(!isset($remember[$pname])) {
							$add = ($val !== "" ? $val : 0);
							if(is_int($add)) {
								$remember[$pname] = $add;
							}
						} else {
							if($val !== "") {
								$add = $val;
								if(is_int($val) && $add > $remember[$pname]) {
									$remember[$pname] = $add;
								}
							} else {
								$add = ++$remember[$pname];
							}
						}
						$pname .= '[' . $add . ']';
					}
				}

				if(!$utf8) {
					$pname = $this->fromUtf8($pname, $encoding);
				}

				if($skip !== null && preg_match($skip, $pname . ($checkValue ? '[]' : ''))) {
					// skip field
					continue;
				}

				// there's an error with the element's name in the request? good. let's give the baby a class!
				if($vm->isFieldFailed($pname)) {
					// a collection of all elements that need an error class
					$errorClassElements = array();
					// the element itself of course
					$errorClassElements[] = $element;
					// all implicit labels
					foreach($this->xpath->query('ancestor::' . $this->ns . 'label[not(@for)]', $element) as $label) {
						$errorClassElements[] = $label;
					}
					// and all explicit labels
					if(($id = $element->getAttribute('id')) != '') {
						foreach($this->xpath->query('descendant::' . $this->ns . 'label[@for="' . $id . '"]', $form) as $label) {
							$errorClassElements[] = $label;
						}
					}
					
					// now loop over all those elements and assign the class
					foreach($errorClassElements as $errorClassElement) {
						// go over all the elements in the error class map
						foreach($cfg['error_class_map'] as $xpathExpression => $errorClassName) {
							$errorClassTest = $this->xpath->query(AgaviToolkit::expandVariables($xpathExpression, array('htmlnsPrefix' => $this->ns)), $errorClassElement);
							if($errorClassTest && $errorClassTest->length) {
								$errorClassElement->setAttribute('class', preg_replace('/\s*$/', ' ' . $errorClassName, $errorClassElement->getAttribute('class')));
								// and break the foreach, our expression matched after all - no need to look further
								break;
							}
						}
					}
					
					// up next: the error messages
					$fieldIncidents = array();
					$multiFieldIncidents = array();
					foreach($vm->getFieldIncidents($pname) as $incident) {
						if(($incidentKey = array_search($incident, $allIncidents, true)) !== false) {
							if(count($incident->getFields()) > 1) {
								$multiFieldIncidents[] = $incident;
							} else {
								$fieldIncidents[] = $incident;
							}
							// remove it from the list of all incidents
							unset($allIncidents[$incidentKey]);
						}
					}
					// 1) insert error messages that are specific to this field
					if(!$this->insertErrorMessages($element, $fieldErrorMessageRules, $fieldIncidents)) {
						$allIncidents = array_merge($allIncidents, $fieldIncidents);
					}
					// 2) insert error messages that belong to multiple fields (including this one), if that message was not inserted before
					if(!$this->insertErrorMessages($element, $multiFieldErrorMessageRules, $multiFieldIncidents)) {
						$allIncidents = array_merge($allIncidents, $multiFieldIncidents);
					}
				}

				$value = $p->getParameter($pname);

				if(is_array($value) && !($element->nodeName == 'select' || $checkValue)) {
					// name didn't match exactly. skip.
					continue;
				}

				if(!$utf8) {
					$value = $this->toUtf8($value, $encoding);
				} else {
					if(is_array($value)) {
						$value = array_map('strval', $value);
					} else {
						$value = (string) $value;
					}
				}

				if($element->nodeName == 'input') {

					if(!$element->hasAttribute('type') || $element->getAttribute('type') == 'text' || $element->getAttribute('type') == 'hidden') {

						// text inputs
						$element->removeAttribute('value');
						if($p->hasParameter($pname)) {
							$element->setAttribute('value', $value);
						}

					} elseif($element->getAttribute('type') == 'checkbox' || $element->getAttribute('type') == 'radio') {

						// checkboxes and radios
						$element->removeAttribute('checked');

						if($checkValue && is_array($value)) {
							$eValue = $element->getAttribute('value');
							if(!$utf8) {
								$eValue = fromUtf8($eValue, $encoding);
							}
							if(!in_array($eValue, $value)) {
								continue;
							} else {
								$element->setAttribute('checked', 'checked');
							}
						} elseif($p->hasParameter($pname) && (($element->hasAttribute('value') && $element->getAttribute('value') == $value) || (!$element->hasAttribute('value') && $p->getParameter($pname)))) {
							$element->setAttribute('checked', 'checked');
						}

					} elseif($element->getAttribute('type') == 'password') {

						// passwords
						$element->removeAttribute('value');
						if($cfg['include_password_inputs'] && $p->hasParameter($pname)) {
							$element->setAttribute('value', $value);
						}
					}

				} elseif($element->nodeName == 'select') {
					// select elements
					// yes, we still use XPath because there could be OPTGROUPs
					foreach($this->xpath->query('descendant::' . $this->ns . 'option', $element) as $option) {
						$option->removeAttribute('selected');
						if($p->hasParameter($pname) && ($option->getAttribute('value') === $value || ($multiple && is_array($value) && in_array($option->getAttribute('value'), $value)))) {
							$option->setAttribute('selected', 'selected');
						}
					}

				} elseif($element->nodeName == 'textarea') {

					// textareas
					foreach($element->childNodes as $cn) {
						// remove all child nodes (= text nodes)
						$element->removeChild($cn);
					}
					// append a new text node
					if($xhtml && $properXhtml) {
						$element->appendChild($this->doc->createCDATASection($value));
					} else {
						$element->appendChild($this->doc->createTextNode($value));
					}
				}

			}

			// now output the remaining incidents
			if(!$this->insertErrorMessages($form, $errorMessageRules, $allIncidents)) {
				$rq->setAttribute('lolz', $allIncidents, 'org.agavi.filter.FormPopulationFilter');
			}
		}
		if($xhtml) {
			$fiveTwo = version_compare(PHP_VERSION, '5.2', 'ge');
			$firstError = null;

			if(!$cfg['parse_xhtml_as_xml']) {
				// workaround for a bug in dom or something that results in two xmlns attributes being generated for the <html> element
				foreach($this->xpath->query('//html') as $html) {
					$html->removeAttribute('xmlns');
				}
			}
			$out = $this->doc->saveXML();
			if((!$cfg['parse_xhtml_as_xml'] || !$properXhtml) && $cfg['cdata_fix']) {
				// these are ugly fixes so inline style and script blocks still work. better don't use them with XHTML to avoid trouble
				$out = preg_replace('/<style([^>]*)>\s*<!\[CDATA\[/iU' . ($utf8 ? 'u' : ''), '<style$1><!--/*--><![CDATA[/*><!--*/', $out);
				if(!$firstError && $fiveTwo) {
					$firstError = preg_last_error();
				}
				$out = preg_replace('/\]\]><\/style>/iU' . ($utf8 ? 'u' : ''), '/*]]>*/--></style>', $out);
				if(!$firstError && $fiveTwo) {
					$firstError = preg_last_error();
				}
				$out = preg_replace('/<script([^>]*)>\s*<!\[CDATA\[/iU' . ($utf8 ? 'u' : ''), '<script$1><!--//--><![CDATA[//><!--', $out);
				if(!$firstError && $fiveTwo) {
					$firstError = preg_last_error();
				}
				$out = preg_replace('/\]\]><\/script>/iU' . ($utf8 ? 'u' : ''), '//--><!]]></script>', $out);
				if(!$firstError && $fiveTwo) {
					$firstError = preg_last_error();
				}
			}
			if($cfg['remove_auto_xml_prolog'] && !$hasXmlProlog) {
				// there was no xml prolog in the document before, so we remove the one generated by DOM now
				$out = preg_replace('/<\?xml.*?\?>\s+/iU' . ($utf8 ? 'u' : ''), '', $out);
				if(!$firstError && $fiveTwo) {
					$firstError = preg_last_error();
				}
			} elseif(!$cfg['parse_xhtml_as_xml']) {
				// yes, DOM sucks and inserts another XML prolog _after_ the DOCTYPE... and it has two question marks at the end, not one, don't ask me why
				$out = preg_replace('/<\?xml.*?\?\?>\s+/iU' . ($utf8 ? 'u' : ''), '', $out);
				if(!$firstError && $fiveTwo) {
					$firstError = preg_last_error();
				}
			}

			$hasError = false;
			if($fiveTwo) {
				$hasError = $firstError;
			} else {
				$hasError = $out == '';
			}

			if($hasError) {
				$error = "Form Population Filter encountered an error while performing final regular expression replaces on the output.\n";
				// the preg_replaces failed and produced an empty string. let's find out why
				if($fiveTwo) {
					$error .= "The error reported by preg_last_error() indicates that ";
					switch($firstError) {
						case PREG_BAD_UTF8_ERROR:
							$error .= "the input contained malformed UTF-8 data.";
							break;
						case PREG_RECURSION_LIMIT_ERROR:
							$error .= "the recursion limit (defined by \"pcre.recursion_limit\") was hit. This shouldn't happen unless you changed that limit yourself in php.ini or using ini_set(). If the problem is not on your end, please file a bug report with a reproduce case on the Agavi issue tracker or drop by on the IRC support channel.";
							break;
						case PREG_BACKTRACK_LIMIT_ERROR:
							$error .= "the backtrack limit (defined by \"pcre.backtrack_limit\") was hit. This shouldn't happen unless you changed that limit yourself in php.ini or using ini_set(). If the problem is not on your end, please file a bug report with a reproduce case on the Agavi issue tracker or drop by on the IRC support channel.";
							break;
						case PREG_INTERNAL_ERROR:
						default:
							$error .= "an internal PCRE error occured. As a quick countermeasure, try to upgrade PHP (and the bundled PCRE) as well as libxml (yes!) to the latest versions to see if the problem goes away. If the issue persists, file a bug report with a reproduce case on the Agavi issue tracker or drop by on the IRC support channel.";
					}
				} else {
					$error .= "Unfortunately, no error information is available due to your PHP version being lower than 5.2.";
					if($utf8) {
						$error .= "\nHowever, your document is encoded as UTF-8, and an empty result from preg_replace() typically means that the input contained malformed UTF-8 data.";
					}
				}
				throw new AgaviException($error);
			}

			$response->setContent($out);
		} else {
			$response->setContent($this->doc->saveHTML());
		}
		unset($this->xpath);
		unset($this->doc);
	}
	
	/**
	 * Insert the error messages from the given incidents into the given element
	 * using the given rules.
	 *
	 * @param      DOMElement The element to work on.
	 * @param      array      An array of insertion rules
	 * @param      array      An array of AgaviValidationIncidents.
	 *
	 * @return     bool Whether or not the inserts were successful.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function insertErrorMessages(DOMElement $element, array $rules, array $incidents)
	{
		$errorMessages = array();
		foreach($incidents as $incident) {
			foreach($incident->getErrors() as $error) {
				$errorMessages[] = $error->getMessage();
			}
		}
		
		$luie = libxml_use_internal_errors(true);
		libxml_clear_errors();
		
		$insertSuccessful = false;
		foreach($rules as $xpathExpression => $errorMessageInfo) {
			$targets = $this->xpath->query(AgaviToolkit::expandVariables($xpathExpression, array('htmlnsPrefix' => $this->ns)), $element);
			
			if(!$targets || !$targets->length) {
				continue;
			}
			
			if(!is_array($errorMessageInfo)) {
				$errorMessageInfo = array('markup' => $errorMessageInfo);
			}
			if(isset($errorMessageInfo['markup'])) {
				$errorMarkup = $errorMessageInfo['markup'];
			} else {
				throw new AgaviException('Form Population Filter was unable to insert an error message into the document using the XPath expression "' . $xpathExpression . '" because the element information did not contain markup to use.');
			}
			if(isset($errorMessageInfo['location'])) {
				$errorLocation = $errorMessageInfo['location'];
			} else {
				$errorLocation = 'after';
			}
			if(isset($errorMessageInfo['container'])) {
				$errorContainer = $errorMessageInfo['container'];
			} else {
				$errorContainer = null;
			}
			
			$errorElements = array();
			
			foreach($errorMessages as $errorMessage) {
				if(is_string($errorMarkup)) {
					// it's a string with the HTML to insert
					// %s is the placeholder in the HTML for the error message
					$errorElement = $this->doc->createDocumentFragment();
					$errorElement->appendXML(
						AgaviToolkit::expandVariables(
							$errorMarkup,
							array(
								'elementId'    => $element->getAttribute('id'),
								'elementName'  => $element->getAttribute('name'),
								'errorMessage' => $errorMessage,
							)
						)
					);
				} elseif(is_callable($errorMarkup)) {
					// it's a callback we can use to get a DOMElement
					// we give it the element as the first, and the error message as the second argument
					$errorElement = call_user_func($errorMarkup, $element, $errorMessage);
					$this->doc->importNode($errorElement, true);
				} else {
					throw new AgaviException('Form Population Filter was unable to insert an error message into the document using the XPath expression "' . $xpathExpression . '" because the element information could not be evaluated as an XML/HTML fragment or as a PHP callback.');
				}
				
				$errorElements[] = $errorElement;
			}
			
			if($errorContainer) {
				// we have an error container.
				// that means that instead of inserting each message element, we add the messages into the container
				// then, the container is the only element scheduled for insertion
				$errorStrings = array();
				// add all error XML strings to an array
				foreach($errorElements as $errorElement) {
					$errorStrings[] = $errorElement->ownerDocument->saveXML($errorElement);
				}
				
				// create the container element and replace the errors placeholder in the container
				if(is_string($errorContainer)) {
					// it's a string with the HTML to insert
					// %s is the placeholder in the HTML for the error message
					$containerElement = $this->doc->createDocumentFragment();
					$containerElement->appendXML(
						AgaviToolkit::expandVariables(
							$errorContainer,
							array(
								'elementId'     => $element->getAttribute('id'),
								'elementName'   => $element->getAttribute('name'),
								'errorMessages' => implode("\n", $errorStrings),
							)
						)
					);
				} elseif(is_callable($errorContainer)) {
					// it's a callback we can use to get a DOMElement
					// we give it the element as the first, and the error messages array(!) as the second argument
					$containerElement = call_user_func($errorContainer, $element, $errorStrings);
					$this->doc->importNode($containerElement, true);
				} else {
					throw new AgaviException('Form Population Filter was unable to insert an error message container into the document using the XPath expression "' . $xpathExpression . '" because the element information could not be evaluated as an XML/HTML fragment or as a PHP callback.');
				}
				
				// and now the trick: set the error container element as the only one in the errorElements variable
				// that way, it's going to get inserted for us as if it were a normal error message element, using the location specified
				$errorElements = array($containerElement);
			}
			
			if(libxml_get_last_error() !== false) {
				$errors = array();
				foreach(libxml_get_errors() as $error) {
					$errors[] = sprintf("Line %d: %s", $error->line, $error->message);
				}
				libxml_clear_errors();
				libxml_use_internal_errors($luie);
				$emsg = sprintf(
					'Form Population Filter was unable to insert an error message into the document using the XPath expression "%s" due to the following error%s: ' . "\n\n%s",
					$xpathExpression,
					count($errors) > 1 ? 's' : '',
					implode("\n", $errors)
				);
				throw new AgaviParseException($emsg);
			}
			
			foreach($errorElements as $errorElement) {
				foreach($targets as $target) {
					if($errorLocation == 'before') {
						$target->parentNode->insertBefore($errorElement, $target);
					} elseif($errorLocation == 'after') {
						// check if there is a following sibling, then insert before that one
						// if not, append to parent
						if($target->nextSibling) {
							$target->parentNode->insertBefore($errorElement, $target->nextSibling);
						} else {
							$target->parentNode->appendChild($errorElement);
						}
					} elseif($errorLocation == 'replace') {
						$target->parentNode->replaceChild($errorElement, $target);
					} else {
						$target->appendChild($errorElement);
					}
				}
			}
			
			// and break the foreach, our expression matched after all - no need to look further
			$insertSuccessful = true;
			break;
		}
		
		libxml_clear_errors();
		libxml_use_internal_errors($luie);
		
		return $insertSuccessful;
	}
	
	/**
	 * Encode given value to UTF-8
	 *
	 * @param      mixed  The value to convert (can be an array).
	 * @param      string The encoding of the value.
	 *
	 * @return     mixed  The converted value.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function toUtf8($value, $encoding = self::ENCODING_ISO_8859_1)
	{
		if($encoding == self::ENCODING_ISO_8859_1) {
			if(is_array($value)) {
				foreach($value as &$val) {
					$val = $this->toUtf8($val, $encoding);
				}
			} else {
				$value = utf8_encode($value);
			}
		} else {
			if(is_array($value)) {
				foreach($value as &$val) {
					$val = $this->toUtf8($val, $encoding);
				}
			} else {
				$value = iconv($encoding, self::ENCODING_UTF_8, $value);
			}
		}
		
		return $value;
	}
	
	/**
	 * Decode given value from UTF-8
	 *
	 * @param      mixed  The value to convert (can be an array).
	 * @param      string The encoding of the value.
	 *
	 * @return     mixed  The converted value.
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function fromUtf8($value, $encoding = self::ENCODING_ISO_8859_1)
	{
		if($encoding == self::ENCODING_ISO_8859_1) {
			if(is_array($value)) {
				foreach($value as &$val) {
					$val = $this->fromUtf8($val, $encoding);
				}
			} else {
				$value = utf8_decode($value);
			}
		} else {
			if(is_array($value)) {
				foreach($value as &$val) {
					$val = $this->fromUtf8($val, $encoding);
				}
			} else {
				$value = iconv(self::ENCODING_UTF_8, $encoding, $value);
			}
		}
		
		return $value;
	}

	/**
	 * Initialize this filter.
	 *
	 * @param      AgaviContext The current application context.
	 * @param      array        An associative array of initialization parameters.
	 *
	 * @throws     <b>AgaviFilterException</b> If an error occurs during
	 *                                         initialization
	 *
	 * @author     David Zülke <dz@bitxtender.com>
	 * @since      0.11.0
	 */
	public function initialize(AgaviContext $context, array $parameters = array())
	{
		// set defaults
		$this->setParameters(array(
			'methods'                    => array(),
			'output_types'               => null,
			
			'populate'                   => null,
			'skip'                       => null,
			'include_hidden_inputs'      => true,
			'include_password_inputs'    => false,
			
			'force_output_mode'          => false,
			'force_encoding'             => false,
			'force_request_uri'          => false,
			'force_request_url'          => false,
			'cdata_fix'                  => true,
			'parse_xhtml_as_xml'         => true,
			'remove_auto_xml_prolog'     => true,
			'dom_substitute_entities'    => false,
			'dom_resolve_externals'      => false,
			'dom_validate_on_parse'      => false,
			'dom_preserve_white_space'   => true,
			'dom_format_output'          => false,
			
			'error_class'                => 'error',
			'error_class_map'            => array(),
			'error_messages'             => array(),
			'field_error_messages'       => array(),
			'multi_field_error_messages' => array(),
			
			'log_parse_errors'           => true,
			'logging_severity'           => AgaviLogger::FATAL,
			'logging_logger'             => null,
		));
		
		// initialize parent
		parent::initialize($context, $parameters);

		// and "clean up" some of the params just in case the user messed up
		
		$errorClassMap = (array) $this->getParameter('error_class_map');
		// append a match-all expression to the map, which assigns the default error class
		$errorClassMap['self::${htmlnsPrefix}*'] = $this->getParameter('error_class');
		$this->setParameter('error_class_map', $errorClassMap);
		
		$this->setParameter('methods', (array) $this->getParameter('methods'));
		
		if($ot = $this->getParameter('output_types')) {
			$this->setParameter('output_types', (array) $ot);
		}
		
		// and now copy all that to the request namespace so it can all be modified at runtime, not just overwritten
		$this->context->getRequest()->setAttributes($this->getParameters(), 'org.agavi.filter.FormPopulationFilter');
	}
}

?>