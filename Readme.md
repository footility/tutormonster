# TutorMonster - Strumenti di Automazione per Tutor

## Introduzione
TutorMonster è una suite di strumenti di automazione progettati per assistere i tutor nel processo di valutazione degli studenti. Questa suite contiene vari script, il primo dei quali è "TritaLaravel", mirato a ottimizzare e migliorare il feedback sugli esercizi di Laravel.

## Script TRITALARAVEL

### Descrizione
"TritaLaravel" è uno script automatizzato, attualmente in versione beta, sviluppato per semplificare e velocizzare l'analisi degli esercizi di Laravel. Questo strumento è stato creato per rendere più efficiente il lavoro dei tutor a supporto della loro routine pomeridiana.

### Funzionamento dello Script

Il processo inizia con il download delle repository degli studenti direttamente dai loro repository GitHub. Questo passaggio assicura che l'ultimo lavoro degli studenti sia sempre analizzato, garantendo che il feedback sia quanto più attuale e rilevante possibile.

Una volta scaricata la repository, lo script procede alla configurazione dell'ambiente. Questo include la riconfigurazione del file .env per ogni progetto, utilizzando un file .env personalizzato specifico per l'ambiente locale.

Segue poi la fase di installazione e setup di Laravel. Lo script utilizza Composer per installare le dipendenze necessarie e configura l'ambiente Laravel eseguendo operazioni cruciali come la generazione delle chiavi, le migrazioni del database e il seeding. Inoltre, viene configurato il link allo storage per gestire correttamente i file multimediali e altri dati statici usati nelle applicazioni degli studenti.

Per facilitare i test, viene creato un utente di test nel database. Questo utente permette al tutor di testare le funzionalità di autenticazione e altre funzionalità che richiedono un utente registrato, senza la necessità di registrarsi manualmente ogni volta.

La esecuzione in background degli ambienti di sviluppo come Vite e Artisan permette al tutor di proseguire con altre attività mentre gli ambienti sono completamente operativi e pronti per la valutazione.

Lo script recupera le rotte configurate dal progetto dello studente e le stampa in console, pronte per essere aperte dal tutor. Il tutor può quindi selezionare quale rotta aprire direttamente dalla console.

Infine, la pulizia post-valutazione è essenziale per mantenere l'ambiente di lavoro pulito e pronto per il prossimo studente. Questo include la terminazione di tutti i processi avviati, l'eliminazione dei database creati e la rimozione dei file scaricati, assicurando che non vi siano residui che possano influenzare le valutazioni successive.

### Configurazione

1. Assicurati di configurare correttamente il file config.env. è sufficiente la porta di connessione al db, il nome del db può rimanere 'tritalaravel'
2. Loggati in montessori
3. Recati alla pagina di recap degli esercizi `/montessori-v2/classrooms/full-stack-developer-[numero classe]/teaching`
1. Apri il network e trova il percorso dell'API `/montessori/api/v2/students/exercises-table-recap?classroom_slug=full-stack-developer-[numero della tua classe]`.
2. Salva il payload della chiamata indicata precedentemente in una variabile `obj` all'interno della console.
3. Esegui questo script nella console del browser:

    ```javascript
    Object.keys(obj.students).forEach(item => console.log(obj.students[item].github_user))
    const lista = [];
    Object.keys(obj.students).forEach(item => {
        lista.push(`"${obj.students[item].github_user}"`);
    });
    console.log(lista.join(",\n    "));
    ```

4. Copia e incolla l'elenco degli user-github dei tuoi studenti dentro il file `students.php` sotto forma di array:
    ```php
    return [
        // Incolla qui l'elenco degli user-github
    ];
    ```
   
### Modalità di Uso

Lo script offre diverse modalità d'uso per facilitare la selezione e la valutazione degli studenti:

1. **Selezione Singolo Studente**: Inserire il numero relativo allo studente stampato a video.
2. **Prima Metà o Seconda Metà**: Inserire "prima metà" o "seconda metà" per analizzare automaticamente tutti gli studenti nella prima o seconda metà della lista, utile in caso di suddivisione della classe tra tutor.
3. **Continua da un Numero Specifico**: Inserire "da [numero]" per iniziare l'analisi da uno studente specifico e continuare con gli altri, utile in caso di ripresa della verifica dopo un'interruzione.

### Utilizzo
Per utilizzare lo script:
- Modifica l'array degli studenti nel file `config.php`.
- Assicurati di inserire le porte corrette di MySQL nel file `configs/.env`.
- Esegui lo script con il comando `php run.php [repo-esercizio]`.
- Segui le istruzioni visualizzate nella console.
- 
### Requisiti minimi
Per utilizzare lo script, assicurati di avere:
- PHP compatibile con la versione di Laravel degli studenti.
- MySQL per la gestione del database.
- Composer per l'installazione di Laravel - Se non presente lo script lo caricherà per te.
- npm (Node) per l'installazione e l'esecuzione di Vite.



### Problemi Noti
- **Rimozione delle Repo Scaricate**: Problemi nel rimuovere completamente le directory scaricate a causa di file log bloccati.
- **Compatibilità con Windows**: Non implementato su Windows, potrebbero sorgere problemi specifici di sistema operativo.
- **Compatibilità con Linux**: Non implementato su linux, potrebbero sorgere problemi specifici di sistema operativo.

### Come contribuire

Sei invitato a contribuire allo sviluppo di questo strumento. Ogni suggerimento o miglioramento è ben accetto!

Al momento lo script si lancia attraverso il singolo file `run.php`, che seleziona lo script effettivo in base al sistema operativo:

1. `_linux.php`
2. `_mac.php`
3. `_windows.php`

Attualmente, soltanto il file `_mac.php` contiene "l'algoritmo" dello script. Se vuoi contribuire per Windows o per Linux, copia tutto il contenuto dello script per macOS nel file corrispondente al tuo sistema operativo e apporta le modifiche necessarie per risolvere eventuali errori specifici del tuo sistema operativo.

Quando comprenderemo le differenze tra i sistemi operativi, potremo migliorare ulteriormente la compatibilità e l'usabilità dello script e unificheremo la procedura in un singolo file.

