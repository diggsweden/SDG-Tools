![Logo](https://docs.swedenconnect.se/technical-framework/latest/img/digg_centered.png)

---

# SDG Statistik plugin i Matomo
En plugin till Matomo för att automatisera statistik rapportering enligt SDG-förordningen

Vid installation så kan det vara bra att pinga API:et som ska användas för att se att det finns en öppning och att trafiken går fram.

## Installera pluginet från zip
Pluginet packas som en zip-fil och kan importeras på två sätt
1. Importeras direkt i Matomos GUI om användaren har behörighet. Kräver att enable_plugin_upload=1 är satt i Matomo under [General] i config/config.ini.php
2. Packas upp och placeras direkt i plugins i Matomo.
När pluginet installeras kommer ett nytt table skapas i databasen. Table:n tas bort igen om pluginet avinstalleras.

## Uppdatera plugin från zip
För att uppdatera pluginet, installera pluginet på nytt enligt ovan. När pluginet ska uppdateras behöver inte pluginet deaktiveras eller avinstalleras. Om pluginet avinstalleras och installeras på nytt kommer table:n med tidigare requests att rensas, och settings behöver sättas för pluginet på nytt.

## Settings
För att pluginet ska fungera måste settings vara satta.
Endast användare med superadmin-rättigheter kan uppdatera settings.
De settings som finns är följande:
- API Key – API-nyckel för authentisering som sätts i headern i api-anropet.
- Unique ID Url – URL för att hämta unikt id från SDG som används vid postning av statistik.
- Statistics URL – URL till SDG för att posta statistik.
- Site ID – ID:t till siten som statistiken ska tas ifrån. Default är 1.
- PageTitleIdentifier (Optional) En sträng som används för att identifiera sidorna som ska samlas in. Om denna inte är satt så kommer statistik att samlas in för alla sidor på den valda siten. På FK har vi satt ett meta-fält i headern, <meta name="sdg-tag" content="sdg">, för att identifiera vilka sidor som ingår i SDG och sätter då denna setting till SDG.

## Användning
### Custom report
Just nu finns en custom report uppsatt som hämtar statistik från pluginets Archive. Datan filtreras på värdet i settings, så den motsvarar det som hade skickats till API:et för den valda perioden.
### Scheduled Task
Pluginet försöker skicka statistik för den föregående månaden en gång om dagen, så länge som det inte finns något lyckat inskick för den perioden. Scheduled Tasks går att se i GUI:t med pluginet Scheduled Task, som är ett gratis plugin.
### Adminsida
En adminsida under Administration-menyn i GUI:t. Där finns ett table med information om de 10 senaste requesten som är gjorda till SDG-API:et samt finns en knapp för att trigga ett nytt inskick manuellt. Endast användare med admin-rättigheter för den valda siten kan se tidigare inskick och trigga inskick manuellt.
