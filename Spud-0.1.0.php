<?php

class Potato {

# ------- Begin Core Functions
 #
  #
    function Potato ($options='') {

        if ($options == '') {
            echo 'Required params missing (host and port.)';
            return false;
        }

        foreach ($options as $key => $value) {
            $this->$key = $value;
        }

        # Default settings
        $this->version = '0.1.0';
        $this->buildDate = '2011/03/05';
        $this->name = 'Spud';
        $this->dedication = 'Katelyn M Lewis';

        # Needed variables
        $this->url = 'http://'.$this->host.':'.$this->port.'/';
        $this->default_db = '';
        $this->params = '';

        # If a password is present, attempt to use the supplied username and password
        if ($this->password) {
            $this->url = 'http://'.$this->username.':'.$this->password.'@'.$this->host.':'.$this->port.'/';
        }

    }

    function sendRequest ($options='') {

        # Default settings
        $url = $this->url;


        # Get and set user sent params
        $this->getParams($options);


        if ($this->params->db) {
            $url .= $this->params->db;
            if ($this->params->docId) {
                $url .= '/'.$this->params->docId;
                if ($this->params->rev) {
                    $url .= '?rev='.$this->params->rev;
                }
            }
        } else {
            $url .= $this->params->url;
        }


        $ch = curl_init();


        # Set cURL params
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type:application/json'));
        curl_setopt($ch, CURLOPT_REFERER, 'http://'.$this->host.':'.$this->port);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, ($this->params->method) ? $this->params->method : 'GET');


        # Check for data
        if ($options['data']) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);
        }


        $response = json_decode(curl_exec($ch));
        $info = (object) curl_getinfo($ch);

        curl_close($ch);

        return array($response, $info);
    }

    function getParams ($options='') {
        $this->params = '';

        if (!$options == '') {
            foreach ($options as $key => $value) {
                $this->params->$key = $value;
            }
        }

        return true;
    }
  #
 #
# - End Core Functions



# ------- Begin Database [specific] Functions
 #
  #
    function createDb ($options='') {
        $options['method'] = 'PUT';
        return $this->sendRequest($options);
    }

    function deleteDb ($options='') {
        $options['method'] = 'DELETE';
        return $this->sendRequest($options);
    }

    function ensureFullCommit ($options='') {
        $options['method'] = 'POST';
        $options['url'] = '_ensure_full_commit';
        return $this->sendRequest($options);
    }

    function listAllDocs ($options='') {
        $options['url'] = '_all_docs';
        return $this->sendRequest($options);
    }

    function compactDb ($options='') {
        $options['method'] = 'POST';
        $options['url'] = '_compact';
        return $this->sendRequest($options);
    }
  #
 #
# - End Database Functions



# ------- Begin Server Functions
 #
  #
    function listAllDbs ($options='') {
        $options['url'] = '_all_dbs';
        return $this->sendRequest($options);
    }

    function serverActiveTasks ($options='') {
        $options['url'] = '_active_tasks';
        return $this->sendRequest($options);
    }

    function serverStats ($options='') {
        $options['url'] = '_stats';
        return $this->sendRequest($options);
    }

    function serverConfig ($options='') {
        $options['url'] = '_config';
        return $this->sendRequest($options);
    }

    function serverRestart ($options='') {
        $options['method'] = 'POST';
        $options['url'] = '_restart';
        return $this->sendRequest($options);
    }
  #
 #
# - End Server Functions



# ------- Start Misc. Functions
 #
  #
    function generateUuid ($options='') {
        if (!$options['count']) { $options['count'] = 1; }

        $options['url'] = '_uuids?count='.$options['count'];
        return $this->sendRequest($options);
    }
  #
 #
#



# ------- Start Document Functions
 #
  #
    function createDoc ($options='') {
        # User specified document Id
        if ($options['docId']) {
            $options['method'] = 'PUT';
            return $this->sendRequest($options);

        # Server decides document Id
        } else {
            $options['method'] = 'POST';
            return $this->sendRequest($options);
        }
    }

    function deleteDoc ($options='') {
        # Check no revision number was sent, delete the most current document
        $options['method'] = 'DELETE';

    }

    function showDoc ($options='') {
        return $this->sendRequest($options);
    }
  #
 #
# - End Document Functions

}

?>

<?php
    $couch = new Potato(array(
        'host'=>'localhost',
        'port'=>5984,
        'username'=>'phaygo',
        'password'=>'track3r5'
    ));

    print_r($couch->showDoc(array('db'=>'phaygo', 'docId'=>'phagyoAdmin', 'rev'=>'1-2caeeaa64ad8ce1177b0653d24064e0e')));
?>