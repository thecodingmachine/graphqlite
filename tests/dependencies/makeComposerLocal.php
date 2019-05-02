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

$composer['require']['thecodingmachine/graphqlite'] = '4.0.x-dev';

file_put_contents($file, json_encode($composer));
