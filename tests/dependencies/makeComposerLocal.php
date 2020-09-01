#!/bin/env php
<?php

$composerBundlePath = $argv[1];

//fetch the dev-master alias from the local graphqlite composer
$composerGraphqlite = json_decode(file_get_contents(__DIR__.'/copy/composer.json'), true);

$masterAlias = $composerGraphqlite['extra']['branch-alias']['dev-master'];

//edit the bundle composer to use the local graphqlite
$composerBundle = json_decode(file_get_contents($composerBundlePath), true);

$composerBundle['repositories'] = [
    [
        'type' => 'path',
        'url' => '../copy/'
    ]
];

$composerBundle['require']['thecodingmachine/graphqlite'] = $masterAlias;

file_put_contents($composerBundlePath, json_encode($composerBundle));