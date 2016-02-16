#!/bin/env php
<?php

/**
 * @copyright 2013 James Linden <kodekrash@gmail.com>
 * @author Manjot Singh <manjot.singh@percona.com> forked from script by James Linden <kodekrash@gmail.com>
 * @link https://github.com/ManjotS/wikipedia.org-xmldump-elasticsearch
 * @link https://github.com/kodekrash/wikipedia.org-xmldump-mongodb
 * @license BSD (2 clause) <http://www.opensource.org/licenses/BSD-2-Clause>
 */

$dsname = $argv[2];
$file = $argv[1];
$logpath = './';

/*************************************************************************/

date_default_timezone_set( 'America/Chicago' );

function abort( $s ) {
	die( 'Aborting. ' . trim( $s ) . PHP_EOL );
}

if( !is_file( $file ) || !is_readable( $file ) ) {
	abort( 'Data file is missing or not readable.' );
}

if( !is_dir( $logpath ) || !is_writable( $logpath ) ) {
	abort( 'Log path is missing or not writable.' );
}

$in = bzopen( $file, 'r' );
if( !$in ) {
	abort( 'Unable to open input file.' );
}

require 'vendor/autoload.php';

$hosts = array($dsname);
$client = \Elasticsearch\ClientBuilder::create()           // Instantiate a new ClientBuilder
                    ->setHosts($hosts)      // Set the hosts
                    ->setRetries(6)
                    ->build();              // Build the client object


try {
    $response = $client->indices()->stats();
    print_r($response['indices']['wikipedia']['primaries']['docs']);
} catch (Elasticsearch\Common\Exceptions\TransportException $e) {
    $previous = $e->getPrevious();
    print_r($previous);
    exit;
}

print "Starting at ".date("F j, Y, g:i a");

$data="";
$line="";
$saving=false;
$inserted=false;
while( !feof( $in ) ) {
	$l = bzread( $in, 1 );

	if( $l === false ) {
		abort( 'Error reading compressed file.' );
	}
	if( $l != PHP_EOL ) {
		$line .= $l;
	}
	else {
		$line = trim($line);

		if($line!==false && $line != '')
		{
			if($line=='<page>')
			{
				$line .= "\n<index>wikipedia</index>\n<type>af</type><body>";
				$saving=true;
				$inserted=false;
			}

			if($line=='</page>')
			{
				$data .= "</body>\n</page>";
				$saving=false;
			}

			if($saving)
			{
		  		$data.=$line;
		  	}

		  	if(!$inserted && !$saving)
		  	{
		  		$inserted=true;
				//var_dump($data);
				$xml = simplexml_load_string($data);
				//var_dump($xml);
				if($xml !== false)
				{
					try {
						$result = $client->index($xml);
						print 'Inserted: '.$result["_id"]."\n";
					} catch (Elasticsearch\Common\Exceptions\TransportException $e) {
				    	$previous = $e->getPrevious();
	    				print_r($previous);
					}
				}

				$data="";
		  	}
		}

		$line = "";
	}
}


bzclose( $in );

print "Finished at ".date("F j, Y, g:i a");


