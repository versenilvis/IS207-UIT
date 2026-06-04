<?php
// scripts/parser.php
// usage: php scripts/parser.php --mode [exam|answer] --input <file> --out <file>

// parse cli args
$opts = getopt('', ['mode:', 'input:', 'out:']);
$mode  = $opts['mode']  ?? 'exam';
$input = $opts['input'] ?? '';
$out   = $opts['out']   ?? '';

if (empty($input) || empty($out)) {
	fwrite(STDERR, "usage: php parser.php --mode [exam|answer] --input <html> --out <json>\n");
	exit(1);
}

if (!file_exists($input)) {
	fwrite(STDERR, "file not found: {$input}\n");
	exit(1);
}

// load html using domdocument
$doc = new DOMDocument();
// suppress libxml warnings about html5 elements
libxml_use_internal_errors(true);
$htmlContent = file_get_contents($input);
// detect encoding or force utf-8 prefix
if (!str_contains($htmlContent, '<?xml encoding=')) {
	$htmlContent = '<?xml encoding="UTF-8">' . $htmlContent;
}
$doc->loadHTML($htmlContent);
libxml_clear_errors();

$xpath = new DOMXPath($doc);

// helper functions
function findClass(DOMXPath $xpath, string $class, ?DOMNode $context = null): DOMNodeList {
	$query = "descendant-or-self::*[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]";
	return $context ? $xpath->query($query, $context) : $xpath->query($query);
}

function findAncestorClass(DOMXPath $xpath, DOMNode $node, string $class): ?DOMElement {
	$parent = $node->parentNode;
	while ($parent) {
		if ($parent instanceof DOMElement) {
			$classes = explode(' ', $parent->getAttribute('class') ?? '');
			if (in_array($class, $classes)) {
				return $parent;
			}
		}
		$parent = $parent->parentNode;
	}
	return null;
}

function getInnerHtml(DOMNode $node): string {
	$html = '';
	foreach ($node->childNodes as $child) {
		$html .= $node->ownerDocument->saveHTML($child);
	}
	return trim($html);
}

function parseQuestion(DOMXPath $xpath, DOMElement $s, ?string $examAudioUrl, bool &$isFirstQuestion): array {
	$q = ['correct_answer' => 'A'];

	$numNode = findClass($xpath, 'question-num', $s)->item(0);
	$numText = $numNode ? trim($numNode->textContent) : '';
	$numText = rtrim($numText, '.');
	$num = (int)$numText;
	if ($num > 0) {
		$q['question_number'] = $num;
	}

	$contentNode = findClass($xpath, 'question-content', $s)->item(0);
	$content = $contentNode ? trim($contentNode->textContent) : '';
	if (str_contains($content, 'Mark your answer')) {
		$content = '';
	}
	$q['content'] = $content;

	// image url
	$imgNodes = $s->getElementsByTagName('img');
	if ($imgNodes->length > 0) {
		$src = $imgNodes->item(0)->getAttribute('src');
		if ($src) {
			$q['image_url'] = basename($src);
		}
	}

	// options
	$options = [];
	$optNodes = findClass($xpath, 'option', $s);
	foreach ($optNodes as $opt) {
		$input = $xpath->query('descendant::input', $opt)->item(0);
		$label = $input ? trim($input->getAttribute('value') ?? '') : '';
		$labelNode = $xpath->query('descendant::label', $opt)->item(0);
		$labelText = $labelNode ? $labelNode->textContent : '';
		$cleaned = trim($labelText);
		if ($label) {
			$prefix = "({$label})";
			if (str_starts_with($cleaned, $prefix)) {
				$cleaned = trim(substr($cleaned, strlen($prefix)));
			}
		}

		// fallback
		if (empty($label)) {
			foreach ($opt->childNodes as $child) {
				if ($child->nodeType === XML_TEXT_NODE) {
					$text = trim($child->textContent);
					if (preg_match('/\(([A-D])\)/', $text, $matches)) {
						$label = $matches[1];
						break;
					} elseif (strlen($text) === 1 && $text >= 'A' && $text <= 'D') {
						$label = $text;
						break;
					}
				}
			}
			if (!empty($label)) {
				$optContentNode = findClass($xpath, 'option-content', $opt)->item(0);
				$cleaned = $optContentNode ? trim($optContentNode->textContent) : '';
			}
		}

		if (!empty($label)) {
			$options[] = [
				'label' => $label,
				'content' => $cleaned
			];
		}
	}
	$q['options'] = $options;

	if ($isFirstQuestion && !empty($examAudioUrl)) {
		$q['audio_url'] = $examAudioUrl;
		$isFirstQuestion = false;
	}

	return $q;
}

