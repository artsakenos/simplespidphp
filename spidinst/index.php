<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Installazione di SimpleSpidPhp</title>
        <center>
            <h1>Installazione di SimpleSpidPhp</h1>
            <div>Benvenuto all'installazione di SimpleSpidPhp, leggi il file <a href="LEGGIMI.md">Leggimi.md</a> per maggiori dettagli su codice e utilizzo</div>
        </center> <hr />
    </head>

    <body>

        <?php
        define('VERSION', '0.2alpha');
        define('REPO', 'http://www.scuolacooperativa.net/drupal7/sites/default/files/simplespidphp.zip');
        define('SAML_PATH', 'libraries/simplespidphp'); // Il path della libreria già copiata e decompressa senza trailing slash, e.g., simplespidphp

        if (extension_loaded('zip') == false) {
            // echo 'estensione ZIP di PHP non presente, impossibile continuare';
            // exit;
        }
        if (extension_loaded('curl') == false) {
            echo 'estensione CURL di PHP non presente, impossibile continuare';
            exit;
        }
        if (extension_loaded('openssl') == false) {
            echo 'estensione OPENSSL di PHP non presente, impossibile continuare';
            exit;
        }

        $query = $_SERVER['QUERY_STRING'];
        $root = $_SERVER['DOCUMENT_ROOT'];
        $script_name = $_SERVER['SCRIPT_NAME'];
        chdir($root);

        if (file_exists(SAML_PATH . "/cert/saml.crt")) {
            echo "<div>Il framework sembra già installato, operazione interrotta.</div>";
            echo "<div>Per forzare la reinstallazione, eliminare i certificati dalla cartella cert/.</div>";
            exit;
        }

        switch ($query) {
            case null:
                echo "<p />Stai per procedere con la configurazione di una versione preinstallata di SimpleSamlPhp personalizzata per SPID.";
                echo "<p />L'installazione e la configurazione avverranno nella cartalla <i>$root<b>/" . SAML_PATH . "</b></i>, da parte dello script <i>$script_name</i>.";
                echo "<p />Se le informazioni sono corrette, <a href=\"$script_name?install\">procedi con l'installazione</a>";
                break;
            case 'start':
                //echo 'start';
                echo start_download();
                break;
            case 'download':
                echo download();
                unzip($is_out ? false : true);
                @unlink('simplespidphp.zip');
                make_link();
                break;
            case 'install':
                echo cert_form();
                break;
            case 'configure':
                $dn = array(
                    "countryName" => $_POST['countryName'],
                    "stateOrProvinceName" => $_POST['stateOrProvinceName'],
                    "localityName" => $_POST['localityName'],
                    "organizationName" => $_POST['organizationName'],
                    "organizationalUnitName" => $_POST['organizationalUnitName'],
                    "commonName" => $_POST['commonName'],
                    "emailAddress" => $_POST['emailAddress']
                );

                $ok = true;

                make_certs($dn);
                $dn["admin_password"] = $_POST['admin_password'];
                $dn["machineName"] = $_POST['machineName'];
                $dn["abs_lib_path"] = getcwd() . "/" . SAML_PATH;
                $dn["secretsalt"] = rand_string(32);
                $ok = $ok && config_file(SAML_PATH . "/config/config.php", $dn);
                $ok = $ok && config_file(SAML_PATH . "/config/authsources.php", $dn);


                echo "<h2>Risultato</h2>";
                if ($ok) {
                    echo "Installazione effettuata.<br/>Puoi accedere alla <a href='/spid'>pagina di amministrazione di SamlPhp per Spid</a>.";
                    make_link();
                } else {
                    echo "Qualcosa non ha funzionato, contattare gli amministratori del sistema per verificare gli errori";
                }
                break;
        }
        ?>

    </body>
</html>
<?php

function token_replace($string, $array) {
    if (strchr($string, '@')) {
        foreach ($array AS $key => $value) {
            $val = str_replace("'", "\'", $value);
            $string = str_replace('@' . $key, $val, $string);
        }
    }
    return $string;
}

