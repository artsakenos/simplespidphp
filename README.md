Introduzione
------------
Questo è un documento di supporto all'installazione di una versione preconfigurata di SimpleSamlPhp per la predisposizione all'autenticazione con [Spid](https://www.spid.gov.it/).

Si basa su un fork di una libreria SimpleSaml preconfigurata con i metadata degli Identity Provider noti e una versione dello [Spidinst](https://github.com/retepasw/spidinst) modificata al fine di facilitare le operazioni di installazione su stack Apache/PHP.


Prerequisiti
------------
Il pacchetto necessita di
* Server Apache PHP Ver 5.4+, 
* Supporto al protocollo HTTPS e certificato SSL


Installazione
-------------
Clonare o scaricare il pacchetto SimpleSaml per Spid
`https://github.com/artsakenos/simplespidphp`
nella cartella di destinazione (negli esempi seguenti verra utilizzato il path `libraries/simplespidphp`).

Avviare dal suo interno `composer install`.

Modificare la costante SAML_PATH all'interno del file `spidinst/index.php`, e.g.,
```
define('SAML_PATH', 'libraries/simplespidphp');
```

Controllare i vincoli di rewrite se esistenti, verso la cartella di destinazione della libreria e verso il path */spid* (verrà creato come link simbolico e utilizzato dal pacchetto per l'accesso al manager di configurazione), e.g., inserendo nel *.htaccess*, dopo le rewrite rules per l'*index.php*
```
# Liberiamo simplespidphp dai vincoli di Rewrite. 
RewriteCond %{REQUEST_URI} !^/libraries/simplespidphp
RewriteCond %{REQUEST_URI} !^/spid
```

Controllare permessi e proprietari delle cartelle *simplespidphp* (è importante confermarlo, soprattutto la cartella *config*) in modo che siano accessibili al server php.


Configurazione
--------------
Avviare l'installer, all'indirizzo *<sito>/<percorso librerie>*, e.g.,: `https://miosito.gov.it/libraries/simplespidphp/spidinst/`
E seguire le istruzioni a schermo, compilando  con attenzione tutti i campi.

A configurazione avvenuta, sarà possibile entrare nella pagina di amministrazione con la password precedentemente impostata.
Se la pagina di amministrazione (e.g., https://miosito.gov.it/spid) non viene trovata significa o che ci sono problemi di rewrite, o che il link simbolico non è stato creato, lo si può creare con, e.g.,:
```
ln -s /[rootweb_percorso_completo]/simplespidphp/www spid 
```

Aprire il tab di Configurazione e controllare che non ci siano librerie required mancanti, e che i requisiti del server siano sufficienti, esempio nella figura seguente.

![Check Configurazione PHP](https://github.com/artsakenos/simplespidphp/blob/master/docs-ssp/img02_CheckPhp.jpg | width=100 "Check Configurazione PHP")

Certificazione dei Metadati
---------------------------
Una volta completata la configurazione è possibile accedere ai propri metadati e recuperarli per la certificazione come Service Provider (SP) inviandoli all'AGID mediante procedura guidata. 

Il metadata è scaricabile dal tab *Federation*, che dovrebbe visualizzare correttamente tutti gli IdP conosciuti una volta entrati come amministratori.

![Recupero Metadati](https://github.com/artsakenos/simplespidphp/blob/master/docs-ssp/img03_metadati.jpg "Recupero Metadati")

I metadati si possono copiare e salvare su file, oppure recuperare direttamente dal link dell'entity ID, e.g.,  

`
https://miosito.gov.it/spid/module.php/saml/sp/metadata.php/default-sp?output=xhtml
`

Per certificarli è sufficiente entrare sul [Sistema helpdesk AgID dedicato alle entità SPID](https://helpdesk.spid.gov.it/index.php?a=add&category=4) per le PA.

![Alt Modulo di Certificazione](https://github.com/artsakenos/simplespidphp/blob/master/docs-ssp/img01_inviaticket.jpg "Modulo di certificazione")

Compilare il form seguendo le istruzioni a schermo, inviarlo e annotare il ticket ID ricevuto per verificare le risposte da AGID.

Arriverà nel giro di alcuni giorni una mail con un link tramite il quale visualizzare il ticket relativo e con l'informazione che il metadato è stato correttamente processato.
A questo punto è possibile effettuare dal tab *Autenticazione* un tentativo di connessione tramite le proprie credenziali Spid (se se ne è in possesso),
e.g., da https://miosito.gov.it/spid
selezionare la scheda Autenticazione e Test delle fonti di autenticazione,
selezionare default-sp (che corrisponde alla configurazione utilizzata)
e dal menu a tendina effettuare l'IdP con il quale si vuole eseguire il login.
Se in seguito al login sarà possibile verificare i propri dati anagrafici la procedura sarà stata eseguita correttamente.

Pubblicazione dei servizi
-------------------------
Quando i servizi interfacciati a SPID saranno pronti per essere pubblicati, il rappresentante legale dovrà compilare lo schema di convenzione fornito su richiesta e il file con l'indicazione dei servizi accessibili tramite spid.
Inviare i documenti a protocollo@pec.agid.gov.it

