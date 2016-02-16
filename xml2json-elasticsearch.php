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

require 'vendor/autoload.php';

$hosts = [$dsname];
$client = \Elasticsearch\ClientBuilder::create()           // Instantiate a new ClientBuilder
                    ->setHosts($hosts)      // Set the hosts
                    ->build();              // Build the client object


$data="";
$line=null;
$rec=false;
$finished=false;
foreach(new SplFileObject($file) as $line) {
	$line = trim($line);

	if($line!==false && $line != '')
	{
		if($line=='<page>')
		{
			$line = "<page>\n<index>wikipedia</index>\n<type>af</type>";
			$rec=true;
			$finished=false;
		}

		if($rec)
		{
	  		$data.=$line;
	  	}

		if($line=='</page>')
		{
			$rec=false;
		}

	  	if(!$finished && !$rec)
	  	{
	  		$finished=true;

			$xml = simplexml_load_string($data);
			if($xml !== false)
			{
//				$xml->addChild("index","wikipedia");
//				$xml->addChild("type","af");

				$json = json_encode($xml);
				//print $json; exit;
				$result = $client->index($json);

				$data="";
			}
	  	}
	}
}

