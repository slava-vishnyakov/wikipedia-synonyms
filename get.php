<?php

use SlavaVishnyakov\MapReduce\MapReduceMemory;
use SlavaVishnyakov\MapReduce\MapReduceProcess;

require 'vendor/autoload.php';

// https://dumps.wikimedia.org/enwiki/20180401/

$mr = new MapReduceProcess();

function wikiTexts($filename)
{
    $xml = new XMLReader();

    $xml->open('php://stdin');

    while (@$xml->read()) {
        if ($xml->name == 'text') {
            yield $xml->readInnerXml();
        }
    }
}

function wikiAnchorsFromText($text)
{
    preg_match_all('{\[\[([^#\|\]]+)\|([^\|\]]+)\]\]}', $text, $m);
    $already = [];
    foreach ($m[0] as $i => $_) {
        $anchor = trim(mb_strtolower($m[2][$i]));
        if (mb_strlen($anchor) <= 25) {
            $topic = trim($m[1][$i]);
            if ($topic && $anchor) {
                if (!$already["$topic $anchor"]) {
                    $already["$topic $anchor"] = 1; // send only once per article
                    yield [$topic, $anchor];
                }
            }
        }
    }
}

foreach (wikiTexts('php://stdin') as $text) {
    foreach (wikiAnchorsFromText($text) as [$topic, $anchor]) {
        $mr->send($topic, $anchor);
    }
}

$mr2 = new MapReduceProcess();
foreach ($mr->iter() as $key => $values) {
    if (count($values) > 1) {
        foreach ($values as $w1) {
            foreach ($values as $w2) {
                if ($w1 != $w2) {
                    $mr2->send($w1, $w2);
                }
            }
        }
    }
}

foreach ($mr2->iter() as $key => $values) {
    $values = array_count_values($values);
    arsort($values);
    print json_encode([$key, $values], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
}