function start_download() {
    $url = "http" . (!empty($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['SERVER_NAME'] . $_SERVER["SCRIPT_NAME"] . '?download';
    $link = '<a href=' . $_SERVER['SCRIPT_NAME'] . '?install>procedi con la installazione</a>';
    echo "<div id='msg' style='font-size:1.2em;text-align:center' >&nbsp;</div>";
    $myscript = <<<MYSCRIPT
<script>
function createXMLHttpRequest() {
  try { return new XMLHttpRequest(); } catch (e) { }
  try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) { }
  alert("XMLHttpRequest non supportato");
  return null;
}
function do_ajax() {
  document.getElementById("download").setAttribute("disabled", "disabled");
  document.getElementById("download").setAttribute("style", "cursor:wait");
  var div = document.getElementById("msg");
  div.innerHTML = 'attendere, sto scaricando...';
  var http_request = createXMLHttpRequest();
  if (!http_request) {
    alert('Javascript error: no XMLHTTP instance');
    return false;
  }
  http_request.open('GET', '$url');
  http_request.onload = function() {
    document.getElementById("download").setAttribute("style", "cursor:default;display:none;");
    if (http_request.status === 200) {
		div.innerHTML = 'file scaricato: $link';
    }
    else {
        alert('Request failed.  Returned status of ' + xhr.status);
    }
  };
  http_request.send();
}
//do_ajax();
</script>	
MYSCRIPT;
    echo $myscript;
    echo '<div style="text-align:center"><input id="download" style="font-size:1.2em" type="button" value="scarica simplespidphp" onclick="do_ajax()" /></div>';
}

function unzip($aruba) {
    $zip = new ZipArchive;
    $res = $zip->open('simplespidphp.zip');
    if ($res === TRUE) {
        $zip->extractTo('.');
        $zip->close();
        echo 'archivio decompresso...<br />';
        if ($aruba) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('simplespidphp'));
            foreach ($iterator as $item) {
                if (substr($item->getBasename(), 0, 1) != '.')
                    chmod($item, 0755);
            }
            echo 'permessi modificati...<br/>';
        }
    } else {
        echo 'errore di decompressione archivio!';
        exit;
    }
}

function make_link() {
    $root = $_SERVER['DOCUMENT_ROOT'];
    $target = getcwd() . "/" . SAML_PATH . "/www";
    chdir($root);
    $success = symlink($target, 'spid');
    echo "<h2>Link a Saml</h2>Un symbolic link a <i>$target</i> è stato creato, ora è <i>/spid</i><br />";
    echo "Se ci fossero problemi legati a una configurazione di <i>.htaccess</i>, creare manualmente l'alias, o da pathauto (e.g., se in Drupal)<br />";
}

function download() {
    $file = 'simplespidphp.zip';
    $url = REPO;
    $ch = curl_init();
    if ($ch) {
        $fp = fopen($file, "w");
        if ($fp) {
            if (!curl_setopt($ch, CURLOPT_URL, $url)) {
                fclose($fp); // to match fopen() 
                curl_close($ch); // to match curl_init() 
                return "FAIL: curl_setopt(CURLOPT_URL)";
            }
            if (!curl_setopt($ch, CURLOPT_FILE, $fp))
                return "FAIL: curl_setopt(CURLOPT_FILE)";
            if (!curl_setopt($ch, CURLOPT_HEADER, 0))
                return "FAIL: curl_setopt(CURLOPT_HEADER)";
            if (!curl_exec($ch))
                return "FAIL: curl_exec()";
            curl_close($ch);
            fclose($fp);
            return "SUCCESS: $file [$url]";
        } else
            return "FAIL: fopen()";
    } else
        return "FAIL: curl_init()";
}

