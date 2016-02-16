wikipedia.org-xmldump-elasticsearch
=============================

Overview
--------

Wikipedia.org XML Dump Importer is a script to import the standard Wikipedia XML dump into a simple elasticsearch data structure, useful as a local cache for searching and manipulating Wikipedia articles. The datastore structure is designed for ease of use, and is not mediawiki-compatible.

Dataset Source
--------------

URL: http://dumps.wikimedia.org/

Updates: monthly

Environment
-----------

* GNU/Linux
* PHP 5.4 + (with mbstring, simplexml extensions)
* Elasticsearch 2.2 +
* php5-curl

Notes
-----

* This script is designed to run on the command line - not a web browser
* enwiki download is approximately 9.5GB compressed and will require another (approx.) 10 times that for the uncompressed data and 2 replicas
* This script reads the compressed file.
* Import process required approximately 4 hours on a well configured quad core with 4GB of memory. 

Howto
-----
* Install elasticsearch via php composer. see notes below
* Download the proper pages-articles XML file - for example, enwiki-20130708-pages-articles.xml.bz2.
* bunzip2 the wiki file
* Create the wikipedia index
	```bash
		curl -XPUT http://localhost:9200/wikipedia -d '{
		    "settings" : {
		        "number_of_shards" : 12,
		        "number_of_replicas" : 2
		    }
		}'
	```
* Download the script.
* Run the script with 2 arguments script.php wikifile.bz2 https://localhost:9200 -- this may take several hours.

Installation of elasticsearch php api via Composer
-------------------------
The recommended method to install _Elasticsearch-PHP_ is through [Composer](http://getcomposer.org).

1. Add ``elasticsearch/elasticsearch`` as a dependency in your project's ``composer.json`` file (change version to suit your version of Elasticsearch):
	```bash
		cd
		nano composer.json
	```

    ```json
        {
            "require": {
                "elasticsearch/elasticsearch": "~2.0"
            }
        }
    ```

2. Download and install Composer:

    ```bash
        curl -s http://getcomposer.org/installer | php
    ```

3. Install your dependencies:

    ```bash
        php composer.phar install --no-dev
    ```


License
-------

This project is BSD (2 clause) licensed.
