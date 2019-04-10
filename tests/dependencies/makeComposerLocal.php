#!/bin/env php
<?php

$file = $argv[1];

$composer = json_decode(file_get_contents($file), true);

$composer['repositories'] = [
    [
        'type' => 'path',
        'url' => '../copy/'
    ]
];

file_put_contents($file, json_encode($composer));
