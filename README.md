SPIDINST II
-----------
Si basa su una versione modificata dello Spidinst di Paolo Bozzo.
    https://github.com/retepasw/spidinst
utilizza una libreria SimpleSaml preconfigurata disponibile su:
    http://www.scuolacooperativa.net/drupal7/sites/default/files/simplespidphp.zip
e facilita le operazioni di installazione anche su ambiente Drupal 8.

PREREQUISITI
------------
Il sito deve essere sotto https.

Questa configurazione di SimpleSamlPhp utilizza SQLite, installabile con

* sudo apt-get install php5-sqlite # per Php5+
* sudo apt-get install php-sqlite3 # per Php7+

INSTALLAZIONE
-------------
Una volta copiato tutto il pacchetto nella directory desiderata,
e.g., libraries/simplespidphp
modificare la costante SAML_PATH
    define('SAML_PATH', 'libraries/simplespidphp');
Su Drupal 8, per eliminare tutte le conseguenze dei redirect inserire nel .htaaccess
le seguenti righe (adattandole al percorso scelto):

  # Liberiamo simplespidphp dai vincoli di Rewrite. 
  RewriteCond %{REQUEST_URI} !^/libraries/simplespidphp
  RewriteCond %{REQUEST_URI} !^/spid

Controllare che i permessi e i chown di simplespidphp siano OK (dovrebbero già esserlo ma è importante confermarlo, soprattutto sulla cartella config)


UTILIZZO
--------
Avviare l'installer, all'indirizzo: https://.../libraries/simplespidphp/spidinst/
E seguire le istruzioni a schermo.


TODO
----
Inserire una procedura per la modifica dei campi ancora hard coded (ad esempio
il path della libreria).

Eliminare vendor, ora inclusa brutalmente con una configurazione funzionante.
Appena il modulo Spid sarà pronto e funzionante verrà testato l'update delle librerie.

NOTE TECNICHE
-------------
Sebbene sia utilizzato composer, la configurazione di base è caricata già insieme
al pacchetto, perché non conosco eventuali problemi di compatibilità con altre 
versioni delle librerie come è già successo con un installazione dell'ultimo saml.
In futuro si può prevedere di ignorare nuovamente le cartelle di composer 
e altre cartelle con i dati (decommentando le linee corrispondenti su -gitignore).
