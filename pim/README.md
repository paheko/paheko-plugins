# Agenda et contacts

**ATTENTION :** Cette extension est développée à titre bénévole pour mes besoins personnels.
Merci de ne pas vous attendre à voir des évolutions de cette extension.

Cette extension permet d'avoir de multiples agendas et un carnet d'adresse pour chaque membre de l'association.

## Fonctionnalités générales

- Chaque membre dispose de ses agendas et son carnet d'adresse
- Quand un membre est supprimé, ses agendas, événements et contacts sont supprimés
- Synchronisation avec des clients CalDAV/CardDAV (mobile, Vivaldi, Thunderbird, etc.)

## Agenda

- Possibilité d'avoir plusieurs catégories (aussi appelés calendriers ou agendas dans d'autres applis)
- Chaque catégorie peut avoir une couleur et un délai de rappel personnalisé
- Un événement peut être défini sur plusieurs jours
- Duplication d'événement
- Lien vers la carte pour l'adresse de l'événement
- Sélection rapide de plusieurs jours
- Reconnaissance automatique des heures dans le titre de l'événement
- Affichage des jours fériés
- Navigation par mois et année
- Import de fichier iCalendar (`.ics`), par événement (`VEVENT`), ou par agenda (`VCALENDAR`)
- Export d'agenda au format iCalendar

### Reconnaissance automatique des heures dans le titre de l'événement

Quand on est dans le champ "Titre" d'un événement, on peut y entrer l'heure de début et de fin de l'événement, elles seronts automatiquement reconnues.

Exemple, si on entre le titre suivant :

```
17h-19h30 Théâtre
```

L'heure de début et de fin de l'événement seront automatiquement renseignées pour être `17h` et `19h30`. Les heures seront ôtées du titre qui sera donc juste "Théâtre".

Exemples d'autres combinaisons :

```
5.30-12.45 Travail
12h Repas
11:30-13 RDV
```

## Contacts

- Nom, prénom, numéros de téléphone, adresse, email, site web, adresse postale, notes, date d'anniversaire
- Photo du contact
- Archivage des anciens contacts
- Impression de carnet d'adresse en PDF
- Export de tous les contacts au format VCard (`.vcf`)
- Import de contacts au format VCard

## Support CalDAV/CardDAV expérimental

Il est possible de se connecter avec CalDAV/CardDAV à ses données à l'adresse suivante :

```
https://adresse.association/p/pim/
```

Cette fonctionnalité est expérimentale, et des données peuvent être corrompues ou supprimées suite à des bugs !

Elle peut aussi être supprimée à tout moment.

### Limitations

L'implémentation CardDAV/CalDAV est limitée :

* Il n'est pas possible de créer un autre carnet d'adresse
* Il n'est pas possible de supprimer le carnet d'adresse par défaut
* Il n'est pas possible de modifier les métadonnées du carnet d'adresse par défaut (son nom restera "Contacts")
* Il n'est pas possible de créer un nouvel agenda (mais ça reste possible depuis l'interface web)
* Il n'est pas possible de supprimer un agenda (mais ça reste possible depuis l'interface web)
* Les photos des contacts n'apparaissent pas dans Thunderbird ([bug de Thunderbird](https://bugzilla.mozilla.org/show_bug.cgi?id=1947052))
