<?php

    /*
        Author: Kyle A. Matheny
        Date: January 1, 2011

        github: https://github.com/phaygoweb
    */

    class Potato {
        function Potato($options) {
            foreach($options as $key => $value) {
                $this->$key = $value;
            }

            $this->version = '0.1.3';
            $this->name = 'Spud'; # Potato
            $this->db = $options['db'];
            $this->species = 'Russet Burbank';
            $this->url = "http://$this->username:$this->password@$this->host:$this->port/";
        }

        function sendRequest($options=array('method'=>'GET', 'data'=>'', 'url'=>'')) {

            # Set URL
            $url = $this->url;
            
            echo "URL: <i>".$url."</i><br/>"
                ."Db: <i>".$options['db']."</i><br/>";
            
            
            # Check for database
            
            ($options['db'] == '~') ? $db = '' :
                ($options['db']) ? $db = $options['db'].'/' : 
                    $db = $this->db;
                    
            
            # Check for database
            /*
            if ($options['db'] == '~') {
                $db = '';
                echo "0.";
            } elseif ($options['db']) {
                $db = $options['db'].'/';
                echo "1.";
            } else {
                $db = $this->db.'/';
                echo "2.";
            }
            */

            
            $url .= $db;
            
            echo "<b>".$url."</b><br/>";
            echo "<b>".$options['url']."</b><br/>";
            

            # Append to URL if needed
            if ($options['url']) {
                $url .= $options['url'];
            }
            
            
            # Initialize cURL and set basic properties
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type:application/json'));
            curl_setopt($ch, CURLOPT_REFERER, 'http://'.$this->host.':'.$this->port);


            # Set method
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, ($options['method']) ? $options['method'] : 'GET');


            # Check for data
            if ($options['data']) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);
            }
            
            
            # Send cURL request
            $response = json_decode(curl_exec($ch));
            $curlInfo = (object) curl_getinfo($ch);
            curl_close($ch);

            return array($response, $curlInfo);

        }

        /*Use:
            createDatabase(array(
                'db'=>'NAME_OF_DATABASE'
            ));
        */
        function createDatabase($options=array()) {
            return $this->sendRequest(array(
                    'method'=>'PUT'
                ));
        }

        /*Use:
            deleteDatabase(array(
                'db'=>'NAME_OF_DATABASE'
            ));
        */
        function deleteDatabase($options=array()) {
            return $this->sendRequest(array(
                    'method'=>'DELETE'
                ));
        }

        /*
            Note: Returns all databases in the system
        */
        function listAllDatabases() {
            return $this->sendRequest(array(
                    'url'=>'_all_dbs',
                    'db'=> '~'
                ));
        }

        /*Use:
            compactDatabase(array(
                'db'=>'NAME_OF_DATABASE'
            ));
        */
        function compactDatabase($options=array()) {
            return $this->sendRequest(array(
                    'method'=>'POST',
                    'url'=>'_compact',
                    'db'=>'~'
                ));
        }

        /*Use:
            replicateDatabase(array(
                'source'=>'SOURCE_DATABASE_TO_REPLICATE',
                'target'=>'DATABASE_TO_REPLICATE_TO',
                'cancel'=>'true/false',
                'continuous'=>'true/false',
                'create_target'=>'true/false'

                Work on:
                Proxy, Filter, and doc_ids
            ));
        */
        function replicateDatabase($options=array('create_target'=>'false')) {
            return $this->sendRequest(array(
                    'method'=>'POST',
                    'url'=>'_replicate',
                    'db'=>'~',
                    'data'=>'{"source":"'.$options['source'].'","target":"'.$options['target'].'","cancel":'.$options['cancel'].',"continuous":'.$options['continuous'].',"create_target":'.$options['create_target'].'}'
                ));
        }

        /*Use:
            cleanupDatabaseView(array(
                'db'=>'NAME_OF_DATABASE'
            ));

            Note: Clean up old view data
        */
        function cleanupDatabaseView($options=array()) {
            return $this->sendRequest(array(
                    'method'=>'POST',
                    'url'=>'_view_cleanup'
                ));
        }

        /*
            Note: Shows active tasks on the database
        */
        function showActiveTasks($options=array()) {
            return $this->sendRequest(array(
                    'url'=>'_active_tasks',
                    'db'=>'~'
                ));
        }

        /*
            Note: Shows statistics
        */
        function showStats($options=array()) {
            return $this->sendRequest(array(
                    'url'=>'_stats',
                    'db'=>'~'
                ));
        }

        /*
            Note: Shows configuration data
        */
        function showConfig($options=array()) {
            return $this->sendRequest(array(
                    'url'=>'_config',
                    'db'=>'~'
                ));
        }

        /*Use:
            showDatabaseChangeFeed(array(
                'db'=>'NAME_OF_DATABASE'
            ));

            Note: Displays the change feed for 'db'
        */
        function showDatabaseChangeFeed($options=array()) {
            return $this->sendRequest(array(
                    'url'=>'/_changes'
                ));
        }

        /*
            Note: Restarts the CouchDB server
        */
        function restartServer() {
            return $this->sendRequest(array(
                    'method'=>'POST',
                    'url'=>'_restart',
                    'db'=>'~'
                ));
        }

        /*Use:
            ensureFullCommit(array(
                'db'=>'NAME_OF_DATABASE'
            ));

            Note: Makes sure all uncommited changes are written and synced to the disk
        */
        function ensureFullCommit($options=array()) {
            return $this->sendRequest(array(
                    'method'=>'POST',
                    'url'=>((!$options['db']) ? $this->db : $options['db']).'/_ensure_full_commit'
                ));
        }

        /*
            Note: Returns a unique identifier
        */
        function generateUuid($options=array('count'=>'1')) {
            return $this->sendRequest(array(
                    'url'=>'_uuids?count='.$options['count']
                ));
        }

        /*Use:
            createDocument(array(
                'data'=>'{}'
                'db'=>'NAME_OF_DATABASE',

                optional:
                'docId'=>'DOCUMENT_ID'
            ));
        */
        function createDocument($options=array('docId'=>'')) {
            if(!$options['docId']) {

                return $this->sendRequest(array(
                    'method'=>'POST',
                    'data'=>$options['data']
                ));

            } else {

                return $this->sendRequest(array(
                    'method'=>'PUT',
                    'data'=>$options['data'],
                    'url'=>((!$options['db']) ? $this->db : $options['db']).'/'.$options['docId']
                ));
            }
        }

        /*Use:
            updateDocument(array(
                'db'=>'NAME_OF_DATABASE',
                'docId'=>'DOCUMENT_ID',
                'data'=>'{"FIELDS":"TO_UPDATE"}'
        */
        /*
        function updateDocument($options=array()) {
            // Get current data from requested document
            $tempData = json_encode($this->getDocument(array('db'=>$options['db'], 'docId'=>$options['docId'])));

            // Append the new data to the current data
            $tempData = substr($tempData,0,strLen($tempData)-1).','.substr($options['data'],1,strlen($options['data']));

            return $this->sendRequest(array(
                    'method'=>'PUT',
                    'data'=>$tempData,
                    'url'=>((!$options['db']) ? $this->db : $options['db']).'/'.$options['docId']
                ));
        }
        */
        
        function updateDocument($options=array()) {
            return $this->sendRequest();
        }

        /*Use:
            replaceDocument(array(
                'docId'=>'DOCUMENT_ID',
                'db'=>'NAME_OF_DATABASE'
            ));
        */
        function replaceDocument($options=array()) {
            $revNum = $this->getDocumentRevNumber(array('docId'=>$options['docId'],'db'=>$options['db']));

            $tempData = substr_replace($options['data'], '"_rev":"'.$revNum.'",'.substr($options['data'],1), 1);

            return $this->sendRequest(array(
                    'method'=>'PUT',
                    'data'=>$tempData,
                    'url'=>((!$options['db']) ? $this->db : $options['db']).'/'.$options['docId']
                ));
        }

        /*Use:
            deleteDocument(array(
                'db'=>'NAME_OF_DATABASE',
                'docId'=>'DOCUMENT_ID'
            ));
        */
        function deleteDocument($options=array()) {
            $revNum = $this->getDocumentRevNumber(array('docId'=>$options['docId'],'db'=>$options['db']));

            return $this->sendRequest(array(
                    'method'=>'DELETE',
                    'url'=>((!$options['db']) ? $this->db : $options['db']).'/'.$options['docId'].'?rev='.$revNum
                ));
        }

        /*Use:
            getDocumentRevNumber(array(
                'docId'=>'DOCUMENT_ID',
                'db'=>'NAME_OF_DATABASE'
            ));
        */
        function getDocumentRevNumber($options=array()) {
            // Get documents Id
            return $docInfo = $this->getDocument(array(
                    'db'=>((!$options['db']) ? $this->db : $options['db']),
                    'docId'=>$options['docId']
                ))->_rev;
            //return $docInfo->_rev;
        }

        /*Use:
            getAllDbDocuments(array(
                'db'=>'NAME_OF_DATABASE'
            ));
        */
        function listAllDbDocuments($options=array()) {
            return $this->sendRequest(array(
                    'url'=>((!$options['db']) ? $this->db : $options['db']).'/_all_docs'
                ));
        }

        /*Use:
            getDocument(array(
                'db'=>'NAME_OF_DATABASE',
                'docId'=>'DOCUMENT_ID'
            ));
        */
        function getDocument($options=array()) {
            return $this->sendRequest(array(
                    'url'=>((!$options['db']) ? $this->db : $options['db']).'/'.$options['docId']
                ));
        }
        
        function showDb($options=array()) {
            return $this->sendRequest(array(
                    'url'=>((!$options['db']) ? $this->db : $options['db'])
                ));
        }
    }
?>