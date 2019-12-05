# Graph

Module de visualisation de données sous forme graphique pour Omeka Classic

Il nécessite le plugin Item Relations modifié par l'ITEM : https://github.com/ENS-ITEM/ItemRelations.

# Dépendances

Ce plugin nécessite les plugins suivants :

- [ItemRelations](https://github.com/ENS-ITEM/ItemRelations)
- [FileRelations](https://github.com/ENS-ITEM/FileRelations)
- [CollectionRelations](https://github.com/ENS-ITEM/CollectionRelations)

# Présentation

Le plugin crée automatiquement des pages pour tous les graphes : 

- graphitem/xxx : Graphe des relations de l'item xxx
- graphcollection/xxx : Graphe des relations au sein de la collection xxx
- graphall : Graphe des relations de l'ensemble des données (attention si vous avez beaucoup de relations)
- graph/choix : page permettant de créer des graphes personnalisés

Un lien devrait apparaître sur les pages item (*items/show*) et collection (*collections/show*) menant vers les graphes correspondants.

# Credits

Ce plugin utilise la librairie Vis.js (http://visjs.org/).

**Réalisé avec le soutien du consortium Cahier.**

Nouvelle version réalisée avec le soutien de l’[IRIHS – Université de Rouen Normandie](https://irihs.univ-rouen.fr/).

