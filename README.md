contao-documentation
====================

Documentation parser for contao/docs
Ein Dokumenten Parser für contao/docs


contao-documentation funktioniert nur, wenn man den Extension-Ordner in documentation umbenennt.

Die Erweiterung scheint für Contao 2.x zu sein, da der plugins Ordner ab 3 nicht mehr im root liegt.

Das Inhaltselement kann nur auf einer Seite liegen, die exakt den gleichen Alias hat wie die .md Datei, welche man ausgeben möchte, incl. Groß-/Kleinschreibung. Der Alias bzw. der Dateiname darf nicht weniger als 4 Zeichen lang sein. Beispiel: 06-Data-Container-Arrays

Ein Beispiel für die Felder im CE:
User: contao
Repository: docs
Branch: 3.1
Path: manual/de/

btw Die Erweiterung ist dafür da, um GitHub Hilfeseiten mit dem Suffix md, die mit dem GitHub-Markdown formatiert sind, in seinem Contao FE in einem Artikel als CE auszugeben.

ps Die Erweiterung findet man nicht im ER. Nach manueller Installation DB-Aktualisierung nicht vergessen.
