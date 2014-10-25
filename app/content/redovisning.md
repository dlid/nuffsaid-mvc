Redovisning
====================================

Kmom06: Verktyg och CI
-----------------------------------

I mitt jobb så är det ofta mycket prat om tester, men det prioriteras tyvärr
ofta bort. Därför är jag flyktigt bekant med begreppen, men har ingen praktisk
erfarenhet av att implementera det.

[dlid/cdbyuml](https://github.com/dlid/cdbyuml)
[![build](https://camo.githubusercontent.com/44079a10fe6d08ddc7a050a7db141474775df2ee/68747470733a2f2f7472617669732d63692e6f72672f646c69642f63646279756d6c2e7376673f6272616e63683d6d6173746572)](https://travis-ci.org/dlid/cdbyuml) [![Quality](https://camo.githubusercontent.com/e19498f36e1ea3054adecbd27e50393911a0afa6/68747470733a2f2f7363727574696e697a65722d63692e636f6d2f672f646c69642f63646279756d6c2f6261646765732f7175616c6974792d73636f72652e706e673f623d6d6173746572)](https://scrutinizer-ci.com/g/dlid/cdbyuml/?branch=master) [![Coverage](https://camo.githubusercontent.com/744d3c10d8882d5d05c9cae1679675c3403c869d/68747470733a2f2f7363727574696e697a65722d63692e636f6d2f672f646c69642f63646279756d6c2f6261646765732f636f7665726167652e706e673f623d6d6173746572)](https://scrutinizer-ci.com/g/dlid/cdbyuml/?branch=master)

Efter föregående kursmoment så ville få chansen att bryta upp min modul i klasser
och försöka få koden bättre strukturerad. Innan jag fick denna kommentar så lade
jag faktiskt inte en tanke på att en enda stor klassfil kanske blir rörigt. Jag
tänkte nog mest att ja, det är ju smidigt att ha allt i en fil så slipper man
tänka på beroenden etc.

Men i och med autoloaders så försvinner ju problemet med att man i koden
måste hålla koll på att klasserna är inladdade, och plötsligt öppnar sig en
värld där det blir mycket lättare att dela upp klassen i flera delar.

Jag började med att koppla mitt lilla projekt mot Travis och Scrutinizer för att
få fram lite siffror på hur koden var just nu, och mycket riktigt fick jag
kodkvalitet på ca 4.5 i scrutinizer. Efter det satte jag genast igång och
försökte optimera koden.


Men jag började med att lägga in några tester, och hamnade på runt 50% procent.

Det var väldigt kul att se hur github, Travis och Scrutinizer fungerar tillsammans.
Så fort man checkar in så vet man att man strax får en ny rapport på förbättringar
eller försämringar som man gjort.

Jag blev sittande en del med detta och gick in i de flesta metoder för att optimera dem
upp till en godtagbar nivå. Vissa metoder hade ju blivit extremt stora, och det 
kändes bra att kunna dela upp dem i mindre metoder som hade mer specifika uppgifter.

Till slut kom jag upp i en kvalitetsnivå på 9.82, och ett test coverage på 71%.

Det blev en hel del fokus på att fixa detta och jag inser hur mycket jobb det måste vara
att optimiera projekt som är större än min lilla modul! Kommentaren om att dela 
upp koden i förra kursmomenten blev en ögonöppnare, och ögonen har fortsatt öppnats
under detta moment.

Jag känner att jag har lärt mig enormt mycket om vad som är bra och dåligt skriven
kod. Medan det är kul att titta på scrutinizer och se hur koden blir bättre och bättre
i de analyserna så blir det ju samtidigt väldigt uppenbart att förändringar också
påverkar kodens läsbarhet och det blir lättare för andra att sätta sig in i koden.

Jag skulle vilja gräva mig ned djupare i test-biten också, nu har jag en ganska
grundläggande uppsättning med tester bara. Men jag lade störst fokus på att
bygga om koden den här gången. På grund av det gjorde jag heller inte extrauppgiften.


Kmom05: Bygg ut ramverket
------------------------------------

Rolig uppgift som kändes mycket mindre tung än den föregående. Det var kul att
få använda och lägga upp saker på github och packagist. Det var en väldigt smidig
process tycker jag, och trevligt att det är så enkelt att göra ett eget bibliotek
lättillgängligt för alla som använder composer!

 > Välj en modul som passar uppgiften. Utveckla den och lägg den i ett eget repo på GitHub.

[https://github.com/dlid/cdbyuml](https://github.com/dlid/cdbyuml)

Jag kikade igenom alla förslag på moduler, men valet på modul föll till sist på 
något jag har funderat på ibland och som jag flera gånger skulle ha behövt
ha användning för själv.

Det är väl inte direkt en självklar del i ett ramverk, utan snarare ett litet
hjälpmedel som kan komma till hands i "åh, jag önskar att jag kunde"-situationer.

Det finns en webbtjänst som heter [yuml.me](http://www.yuml.me/diagram/scruffy/class/draw) där man
med en speciell syntax kan rita upp UML-diagram av olika slag. Min idé har varit
att läsa ut strukturen från en databas, översätta den till yuml:s syntax och sedan
använda yuml.me för att generera ett diagram över databasen.

Jag ville bygga ett enkelt och flxibelt bibliotek som tar ett PDO-objekt eller
annan datakälla (om man t.ex. använder sig av ett bibliotek som CDatabase) för
att skapa dessa diagram för sqlite- och mysql-databaser.


 > Publicera repot som ett paket på Packagist.

[https://packagist.org/packages/dlid/cdbyuml](https://packagist.org/packages/dlid/cdbyuml)

Rätt kul att se hur bra Github och packagist fungerar tillsammans. Jag uppdaterade
och committade lite nya keywords i min composer.json, och det tog inte många sekunder
innan det var uppdaterat på packagist också. Mycket smidigt! Jag hade lite problem
med att sätta upp Webhook:en först.

Jag följde instruktionerna men packagist förstod ändå inte att kopplingen fanns där.
Till slut tog jag bort webhooken samt paketet från packagist och skapade om båda två
igen, och då slog det igenom. Inte helt säker på vad problemet var från början.

 > Testa att din modul fungerar tillsammans med en standardinstallation av Anax MVC.

För att testa mitt paket så tog jag en kopia av min kmom04 och döpte om den till
kmom04-01. Jag lade sedan in mitt paket i composer.json. 

```php
 "require": {
  ...
  "dlid/cdbyuml": "dev-master"
 },
```

Sedan körde jag en composer install, och biblioteket laddades ned och lade sig
helt rätt under vendor-biblioteket.

```
composer install
Loading composer repositories with package information
Installing dependencies (including require-dev)
  - Installing dlid/cdbyuml (0.2)
    Downloading: 100%
```

Jag kopierade över min ANAX Test-kontroller till kmom04-01/webroot och surfade in
på sidan */phpmvc/kmom04-01/webroot/cdbyuml.php*. Det fungerade fin-fint och ser ut
såhär:

![Alt text](img/db-diagram.png "Diagram page")

Samma kontroller ligger här på kmom05 så du kan kika direkt:

* [cdbyuml.php](cdbyuml.php) - Kontrollern
* [cdbyuml.php/dbdiagram](cdbyuml.php/dbdiagram) - Bilden
* [cdbyuml.php/dbdiagram](cdbyuml.php/dbdebug) - Debuginfo

Man får ju se till att mappen där cache-filerna skriv är skrivbar också såklart.

 > Dokumentera hur man gör för att använda din modul tillsammans med Anax MVC.

Jag lade in en [readme-fil](https://github.com/dlid/cdbyuml/blob/master/ANAX.md) för hur man kommer igång med CDbYuml i Anax tillsammans
med CDatabase. Exempelkontrollern som skapas i readme-filen finns även med i paketet.



Kmom04: Databasdrivna modeller
------------------------------------

Det här var ett tidskrävande kursmoment för mig, men jag tror att jag fick lära
mig mycket på vägen. Jag har svårt att sätta fingret på varför det tog sådan
tid, men det har att göra med att komma in i MVC-tänket på ett sätt som gör att
jag både följer mönstret och är nöjd själv tror jag. "User"-sidan
till exempel började jag om med både en och två gånger, och än idag är jag inte
säker på att jag är helt nöjd. Jag tycker t.ex. kontrollern innehåller för mycket
redundant data - t.ex. callbackfunktionen för formuläret när valideringen inte
lyckas, och alla formulär borde kunna göras snyggare också än att definiera dem
i varje action. Kanske bryta ut dem till separata klasser istället, då blir 
koden lite snyggare åtminstone.

> Vad tycker du om formulärhantering som visas i kursmomentet?

Ett smart sätt att enkelt skapa formulär och alltid ha tillgång till de typer
av validering man kan använda. Det är ju även lätt att utöka CForm så att man
kan ha flera typer av validering om man så önskar. Jag gillar "extend"-tänket
med alla konfigurationerna som syns överallt, där alla inställningar hanteras
som arrayer som har defaultvärden som lätt kan ersättas. Alla tester lade jag
in under [test/Cform](test/cform).

För att felmeddelandena skulle visas på "mitt" sätt så utökade jag CForm med en
callback funktion för "[output-write](source?path=src/HTMLForm/CForm.php#L58)". Om man definierar en callbackfunkton så
[anropas den istället](source?path=src/HTMLForm/CForm.php#L197) för att outputen skrivs i HTML:en för formuläret.

Jag lade även in så attributen [novalidate](http://www.w3.org/TR/2012/WD-html5-20121025/form-submission.html#attr-fs-novalidate)
går att sätta på formuläret. Det var lättare att testa valideringen av formuläret
när inte webbläsarens validering kom i vägen. I många fall vill man förmodligen
inte stänga av valideringen i webbläsaren, men när man testar så var det rätt skönt. 

Valideringen för checkbox gillade jag inte riktigt. Om man inte kryssade i kryssrutan
så fick man ett felmeddelande, men checkboxen kryssades också i automatiskt. Jag fixade
det genom att [rensa värdet](source?path=src/HTMLForm/CForm.php#L438) om det finns valideringsfel på checkboxen. 

> Vad tycker du om databashanteringen som visas, föredrar du kanske traditionell SQL?

Jag fick uppgradera min utvecklingsmiljö från PHP 5.3 till 5.5 för att få tillgång
till funktionen [password_hash](http://docs.php.net/manual/en/function.password-hash.php) så
det tog lite tid att fixa, men det gick åtminstone bra. Jag hade kvar version 5.3 eftersom
mitt webbhotell har 5.3, men jag tycker väl att de borde uppgradera snart också!

Sedan jag började använda Entity Framework och Linq i .NET så har jag velat ha 
något liknande för PHP i mina egna små projekt. Jag har sneglat på [propel](http://propelorm.org/)
men har inte haft tid att titta närmare på det. För egen del har jag mest gjort
mindre lösningar med enklare klasser som helt enkelt kör en SQL-fråga och
binder parametrar.

Den stora fördelen är ju att det är lättare att skriva frågorna, men nackdelen
är ju att man kanske inte riktigt har koll på den SQL som genereras. Man får 
lita på att ramverket skapar den bästa SQL-koden för frågan, och om man blint litar
på det så kanske man hamnar i situationer där man inte förstår varför något
går lite segt. Ofta är ju ramverken väldigt optimierade och bra, men det kan vara
bra att försöka titta på den kod som genereras också så att man får förståelse
för vad som händer.

> Gjorde du några vägval, eller extra saker, när du utvecklade basklassen för modeller?

Jag valde att lägga allt i CDatabaseModel-klassen så kommer den att vara användbar för alla
databasmodeller man kan komma att vilja ha. Det bygger ju på att alla tabeller man
skapar har ett id som primärnyckel, men det brukar man ju normalt ha så det tror jag
knappas är någon nackdel. Det är också lätt att överlagra t.ex. getSource i en klass om
om man vill ha ett helt annat namn på tabellen.

> Beskriv vilka vägval du gjorde och hur du valde att implementera kommentarer i databasen.

Jag stångades ju ett tag med att få till [users-biten](users) och om man jämför så är jag
mycket, mycket mer nöjd med hur strukturen och koden blev för den uppdaterade
kommenteringsfunktionen. När kommentarsfunktionen var klar så fick jag åter lite
hopp om att jag inte är hopplöst vilse i MVC-strukturen.

Jag valde att implementera kommentarerna på samma sätt som Users. Jag lade märke till att
det inte blev rätt datum i databasen med DATE_RFC2822 så jag ändrade det till
'Y-m-d H:i:s' och då blev det rätt.

Jag skapade två tabeller - en för själva sidorna som man kommenterar och en för kommentarerna.
Tidigare använde jag en hashad sträng för att identifiera de unika sidorna, men
om man listar alla kommentarer så vill man kanske se vilken sida kommentaren gäller. Så då
ville jag inte spara URL:en för varje kommentar utan skapade istället en ny tabell för det.

Alla kommentarer kan tas bort via [comment/setup](comment/setup).

Kommentarerna kan listas via [comment/all](comment/all).

För att bibehålla utseendet på min kommentarfunktion så utökade jag CFormElement så att 
man kan [skicka in en renderingsfunktion](source?path=src/Comments/CFormComment.php#L14).
Renderingsfunktionen [anropas](source?path=src/HTMLForm/CFormElement.php#L318) i och med elementets
GetHTML() och skickar med allt man kan behöva för att själv rendera ut elementet 
i godtyckligt format.

Formuläret ligger som en [egen klass](source?path=src/Comments/CFormComment.php#L1)
och det kändes som att allt blev mycket snyggare när man delade upp det på det viset.
För att lägga in kommentarfunktionalitet i en region så kan man använda funktionen
[addToView](source?path=src/Comments/Comment.php#L117) som sköter detta åt dig.

I kontrollern finns action för [add](source?path=src%2FComments%2FCommentController.php#L121),
[remove](source?path=src%2FComments%2FCommentController.php#L166), 
[edit](source?path=src%2FComments%2FCommentController.php#L57) samt [view](source?path=src%2FComments%2FCommentController.php#L40)
för att visa kommentarer för en viss sida och [all](source?path=src%2FComments%2FCommentController.php#L20)
för att lista alla kommentarer som finns i databasen.

Jag har inte implementerat någon kontroll så att man endast kan redigera och
ta bort ens egna kommentarer under aktuell session, men det vore såklart ett logiskt
nästa steg, eller om man kopplar ihop det med en inloggad användare.

Det har varit en ordentlig resa under detta kursmoment och jag tror det syns i koden. Koden
för Users (mer specifikt [UsersController](source?path=src/Users/UsersController.php))
blev väldigt stor och känns lite rörig med alla formulär i nästan varenda action. Men jag
hoppas som sagt skillnadens syns i och med implementeringenn av kommentarerna där
jag tycker jag lyckades strukturera upp det betydligt bättre. Om jag återanvänder koden
för användare inför projektet så vet jag ju åtminstone hur jag kommer snygga till det!

Databasen ligger lite oskyddat i webroot nu och den ska ju helst läggas så att
ingen kommer åt den via en webbläsare. Men nu är det iaf lätt att [ladda hem](dali14.sqlite)
och kika på om man vill.

> Gjorde du extrauppgiften? Beskriv i så fall hur du tänkte och vilket resultat du fick.

Hade för avsikt att göra det men hann inte eftersom uppgiften drog ut på tiden.

Kmom03: Bygg ett eget tema
------------------------------------

> Vad tycker du om CSS-ramverk i allmänhet och vilka tidigare erfarenheter har du av dem?

Jag har aldrig använt LESS eller SASS i något projekt tidigare, men jag har testat LESS
med PHP förut men har inte gjort något mer avancerat. Jag har även sneglat på SASS men
har inte använt det. Som jag förstått det så är den stora skillnaden mellan LESS och SASS
att SASS kompileras på serversidan medan det för LESS finns möjligheten att göra det
på klientsidan med JavaScript.

Personligen tycker jag att man ändå borde hantera kompileringen på serversidan och 
inte förlita sig blint på att JavaScript är aktiverat i webbläsaren. Men förutom
att SASS inte riktigt har stöd för kompilering på klientsidan så har jag inte
sett några större skillnader mellan de två.

Anledningen till att jag började med LESS var att jag testade Bootstrap och då
fanns bara en LESS-version tillgänglig. Nu finns däremot en SASS-version också.

> Vad tycker du om LESS, lessphp och Semantic.gs?

Även fast jag tidigare har använt LESS så känns det fortfarande lite magiskt. Jag tycker
att det hierarkiska sättet att skriva på känns väldigt naturligt.

LESS är vad CSS borde ha varit från början. Mixins är otroligt kraftfullt, 
och det gör det så mycket enklare att dela med sig av och återanvända kod.

Jag satt nyligen i ett .NET-projekt där vi började kika på att kompilera SASS-kod
på sreversidan på ett liknande sätt som lessphp gör. På grund av olika omständigheter
så var det väldigt svårt att få till, och vi fick istället kompilera det i ett
script i och med deployen istället. Jämfört med det så är lessphp väldigt enkelt
och lätt att använda.

Jag använde mycket Bootstrap tidigare och tröttnade lite på att man fick för mycket
layout. Jag ville endast ha grid-biten. Då hittade jag [Dead Simple Grid](http://mourner.github.io/dead-simple-grid/) som
jag testade, och semantic.gs har ett liknande - väldigt grundläggande - upplägg.

Med ramverk som bootstrap kan man ganska lätt få väldigt mycket grejer på köpet
som man aldrig använder, om man bara är ute efter grid-systemet så tycker jag det
är skönt att det finns alternativ som Semantic.gs och Dead Simple Grid.

> Vad tycker du om gridbaserad layout, vertikalt och horisontellt?

Det horisontella rutnätet är jag ju bekant med sedan tidigare, jag tror det är
ett ganska vedertaget sätt att bygga sidor nu i.o.m. alla ramverk som hjälper
till med det. Speciellt nu när allt byggs mer och mer responsivt så tror jag att
det horisontella rutnätet har blivit en de facto standard - ungefär som tabeller
var nyckeln till att designa sidor i begynnelsen.

Vertikalt rutnät var däremot något jag aldrig ägnat en tanke åt tidigare. Det
magiska numret och att 16px var den ideala storleken för typsnitt på webben
hade jag heller inte hört talas om. Det var lite meck att få till så det blev rätt 
med alla element som fanns i Lydia:s HTML för typografi, men jag tror att jag
till slut fick det att stämma.	

Detta var en ny aspekt av designtänkandet som jag tycker lade till en spännande
aspekt.

> Har du några kommentarer om Font Awesome, Bootstrap, Normalize?

Font Awsome beskriver sig själv och det är jättesmidigt att ha tillgång till
alla ikoner på ett så enkelt sätt. 

Bootstrap nämnade jag här lite tidigare och jag har bara gott att säga om det. Man
får mycket på köpet, men i vissa fall kanske det är mer än man vill ha. En bra grej
är ju då att helt enkelt använda LESS och välja ut precis de delar man behöver.

Normalize brukar jag använda, men jag har ingen stenkoll på skillnaderna som
normaliseras mellan olika versioner av webbläsare. Jag kan tänka mig att skillnaderna
är störst för äldre webbläsare, men det kanske även finns skillnader i nya versionerna.

> Beskriv ditt tema, hur tänkte du när du gjorde det, gjorde du några utsvävningar?

Jag ritade en liten logotyp som jag scannade in och fixade till med Inkscape. Jag
ville ha ett ganska platt tema med mörk bakgrund, och bilden med polygonen samt
den lilla polygonen i footern tycker jag lyfte upp designen.

Med snygga bilder är det ju enklare att fixa en bra design och jag tog de flesta
bilderna från [unsplash.com](https://unsplash.com/) förutom den på "me"-sidan
som jag tagit själv.

Responsivt så finns det tre brytpunkter.

* När fönstret är minst 960px så låser jag bredden till 960
* När fönstret är 768 eller mindre så visas varje kolumn på separata rader (förutom footer-kolumnerna)
 * Bilden för Main banner justeras så att den ser lite bättre ut på mindre skärm
* När man har större skärm än 1622 så visar jag ev. "flashes" till vänster om wrappern istället. Inte säker på att det är optimalt, men jag testade åtminstone. Om någon vill använda temat så är det ju lätt att fixa så att den visas som vanligt igen.

Utsvävningarna blev väl just "flash"-delen. Har inte övertygat mig själv helt och hållet
att det är bra att lägga ut flash-delarna till vänster om själva wrappern om skärmstorleken tillåter det. Men jag ville som sagt
testa att utnyttja den större yta på något sätt eftersom jag låser bredden på min wrapper vid 960px.

Jag fixade navigeringen så att den blir lite mer touchvänlig om man har en mindre
skärm, och undermenyn fungerar också bra åtminstone till nivå ett.

För övrigt så för Font Awsome och Semantic.gs med sig lite CSS som inte validerar,
men det är jag medveten om.

> Antog du utmaningen som extra uppgift?

Jag valde att prioritera tiden till finjusteringar av layouten; den responsiva
delen och inte minst det vertikala rutnätet som fångade mitt intresse. Så den
här gången gjorde jag inte extrauppgiften.

Kmom02: Kontroller och modeller
------------------------------------

> Hur känns det att jobba med Composer?

Jag var på ett seminarium för en tid sedan där jag fick se hur man jobbar med
[yo](https://www.npmjs.org/package/yo), 
[bower](http://bower.io/) och 
[grunt](http://gruntjs.com/) och blev rätt imponerad. Själv är jag ingrodd
Windowsanvändare med Linuxwannabe - och jag har en tröskel att komma över när
det gäller terminalen och alla utsökta möjligheter som verkar finnas där.

När jag fått igång composer på min lokala miljö så fick jag lite flashback på
seminariet och tyckte det var rätt kul att få det att fungera i Windows på det
sättet. För att testa på studentservern fick jag dock [gå tillbaka lite](http://dbwebb.se/kunskap/att-koppla-upp-dig-mot-en-server-med-ssh-via-terminalen)
för att kolla hur jag kopplade upp mig med putty, men efter det var det inga 
problem.

-----

> Vad tror du om de paket som finns i Packagist, är det något du kan tänka dig att använda och hittade du något spännande att inkludera i ditt ramverk?

Min första tanke var att det är ju fantastiskt smidigt! Åter igen så föll tankarna
tillbaka på seminariet där han med några kommandon kunde ladda ned en mängd paket
och alla dess dependencies. Jag kan helt klart se nyttan i att lägga upp och
hantera paket på detta sätt - och att via composer enkelt kunna hålla referenserna
uppdaterade är ju också väldigt smidigt!

Jag är dock inte van att sitta med kommandopromoten öppen och jobba så pass aktivt
med den hela tiden, men det är något jag gärna skulle vilja vänja mig vid.

Jag hittade en mängd paket för LESS och minifiering av CSS, JavaScript och HTML som
jag tyckte så intressanta ut. Sedan fanns det ju paket för t.ex. fotoalbum, 
porfolios och sökning m.m. Ett riktigt smörgåsbord som jag måste kika i nästa
gång jag ska bygga något.

-----

> Hur var begreppen att förstå med klasser som kontroller som tjänster som dispatchas, fick du ihop allt? 

Det begrepp som jag inte riktigt hade hört tidigare var "dispatchern". Men jag tycker
att begreppen sitter rätt bra nu, och i instruktionerna så läste jag meningen som
sammanfattade allt på ett väldigt bra sätt:

>"En frontcontroller som tar emot requesten och dispatchar till controllern
>som använder sig av modellen för att hämta och uppdatera informationen och därefter
>används vyn för att rendera resultatet till en HTML-sida.""

Att sitta och vidareutveckla kommentarfunktionen ledde ju till att jag fick
skapa nya vyer, utöka CommentController, skapa en ny klass som laddas in i
DI, få undersöka vilka funktioner som finns för t.ex. session, request och response.

Även om jag förstod begreppen innan så var det nyttigt att lösa olika utmaningar med
hjälp av ramverket för att få en bättre förståelse. 

-----

> Hittade du svagheter i koden som följde med phpmvc/comment? Kunde du förbättra något?

Jag samlade lite funktionalitet i en ny klass som jag kallade [PageComments](source?path=vendor/phpmvc/comment/src/Comment/PageComments.php).
När man lagt in den klassen och CommentController så är det lätt att lägga in all kommentarfunktionalitet
med funktionen [addToPage](http://localhost:2014/phpmvc/kmom02/webroot/source?path=webroot/index.php#L48).

Lade även in extrafunktionerna med att den större delen av kommentar-formuläret visas
när textarean får fokus. Om man tömmer textfältet och textarean inte har fokus 
så döljs det igen.

Även gravatarfunktionaliteten är inlagd, både med PHP och JavaScript så att den
uppdateras automatiskt när man skriver in en e-postadress och fältet förlorar fokus.

Jämfört med Icke-MVC-Anax så tycker jag redan att den här versionen känns bättre
att jobba med. Strukturen känns tydligare i och med att man alltid har DI i grunden
där man kommer åt allt man kan behöva.

Jag lade in lite validering så att man måste fylla i åtminstone kommentar och namn.

Jag fixade även en liten bugg där [CSession::get](source?path=src/Session/CSession.php#L68)
returnerade null istället för det defaultvärde man kan skicka in som parameter.

Nästa logiska steg vore ju att spara kommentarerna i en databas, och med fördel
att använda något spam-filter som t.ex. Akismet.

Kmom01: PHP-baserade och MVC-inspirerade ramverk
------------------------------------

> Vilken utvecklingsmiljö använder du?

Jag sitter med Windows 8.1, XAMPP v3.2.1, PHP 5.4.22 och Sublime Text 3. Jag
har min utvecklingsmiljö portabel i en mapp i min DropBox som gör att det är
ganska lätt att utveckla var jag än sitter eller vilken dator jag än har. Det
ger mig en flexibilitet som gör det lite lättare att läsa dessa kurser eftersom
jag samtidigt jobbar, åker runt mycket och sitter på många olika datorer.

> Är du bekant med ramverk sedan tidigare?

Nej. Jag har vid tillfällen börjat kika runt lite med ambitionen att läsa och
lära mig mer om hur dessa ramverk fungerar så att jag själv kan bygga någonting
liknande med en bra standard. Men det har slutat med att andra saker har fått
prioritet istället.

Jag tyckte guiden om Me-sidan med ANAX MVC var bra, men kände mig också rätt
förvirrad. Det tar jag som ett gott tecken såhär i början av kursen

Jag gillade kapitlet om "Dependency Injection/Service Location" i dokumentationen
för Phalcon och fick både mersmak och huvudvärk då jag insåg hur mycket jag
skulle vilja lära mig mer om detta!

> Är du sedan tidigare bekant med de lite mer avancerade begrepp som introduceras?

Det var ett gäng nya begrepp som jag inte har haft koll på tidigare. T.ex. 
är "Dispatcher" något som jag nog aldrig haft ett namn på tidigare och som
vad jag förstått är koden som tar reda på vilken kontroller som ska användas.

Dependency Injection är något jag inte heller haft en term för, men i varje
litet hobbyprojekt jag gjort så har ju precis de tankarna varit i fokus många
gånger, men de har aldrig riktigt landat.

Övriga begrepp är bektanta. Men även om jag känner till begreppen så skulle
jag inte påstå att jag har någon djupare förståelse för dem, så jag ser fram
emot att lära mig mer!

> Din uppfattning om Anax, och speciellt Anax-MVC?

Som sagt, något förvirrande till en början. Det är många filer och många
moduler i ramverket. Jag blev först lite fundersam över att det allra första steget
var att ladda ned det färdiga ramverket, jag hade ju velat koda själv för att
förstå det djupare. 

Men jag sneglade på kmom02 och ska fortsätta läsa länkarna i kmom01, och jag
tror att jag kommer att ha rätt mycket att göra utan att skriva allt från grunden!

Från tidigare kurser har Anax kännts som en väldigt smidig mall att använda
för att snabbt få upp en sida, och vad jag sett hittills av Anax-MVC så är det
en väldigt spännande förbättring.

Jag lade även upp Anax-MVC på min Github med ambitionen att hålla den uppdaterad
under kursens gång.