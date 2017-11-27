Introduzione
------------
Questo è un documento di supporto all'installazione di una versione preconfigurata di SimpleSamlPhp per la predisposizione dell'autenticazione con Spid all'interno del sito dell'Enac ricalcando una procedura esistente sul sito scuolacooperativa.

Si basa su una libreria SimpleSaml preconfigurata e una versione modificata dello [Spidinst](https://github.com/retepasw/spidinst) di Paolo Bozzo al fine di facilitare le operazioni di installazione in particolare su ambiente Drupal 8.


Prerequisiti
------------
Il pacchetto necessita di
* server Apache PHP Ver 5.4+, 
* supporto di protocollo HTTPS e certificato SSL


Installazione
-------------
Clonare il pacchetto SimpleSaml per Spid
`https://github.com/artsakenos/simplespidphp`
nella cartella di destinazione (e.g., libraries/simplespidphp).
Dal suo interno avviare `composer install`.

Una volta copiato tutto il pacchetto nella directory desiderata modificare la costante SAML_PATH all'interno di spidinst/index.php, e.g.,
```
define('SAML_PATH', 'libraries/simplespidphp');
```

Controllare i vincoli di rewrite se esistenti, verso la cartella di destinazione della libreria e verso il path /spid (verrà creato come link simbolico e utilizzato dal pacchetto per l'accesso al manager di configurazione), e.g., inserendo nel .htaccess, dopo 
```
# Liberiamo simplespidphp dai vincoli di Rewrite. 
RewriteCond %{REQUEST_URI} !^/libraries/simplespidphp
RewriteCond %{REQUEST_URI} !^/spid
```

Controllare permessi e proprietari delle cartelle simplespidphp (è importante confermarlo, soprattutto la cartella config) in modo che siano accessibili al server php.


Configurazione
--------------
Avviare l'installer, all'indirizzo <sito>/<percorso librerie>, e.g.,: `https://miosito.gov.it/libraries/simplespidphp/spidinst/`
E seguire le istruzioni a schermo, compilando  con attenzione tutti i campi.

A configurazione avvenuta, sarà possibile entrare nella pagina di amministrazione con la password precedentemente impostata.
Se la pagina di amministrazione (e.g., https://miosito.gov.it/spid) non viene trovata significa che il link simbolico non è stato creato, lo si può creare con, e.g.,:
```
ln -s /[rootweb_percorso_completo]/simplespidphp/www spid 
```
Aprire il tab di Configurazione e controllare che non ci siano librerie required mancanti, e che i requisiti del server siano sufficienti, esempio nella figura seguente.

![Check Configurazione PHP](https://github.com/artsakenos/simplespidphp/blob/master/docs-ssp/img02_CheckPhp.jpg "Check Configurazione PHP")

Certificazione dei Metadati
---------------------------
Una volta completata la configurazione è possibile certificare i propri metadati come Service Provider (SP) inviandoli all'AGID mediante procedura guidata. 

Il metadata è scaricabile dal tab Federation, che dovrebbe visualizzare correttamente tutti gli IdP conosciuti una volta entrati come amministratori.

![Recupero Metadati](https://github.com/artsakenos/simplespidphp/blob/master/docs-ssp/img03_metadati.jpg "Recupero Metadati")

I metadati si possono copiare e salvare su file, oppure recuperare direttamente dal link dell'entity ID, e.g.,  

`
https://miosito.gov.it/spid/module.php/saml/sp/metadata.php/default-sp?output=xhtml
`

Per certificarli è sufficiente entrare sul [Sistema helpdesk AgID dedicato alle entità SPID](https://helpdesk.spid.gov.it/index.php?a=add&category=4) per le PA.

![Alt Modulo di Certificazione](https://github.com/artsakenos/simplespidphp/blob/master/docs-ssp/img01_inviaticket.jpg "Modulo di certificazione")

Compilare il form, inviarlo e annotare il ticket ID ricevuto per verificare le risposte da AGID.

Arriverà nel giro di alcuni giorni una mail con un link tramite il quale visualizzare il ticket relativo e con l'informazione che il metadato è stato correttamente processato.
A questo punto è possibile effettuare dal link a spid un tentativo di connessione tramite delle credenziali spid (se se ne è in possesso),
e.g., da https://miosito.gov.it/spid
selezionare la scheda Autenticazione e Test delle fonti di autenticazione,
selezionare default-sp (che corrisponde alla configurazione utilizzata)
e dal menu a tendina effettuare l'IdP con il quale si vuole eseguire il login.
Se in seguito al login sarà possibile verificare i propri dati anagrafici la procedura sarà stata eseguita correttamente.

Pubblicazione dei servizi
-------------------------
Quando i servizi interfacciati a SPID saranno pronti per essere pubblicati, il rappresentante legale dovrà compilare lo schema di convenzione fornito su richiesta e il file con l'indicazione dei servizi accessibili tramite spid.
Inviare i documenti a protocollo@pec.agid.gov.it