if ($mode === 'answer') {
	$titleNode = $doc->getElementsByTagName('title')->item(0);
	$title = $titleNode ? trim($titleNode->textContent) : '';

	$answers = [];
	$mcqNodes = findClass($xpath, 'mcq-wrapper');

	foreach ($mcqNodes as $s) {
		$numNode = findClass($xpath, 'question-num', $s)->item(0);
		$numText = $numNode ? trim($numNode->textContent) : '';
		$numText = rtrim($numText, '.');
		$num = (int)$numText;
		if ($num <= 0) {
			continue;
		}

		// check passage translation, english transcript and audio
		$passageTrans = '';
		$passageEng = '';
		$passageAudio = '';
		$parentMCQG = findAncestorClass($xpath, $s, 'mcqg-wrapper');
		if ($parentMCQG) {
			$viDivNode = $xpath->query('descendant::div[contains(concat(" ", normalize-space(@class), " "), " reading-text-wrapper ") and contains(concat(" ", normalize-space(@class), " "), " text-vi ")]/div', $parentMCQG)->item(0);
			if ($viDivNode) {
				$passageTrans = getInnerHtml($viDivNode);
			}
			$enDivNode = $xpath->query('descendant::div[contains(concat(" ", normalize-space(@class), " "), " reading-text-wrapper ") and contains(concat(" ", normalize-space(@class), " "), " text-en ")]/div', $parentMCQG)->item(0);
			if ($enDivNode) {
				$passageEng = getInnerHtml($enDivNode);
			}
			$pAudioNode = $xpath->query('descendant::audio//source', $parentMCQG)->item(0);
			if ($pAudioNode) {
				$src = $pAudioNode->getAttribute('src');
				if ($src) {
					$passageAudio = trim($src);
					if (str_starts_with($passageAudio, '/')) {
						$passageAudio = 'https://tienganhmoingay.com' . $passageAudio;
					}
				}
			}
		}

		// check question-level audio
		$questionAudio = '';
		$qAudioNode = $xpath->query('descendant::audio//source', $s)->item(0);
		if ($qAudioNode) {
			$src = $qAudioNode->getAttribute('src');
			if ($src) {
				$questionAudio = trim($src);
				if (str_starts_with($questionAudio, '/')) {
					$questionAudio = 'https://tienganhmoingay.com' . $questionAudio;
				}
			}
		}

		$correctLabel = '';
		$optionAnswers = [];
		$optNodes = findClass($xpath, 'option', $s);
		foreach ($optNodes as $opt) {
			if (!$opt instanceof DOMElement) {
				continue;
			}
			$label = '';
			foreach ($opt->childNodes as $child) {
				if ($child->nodeType === XML_TEXT_NODE) {
					$text = trim($child->textContent);
					if (preg_match('/\(([A-D])\)/', $text, $matches)) {
						$label = $matches[1];
						break;
					} elseif (strlen($text) === 1 && $text >= 'A' && $text <= 'D') {
						$label = $text;
						break;
					}
				}
			}

			if (empty($label)) {
				continue;
			}

			$transNode = findClass($xpath, 'option-translation', $opt)->item(0);
			$translation = $transNode ? trim($transNode->textContent) : '';

			$optionAnswers[] = [
				'label' => $label,
				'translation' => $translation
			];

			$class = $opt->getAttribute('class') ?? '';
			if (str_contains($class, 'correct-option')) {
				$correctLabel = $label;
			}
		}

		if (!empty($correctLabel)) {
			$entry = [
				'question_number' => $num,
				'correct_answer' => $correctLabel
			];
			if (!empty($passageTrans)) {
				$entry['passage_translation'] = $passageTrans;
			}
			if (!empty($passageEng)) {
				$entry['passage_english'] = $passageEng;
			}
			if (!empty($passageAudio)) {
				$entry['passage_audio'] = $passageAudio;
			}
			if (!empty($questionAudio)) {
				$entry['audio_url'] = $questionAudio;
			}
			$entry['options'] = $optionAnswers;
			$answers[] = $entry;
		}
	}

	$result = [
		'exam_title' => $title,
		'answers' => $answers
	];
} else {
	// exam mode
	$duration = 2700;
	$audioUrl = '';
	$scripts = $doc->getElementsByTagName('script');
	foreach ($scripts as $script) {
		$text = $script->textContent;
		if (preg_match('/testDuration\s*=\s*(\d+)/', $text, $matches)) {
			$duration = (int)$matches[1];
		}
		if (preg_match('/listeningAudio\s*=\s*["\']([^"\']+)["\']/', $text, $matches)) {
			$audioUrl = basename($matches[1]);
		}
	}

	if (empty($audioUrl)) {
		$audioSources = $xpath->query('//audio[@id="full-test-audio"]//source');
		if ($audioSources->length > 0) {
			$src = $audioSources->item(0)->getAttribute('src');
			if ($src) {
				$audioUrl = basename($src);
			}
		}
	}

	$titleNode = $doc->getElementsByTagName('title')->item(0);
	$title = $titleNode ? trim($titleNode->textContent) : 'TOEIC Exam';

	$parts = [];
	$partIDs = ['part-1', 'part-2', 'part-3', 'part-4', 'part-5', 'part-6', 'part-7'];
	$isFirstQuestion = true;

	foreach ($partIDs as $partID) {
		$section = $xpath->query("//section[@id='{$partID}']")->item(0);
		if (!$section) {
			continue;
		}

		$partNum = (int)str_replace('part-', '', $partID);
		$partData = ['part_number' => $partNum];

		if (in_array($partNum, [1, 2, 5])) {
			$questions = [];
			$mcqNodes = findClass($xpath, 'mcq-wrapper', $section);
			foreach ($mcqNodes as $s) {
				$q = parseQuestion($xpath, $s, null, $isFirstQuestion);
				if (isset($q['question_number'])) {
					$questions[] = $q;
				}
			}
			$partData['questions'] = $questions;
		} else {
			$passages = [];
			$mcqgNodes = findClass($xpath, 'mcqg-wrapper', $section);
			foreach ($mcqgNodes as $s) {
				if (!$s instanceof DOMElement) {
					continue;
				}
				$passage = [];
				$questions = [];
				$mcqNodes = findClass($xpath, 'mcq-wrapper', $s);
				foreach ($mcqNodes as $qSel) {
					$q = parseQuestion($xpath, $qSel, null, $isFirstQuestion);
					if (isset($q['question_number'])) {
						$questions[] = $q;
					}
				}

				if (empty($questions)) {
					continue;
				}
				$passage['questions'] = $questions;

				// passage image
				$imgNodes = $s->getElementsByTagName('img');
				if ($imgNodes->length > 0) {
					$src = $imgNodes->item(0)->getAttribute('src');
					if ($src) {
						$passage['image_url'] = basename($src);
					}
				}

				// passage content (part 6, 7)
				if (in_array($partNum, [6, 7])) {
					$pContentNode = $xpath->query('descendant::*[contains(concat(" ", normalize-space(@class), " "), " passage-content ") or contains(concat(" ", normalize-space(@class), " "), " reading-passage ") or self::article]', $s)->item(0);
					if ($pContentNode) {
						$passage['content'] = trim($pContentNode->textContent);
					}
				}

				if (empty($passage['content'])) {
					$nums = array_column($questions, 'question_number');
					$min = min($nums);
					$max = max($nums);
					$passage['content'] = "Questions {$min} - {$max}:";
				}

				$isFirstQuestion = false;

				$passages[] = $passage;
			}
			$partData['passages'] = $passages;
		}

		$parts[] = $partData;
	}

	$result = [
		'title' => $title,
		'duration' => $duration,
		'parts' => $parts
	];
	if (!empty($audioUrl)) {
		$result['audio_url'] = $audioUrl;
	}
}

file_put_contents($out, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✓ parsed [{$mode}] → {$out}\n";
