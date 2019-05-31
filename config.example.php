<?php

// This should be the value of $wgSecretKey from the server you want to access
// It will be some really long alphanumeric string
$secretKey = 'abcdefghiJkLmNoPQrStUvWxYz0192948576ABCDEfGHiJkLmNoPQrStUVwxYz12';

// This should be the file path to the MediaWiki source code on your computer.
// It does not have to be a working copy of MediaWiki that is serving pages
// anywhere. It just needs to be the source code. It should probably be as close
// as possible to the same version as is running on the server you want to
// access, otherwise it's possible things could get out of sync.
$pathToMediaWiki = 'C:/Users/James/Desktop/wiki/mediawiki-core'; // Windows
$pathToMediaWiki = '/home/james/mediawiki-core';                 // Unix-like

// How long until expiration. Default is 24 hours * 60 minutes * 60 seconds, or
// one day.
$expirationIn = 24*60*60;
