# Gestion de stock de vélos

Cette extension permet de gérer le stock de vélos d'un atelier associatif de réparation de vélos.

## Avertissement

Cette extension a été conçue pour les besoins de l'atelier de La rustine, il est possible qu'elle ne convienne pas à vos besoins spécifiquement.

Nous ne faisons pas de développements pour d'autres ateliers, désolé.

## Fonctionnement

Dans notre atelier, tous les vélos sont étiquetés (étiquette fabriquée avec un morceau de chambre à air, et du marqueur blanc). Chaque étiquette comporte un numéro unique.

Quand on reçoit un vélo, on entre le vélo dans l'extension de gestion de stock, avec un bref descriptif. On prend la première étiquette libre dans le tiroir à étiquettes, et on la met sur le vélo : ça sera le numéro du vélo. Normalement le logiciel garde trace des étiquettes numérotées et propose donc la première étiquette disponible par défaut.

Quand un vélo est réparé, on lui ajoute une seconde étiquette avec le prix. À ce moment-là, on modifie la fiche du vélo pour lui donner un prix.

Les vélos ayant un prix apparaissent sur notre site web en temps réel.

Quand on vend un vélo on passe par la gestion du stock, pour imprimer un contrat de vente (qui peut être réimprimé plus tard).

Quand on vend le vélo ou qu'on le démonte, on récupère l'étiquette, le vélo est retiré du stock sur le logiciel.

### Rachat de vélos

Nous permettons à nos adhérents de nous revendre un vélo s'il a été acheté chez nous. Cela permet de proposer une alternative à la location pour les étudiants, tout en n'ayant aucun des problèmes de la location (vol, casse, etc.).

L'extension propose donc le rachat de vélo, généralement au tiers du prix d'origine.
