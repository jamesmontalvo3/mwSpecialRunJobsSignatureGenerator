<?php

if ( ! isset( $argv[1] ) ) {
	echo 'You must pass at least one parameter into this script: the type of job to run.';
	exit( 1 ); // exit with bad status because user made an error
}
elseif ( $argv[1] === '-h' || $argv[1] === '--help' ) {
	echo <<<OUTPUT
sig-gen.php generates the 'signature' field for a POST request to
Special:RunJobs based upon the exact parameters POSTed, using the value of the
server's $wgSecretKey as the shared secret between you and the server. Of course
you need to know the value of $wgSecretKey for this to work, and you must add it
to config.php

Usage: php sig-gen.php [JOBTYPE] [MAXJOBS] [MAXTIME]

JOBTYPE: (REQUIRED) Value of `job_cmd` column in MW `job` SQL table.
         Examples: replaceText, recentChangesUpdate, htmlCacheUpdate
MAXJOBS: (optional, default 1) The maximum number of jobs to run
MAXTIME: (optional, default 30 seconds) The maximum amount of time to run in
         seconds. Note that this may be limited by the server's timeout time.

OUTPUT;
	exit( 0 ); // exit with good status since help was requested
}

$jobType = $argv[1];
$maxJobs = isset( $argv[2] ) ? $argv[2] : 1;  // num jobs to run, default 1
$maxTime = isset( $argv[3] ) ? $argv[3] : 30; // num seconds to run, default 30

$configFile = __DIR__ . '/config.php';

if ( ! is_file( $configFile ) ) {
	echo 'You must create a config.php file. Copy config.example.php';
	exit( 1 ); // exit with bad status because user hasn't completed config
}
else {
	require_once $configFile;
}

$required = [
	'secretKey',
	'pathToMediaWiki',
	'expirationIn',
];
$missing = [];
foreach ( $required as $req ) {
	if ( ! isset( $GLOBALS[$req] ) ) {
		$missing[] = $req;
	}
}
if ( count( $missing ) > 0 ) {
	echo 'You are missing the following from your config.php file: '
		. implode( ', ', $missing );
	exit( 1 ); // bad status
}

$sigexpiry = time() + $expirationIn;
$query = [
	'tasks'     => 'dummy',
	'title'     => 'Special:RunJobs',
	'sigexpiry' => $sigexpiry,
	'type'      => $jobType,
	'maxjobs'   => $maxJobs,
	'maxtime'   => $maxTime,
];

// Need to define MEDIAWIKI constant else we can't include GlobalFunctions.php
define( 'MEDIAWIKI', 1 );

require_once $pathToMediaWiki . '/includes/GlobalFunctions.php';

// The next two lines are exactly what SpecialRunJobs::getQuerySignature() does
ksort( $query );
$signature = hash_hmac( 'sha1', wfArrayToCgi( $query ), $secretKey );

echo <<<OUTPUT
Format your POST data like:
{
	"tasks"     : "dummy",
	"signature" : "$signature",
	"sigexpiry" : $sigexpiry,
	"type"      : "$jobType",
	"maxjobs"   : $maxJobs,
	"maxtime"   : $maxTime
}

OUTPUT;
