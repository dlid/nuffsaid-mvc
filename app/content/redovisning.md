Redovisning
====================================

Inloggningen är återanvänd från tidigare kursmoment men jag implementerade även konceptet "userContext" där varje inloggning sparas undan. Där lagras även information om när användaren senast var aktiv, och om man är aktiv under en längre tid så loggas man ut.

Att registrera en användare gör man via registreringslänken uppe till höger. Sidan innehåller lite information om vad sidan handlar om och vilka de tänkta användarna är. Registreringsformuläret använder sig av CForm-lösningen som vi ju lärt oss känna i tidigare moment.

En användares profil fick lida lite då jag hade lite tidsbrist. Man får uppdatera sin e-post och namn men inte så mycket mer. Användarens avatar laddas från gravatar.

Det finns en sida för att lista frågorna och sortera dem på olika sätt.

Det finns en enkel sida för taggarna där man kan söka efter taggarna och därifrån länkas till frågorna som är taggade med den taggen.

Den finns också en enkel sida för användarna där man också kan söka efter användarna.

Om-sidan innehåller information om mig och en länk till rapportsidan.

Startsidan innehåller en hel del information - senaste frågorna, de mest populära taggarna och de mest aktiva användarna finns med.

En användare kan ställa frågor, skriva svar och kommentera svar och frågor. I databasen löste jag detta
genom att lagra alla dessa som "contributions" i en enda tabell med interna relationer.

Klickar man på en användare får man se dennes profil, och därifrån kan den inloggade användaren ändra sin profil.
På användarens sida listas dennes aktiviteter samt dennes frågor och svar.

Frågorna kan ha taggar kopplade, och jag är ganska nöjd med hur det går till när man skriver frågorna
och lägger till taggarna. Här hanteras om taggarna redan finns eller om de måste skapas.

Taggarna följer med i listorna och inne när man tittar på frågorna. Klickar man på en tagg så listas
alla inlägg som  är taggade med samma tagg.

Markdown fungerar i alla lägen för frågor, svar och kommentarer.

Lösningen ligger på [github](https://github.com/dlid/nuffsaid-mvc/). Jag önskar jag hade haft tid att
bryta upp lösnigen på ett snyggare sätt och paketera det hela bättre. Men nu finns den åtminstone
på plats och går att använda om man laddar hem den. Jag kopplade på Travis och scrutinizer med ambitionen
att få det snyggt och fint, men jag hade inte tid att optimera koden så mycket alls tyvärr.

Krav 4: Frågor
-----------------------------------

Jag har byggt in så att man kan acceptera ett svar på sin egna fråga som det "rätta" svaret.
Man kan även ångra sig om man vill.

Dessutom kan man stänga en fråga om ingen verkar intresserad av att svara på den. Då
visas den inte längre som "öppen".

Om man röstar upp en fråga så får den användare ryktespoäng, och om man röstar ned
så dras poäng av. Samma sak om man accepterar ett svar som "rätt" - då får personen
som skrev svaret en stor mängd poäng. Ranken för varje fråga/svar visas till vänster
om frågan, och för frågorna visas det även i listorna.

Krav 5: Användare 
-----------------------------------

Det mesta man gör ger aktivitetspoäng:

* Skriva fråga
* Skriva svar
* Kommentera
* Rösta upp
* Rösta ned
* Acceptera svar

Man har alltså två typer av poäng. Ett som baseras på hur andra röstar på dig, och
den andra hur aktiv du är på sidan.

Jag har inte gjort någon summering av aktivitet + rykte för att skapa ett enda
värde för användaren. Mattematik är min svagaste sida så jag orkade inte försöka.

Jag tycker däremot att det blev rätt så bra när de är separerade också.


Krav 6: Det lilla extra
-----------------------------------

Jag lade ned väldigt mycket tid på att få till databasstrukturen med användare,
aktiviteter, frågor, svar och allt annat.

Det finns en tabell med aktiviteter där det också definieras hur många ryktespoäng
respektive aktivitetspoäng man får. När en användare gör något så skapas en koppling
mellan användaren och en aktivitet i en tabell som heter "useractivities".

Med hjälp av den tabellen lagras allting som användare gör, och också all information
om hur användaren fått eller förlorat poäng.

Om man går in och röstar på ett svar så skapas två aktiviteter - en för personen
som röstar som får aktivitetspoäng för att denne deltar aktivt på sidan och röstar
på inlägget. Sedan får också den som skrivit det uppröstade inlägget en aktivitet
där denne får ryktespoäng.

Här är en bild på hur databasen ser ut: http://www.student.bth.se/~dali14/phpmvc/kmom07/webroot/img/dbdiagram.png

Den innehåller lite extra kolumner och så just nu, men det var mycket för att
jag hade planerat att hinna med mycket mer.

Hur har det gått?
-----------------------------------

Det har inte varit lätt denna gång - heltidsarbete, barnkalas och
företagstillställningar har stulit min tid. Jag har lagt mycket arbete på detta
projekt men jag hade en stark ambition att göra så mycket mer. Det är tråkigt
att det inte gick, och nu i slutminutrarna här så har det varit väldigt stressigt.

Jag förstår ramverket, jag förstår hur jag bäst separerar innehåll mot logik och 
lägger upp det på bäst sätt. Men tyvärr hann jag inte göra det så snyggt och det
känns extremt tråkigt, men nu är jag bara glad om jag lyckas lämna in detta i tid.

Om kursen
-----------------------------------
Kursen har varit jättebra. Jag tycker det är mycket bättre med kortare deadlines
istället för att ha en stor deadline i slutet av kursen. Det har hjälpt mig att
kunna prioritera kursen då jag i andra fall kanske skulle ha skjutit på det istället.

Jättebra kurs, och jag är väldigt nöjd med upplägg och uppgifter.

