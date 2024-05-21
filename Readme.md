# TutorMonster - Strumenti di Automazione per Tutor

## Introduzione
TutorMonster è una suite di strumenti di automazione progettati per assistere i tutor nel processo di valutazione degli studenti. Questa suite contiene vari script, il primo dei quali è "TritaLaravel", mirato a ottimizzare e migliorare il feedback sugli esercizi di Laravel.

## Script TRITALARAVEL

### Descrizione
"TritaLaravel" è uno script automatizzato, attualmente in versione beta, sviluppato per semplificare e velocizzare l'analisi degli esercizi di Laravel. Questo strumento è stato creato per rendere più efficiente il lavoro dei tutor a supporto della loro routine pomeridiana.

### Funzionalità
- **Download delle Repository**: Scarica automaticamente la repo degli studenti.
- **Configurazione Ambiente**: Riconfigura il file `.env` per creare un database con il pattern `[utente]_[esercizio]`.
- **Installazioni e Setup Laravel**: Esegue l'installazione di npm e Laravel (tramite Composer), oltre a vari comandi Laravel come la generazione di chiavi, le migrazioni, il seeding e la creazione di link allo storage.
- **Creazione Utente per Test**: Inserisce un utente di test nel database tramite una query diretta.
- **Esecuzione in Background**: Avvia in background i server Vite e Artisan.
- **Apertura Browser per Valutazione**: Apre Chrome alla porta di default di Artisan per la valutazione manuale.
- **Pulizia**: Dopo la valutazione, termina i processi, elimina il database e rimuove i file scaricati.

### Modalità di Uso
- **Selezione Studente**: Scegli uno studente specifico da analizzare.
- **Analisi della Prima o della seconda metà della classe**: Analizza automaticamente tutti gli studenti nella prima metà della lista.
- **Analisi Continua**: Inizia l'analisi da uno studente specifico e procedi con gli altri.

### Installazione
Per utilizzare lo script, assicurati di avere:
- PHP compatibile con la versione di Laravel degli studenti.
- MySQL per la gestione del database.
- Composer per l'installazione di Laravel - Se non presente lo script lo caricherà per te.
- npm (Node) per l'installazione e l'esecuzione di Vite.

### Utilizzo
Per utilizzare lo script:
- Modifica l'array degli studenti nel file config.php
- Assicurati di inserire le porte corrette di mysql dentro il file configs/.env
- Esegui con `php run.php [repo-esercizio]`.
- Segui le istruzioni in console.

### Problemi Noti
- **Rimozione delle Repo Scaricate**: Problemi nel rimuovere completamente le directory scaricate a causa di file log bloccati.
- **Compatibilità con Windows**: Non implementato su Windows, potrebbero sorgere problemi specifici di sistema operativo.
- **Compatibilità con Linux**: Non implementato su linux, potrebbero sorgere problemi specifici di sistema operativo.
- 
### Contributi
Sei invitato a contribuire allo sviluppo di questo strumento. Ogni suggerimento o miglioramento è ben accetto!
