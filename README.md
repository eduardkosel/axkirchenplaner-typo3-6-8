# Kirchenplaner für TYPO3 6 bis 8

Die Extension bindet Termine aus der Kirchenplaner-API als TYPO3-Inhaltselement ein. Version **1.0.8** deckt TYPO3 6.0 bis 8.x ab. Die benötigte PHP-Version richtet sich nach der eingesetzten TYPO3-Version.

## Installation per ZIP

1. Lade [ax_kirchenplaner_1.0.8.zip](https://github.com/eduardkosel/axkirchenplaner-typo3-6-8/releases/download/v1.0.8/ax_kirchenplaner_1.0.8.zip) aus dem Release herunter.
2. Lade das ZIP im TYPO3-Backend im Modul **Erweiterungen** hoch und aktiviere **Kirchenplaner**. Der Extension-Key lautet `ax_kirchenplaner`.

Falls der Extension-Manager das Archiv nicht entpacken kann, entpacke es in `typo3conf/ext/ax_kirchenplaner/`. Die Datei `ext_emconf.php` muss direkt in diesem Ordner liegen. Aktiviere die Extension anschließend im Backend.

## Installation in einem Composer-Projekt

Führe die Befehle im TYPO3-Projektverzeichnis aus. Verwende eine Composer-Version, die zum Installer deiner TYPO3-Version passt.

```bash
composer config repositories.axkirchenplaner-typo3-6-8 vcs https://github.com/eduardkosel/axkirchenplaner-typo3-6-8.git
composer require cmft/ax_kirchenplaner:1.0.8
```

Für eine Classic-Installation verwende das ZIP aus dem Release.

## Einrichtung

1. Hinterlege den API-Schlüssel in der Extension-Konfiguration.
2. Binde das statische TypoScript-Template **Kirchenplaner** ein.
3. Füge das Inhaltselement **Kirchenplaner** hinzu und konfiguriere Darstellung und Filter.

Terminliste, API-Filter und Detaildialog wurden mit einer lokalen Test-API auf TYPO3 6.0.0, 6.1.0, 6.2.31, 7.0.0, 7.6.32, 8.0.0 und 8.7.32 im Classic-Modus geprüft.