function cert_form() {
    $commonName = $_SERVER['SERVER_NAME'];
    $myform = <<<MYFORM
<script>
function validator() {
var x = document.getElementById("get_data");
var i;
for (i = 0; i < x.length; i++) {
    if (x.elements[i].value == "") {
		alert("compilare tutti i campi");
		return false;
	}
}
if (document.getElementById("pass1").value != document.getElementById("pass2").value) {
	alert("le due password non coincidono");
	return false;
}
return true;
}
</script>
	<h2>Generatore di Certificato e Configurazioni per SPID</h2>
        Compilare correttamente tutti i campi, chiedendo conferme ad amministratori ed eventualmente all'AGID,<br />
        prestare inoltre attenzione alle <i>note in corsivo</i>.<br/>
	<form id="get_data" action="?configure" method="POST" onSubmit="return validator()">
		<p />-------- Certificato ---------<br/>
		<input name="countryName" type="text" value="IT" readonly /> countryName <i>- Lasciare IT!</i><br/>
		<select name="stateOrProvinceName"><option>Agrigento</option><option>Alessandria</option><option>Ancona</option><option>Aosta</option><option>Arezzo</option><option>Ascoli Piceno</option><option>Asti</option><option>Avellino</option><option>Bari</option><option>Barletta-Andria-Trani</option><option>Belluno</option><option>Benevento</option><option>Bergamo</option><option>Biella</option><option>Bologna</option><option>Bolzano</option><option>Brescia</option><option>Brindisi</option><option>Cagliari</option><option>Caltanissetta</option><option>Campobasso</option><option>Carbonia-Iglesias</option><option>Caserta</option><option>Catania</option><option>Catanzaro</option><option>Chieti</option><option>Como</option><option>Cosenza</option><option>Cremona</option><option>Crotone</option><option>Cuneo</option><option>Enna</option><option>Fermo</option><option>Ferrara</option><option>Firenze</option><option>Foggia</option><option>Forlì-Cesena</option><option>Frosinone</option><option>Genova</option><option>Gorizia</option><option>Grosseto</option><option>Imperia</option><option>Isernia</option><option>La Spezia</option><option>L&#039;Aquila</option><option>Latina</option><option>Lecce</option><option>Lecco</option><option>Livorno</option><option>Lodi</option><option>Lucca</option><option>Macerata</option><option>Mantova</option><option>Massa-Carrara</option><option>Matera</option><option>Messina</option><option>Milano</option><option>Modena</option><option>Monza e della Brianza</option><option>Napoli</option><option>Novara</option><option>Nuoro</option><option>Olbia-Tempio</option><option>Oristano</option><option>Padova</option><option>Palermo</option><option>Parma</option><option>Pavia</option><option>Perugia</option><option>Pesaro e Urbino</option><option>Pescara</option><option>Piacenza</option><option>Pisa</option><option>Pistoia</option><option>Pordenone</option><option>Potenza</option><option>Prato</option><option>Ragusa</option><option>Ravenna</option><option>Reggio Calabria</option><option>Reggio Emilia</option><option>Rieti</option><option>Rimini</option><option selected>Roma</option><option>Rovigo</option><option>Salerno</option><option>Medio Campidano</option><option>Sassari</option><option>Savona</option><option>Siena</option><option>Siracusa</option><option>Sondrio</option><option>Taranto</option><option>Teramo</option><option>Terni</option><option>Torino</option><option>Ogliastra</option><option>Trapani</option><option>Trento</option><option>Treviso</option><option>Trieste</option><option>Udine</option><option>Varese</option><option>Venezia</option><option>Verbano-Cusio-Ossola</option><option>Vercelli</option><option>Verona</option><option>Vibo Valentia</option><option>Vicenza</option><option>Viterbo</option></select> stateOrProvinceName<br/>
		<input name="localityName" type="text" value="Roma" /> localityName<br/>
		<input name="organizationName" type="text" value="Ente Nazionale Per L'Aviazione Civile"/> organizationName<br/>
		<input name="organizationalUnitName" type="text" value="ENAC"/> organizationalUnitName<br/>
		<input name="commonName" type="text" value="$commonName"/> commonName <i>- Nome del dominio</i><br/>
		<input name="emailAddress" type="text" value=""/> emailAddress <i>- Indicare un indirizzo email da contattare per eventuali rinnovi o altre informazioni</i><br/>
		<p />-------- Configurazione ---------<br/>
		<input id="pass1" name="admin_password" type="password" value=""/> Password amministratore <i>Serve per accedere al pannello di configurazione Saml</i><br/>
		<input id="pass2" name="admin_password2" type="password" value=""/> Ripeti password amministratore<br/>
		<input name="machineName" type="text" value="login_certificato"/> Nome del servizio (senza spazi) <i>- Nel caso in futuro si vogliano esporre servizi differenti</i><br/>
		<input type="submit" value="Procedi"/>
	</form>	
MYFORM;
    return $myform;
}

function make_certs($dn) {
    $numberofdays = 3652 * 2;
    $privkey = openssl_pkey_new(array(
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'x509_extensions' => 'v3_ca',
        'digest_alg' => 'sha256',
    ));
    $csr = openssl_csr_new($dn, $privkey);
    $serials = @file("serials.txt");
    if ($serials === false)
        $serials = array();
    do {
        $myserial = hexdec(bin2hex(openssl_random_pseudo_bytes(8)));
    } while (in_array($myserial, $serials));
    $fh = fopen("serials.txt", "a");
    if ($fh) {
        fwrite($fh, sprintf("%d\n", $myserial));
        fclose($fh);
    }
    $configArgs = array("digest_alg" => "sha256");
    $sscert = openssl_csr_sign($csr, null, $privkey, $numberofdays, $configArgs, (int) $myserial);
    openssl_x509_export($sscert, $publickey);
    openssl_pkey_export($privkey, $privatekey);

    // Creazione di tutte le directory necessarie (se non esistono)
    if (!file_exists(SAML_PATH . "/cert/")) {
        mkdir(SAML_PATH . "/cert/", 0777, true);
    }
    if (!file_exists(SAML_PATH . "/log/")) {
        mkdir(SAML_PATH . "/log/", 0777, true);
    }

    //openssl_csr_export($csr, $csrStr);
    file_put_contents(SAML_PATH . "/cert/saml.pem", $privatekey);
    file_put_contents(SAML_PATH . "/cert/saml.crt", $publickey);

    //echo $privatekey.'<br/>'; // Will hold the exported PriKey
    //echo $publickey.'<br/>';  // Will hold the exported PubKey
    //echo $csrStr;     // Will hold the exported Certificate
}

function config_file($file, $array) {
    $lines = file($file);
    foreach ($lines as &$line)
        $line = token_replace($line, $array);
    $handle = fopen($file, "w");
    if ($handle) {
        foreach ($lines as $line)
            fwrite($handle, $line);
        fclose($handle);
        return TRUE;
    } else {
        echo "<p /><b>Impossibile scrivere file di configurazione $file</b>";
        return FALSE;
    }
}

function rand_string($length) {
    $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
    $size = strlen($chars);
    $str = "";

    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[rand(0, $size - 1)];
    }
    return $str;
}

// Funzioni obsolete
?>

