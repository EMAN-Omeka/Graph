# Graph

Module de visualisation de donn�es sous forme graphique pour Omeka Classic

Il n�cessite le plugin Item Relations modifi� par l'ITEM : https://github.com/ENS-ITEM/ItemRelations.

# D�pendances

Ce plugin n�cessite les plugins suivants :

- [ItemRelations](https://github.com/ENS-ITEM/ItemRelations)
- [FileRelations](https://github.com/ENS-ITEM/FileRelations)
- [CollectionRelations](https://github.com/ENS-ITEM/CollectionRelations)

# Pr�sentation

Le plugin cr�e automatiquement des pages pour tous les graphes : 

- graphitem/xxx : Graphe des relations de l'item xxx
- graphcollection/xxx : Graphe des relations au sein de la collection xxx
- graphall : Graphe des relations de l'ensemble des donn�es (attention si vous avez beaucoup de relations)
- graph/choix : page permettant de cr�er des graphes personnalis�s

Un lien devrait appara�tre sur les pages item (*items/show*) et collection (*collections/show*) menant vers les graphes correspondants.

# Credits

Ce plugin utilise la librairie Vis.js (http://visjs.org/).

**R�alis� avec le soutien du consortium Cahier.**


