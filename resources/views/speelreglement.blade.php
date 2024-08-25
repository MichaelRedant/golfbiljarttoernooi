@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mt-4">
        <div class="text-center">
            <h1>Speelreglement</h1>
        </div>
        <div>
            <ul class="list-unstyled">
                <li>Art.1 Algemene bepaling van het spel</li>
                <li>Art.2 Aanvang van een partij</li>
                <li>Art.3 Einde van een partij</li>
                <li>Art.4 Kaderspel7</li>
                <li>Art.5 Doelen</li>
                <li>Art.6 Vliegeren </li>
                <li>Art.7 Bal(len) in de grote verdedigingsdriehoek  </li>
                <li>Art.8 Fouten</li>
                <li>Art.9 Ballen uit het biljart</li>
                <li>Art.10 Mikken en/of spelen</li>
                <li>Art.11 Toucher </li>
                <li>Art.12 Doorstoot </li>
                <li>Art.13 Onbehoorlijk gedrag</li>
                <li>Art.14 Beïnvloeding</li>
                <li>Art.15 Strafpunten (fig 3)</li>
                <li>Art.16 Duo wedstrijden</li>
                <li>Art.17 Wedstrijdleiding</li>
                <li>Art.18 Standaardnormen biljart</li>
                <li>Art.19 Biljartballen:</li>
                <li>Art.20 Meetinstrumenten</li>
                <li>Art.21 Hulpmiddelen</li>
                <li>Art.22 Krijt</li>
                <li>Art.23 Fig 1: kaders driehoeken en opgangspunten</li>
                <li>Art.24 Fig 2: driehoeken en stootlijnen</li>
                <li>Art.25 Fig 3: strafpunten en aanvangspunten</li>
            </ul>

            <h3 class="mt-4">Art.1 Algemene bepaling van het spel</h3>
            <p>Het spel wordt 1 tegen 1 (enkel) of 2 tegen 2 (duo) gespeeld. Men speelt om beurten, wanneer een speler een geldig doel maakt, blijft deze aan de beurt. Er wordt gespeeld naar 2 winnende manches tenzij de BNV midweek competitie.</p>

            <h3 class="mt-4">Art.2 Aanvang van een partij</h3>
            <p><strong>Reguliere competitie:</strong></p>
            <p>Bij aanvang van de manche poetst de wedstrijdleider de ballen. De spelers leggen zelf de ballen op de aanvangspunten, rode ballen aan de kant van het witte doel en de witte ballen aan de kant van het rode doel. De wedstrijdleider doet controle op de juiste plaatsing van de ballen, en verbetert indien nodig. Het is de spelers vanaf dat moment niet meer toegestaan om de ballen met de hand te verplaatsen (onbehoorlijk gedrag - mancheverlies). Indien de speler niet akkoord gaat met de correctie van de scheidsrechter kan hij de scheidsrechter vragen de bal(len) te herpositioneren. Deze regel geldt ook tijdens tornooien.</p>
            <p>Wanneer na aanvang van het spel vastgesteld wordt dat de ballen langs de verkeerde kant liggen (wit bij wit doel, rood bij rood doel), worden de spelers er attent op gemaakt en gaat het spel gewoon verder.</p>
            <p>Bij wedstrijden van club tegen club, speelt de thuisspelende ploeg de 1ste manche met de witte ballen. Bij een eventuele beslissende manche heeft de bezoeker de kleurkeuze.</p>
            <p>Bij kampioenschappen speelt de eerst afgeroepen met de witte ballen en bij een beslissende manche spelen beiden een bal recht voor zich uit van korte band naar korte band van het biljart. De speler wiens bal het dichtst bij de band (men mag de band raken) vanwaar deze vertrokken is, stil komt te liggen, mag de kleurkeuze bepalen.</p>
            <p>De wedstrijdleider telt langzaam tot 3. Bij "3" wordt de middelste bal zacht via de linkse band gespeeld. De bal moet de breedte-as van het biljart overschrijden.</p>
            <p>De bal mag niet vertrekken vooraleer de wedstrijdleider "3" heeft gezegd. De bal van de ene speler moet vertrokken zijn vooraleer die van de andere de lange band raakt.</p>
            <p>Bij overtreding van wordt herbegonnen. Bij een tweede overtreding van dezelfde speler lijdt deze beurtverlies en wordt de speelbal op het strafpunt geplaatst.</p>
            <p>Bij betwisting over de afstand van de ballen tot elk der doelen moet gemeten worden met een doelmeter.</p>
            <p>De speler wiens bal het dichtst bij het doel terechtkomt, blijft aan de beurt.</p>
            <p>Bij toucher op eigen bal(len) tijdens de aanvangsstoot wordt de speelbal van de overtreder op het strafpunt geplaatst. Alle ballen, met uitzondering van de bal van de tegenstander, worden teruggeplaatst en men heeft beurtverlies.</p>
            <p>Bij toucher op de bal van de tegenstander tijdens de aanvangsstoot wordt deze in doel gestoken. De speelbal van de overtreder wordt op het strafpunt geplaatst.</p>
            <p>Wanneer beide spelers bij de aanvangsstoot doelen, wordt de aanvangsstoot herhaald met een andere bal naar keuze, doch steeds zacht via de linkse band.</p>
            <p>Een bal op de lijn van de kleine driehoek wordt beschouwd als in de kleine driehoek.</p>
            <p>Een bal tegen de dop van het doel in de kleine aanvalsdriehoek mag hard gedoeld worden.</p>
            <p>Een beurt duurt 40 seconden, vanaf het stilvallen der ballen van de voorafgaande stoot. De wedstrijdleider kondigt "TIJD" aan na 30 seconden, waarna moet gespeeld worden binnen 10 seconden. Bij overtreding geldt beurtverlies. Tijdens het kaderspel duidt de tegenstander een bal aan om op het strafpunt te plaatsen. Bij duo wedstrijden krijgt men 10 seconden extra om te spelen.</p>

            <p><strong>Midweek competitie:</strong></p>
            <p>Bij aanvang van de manche leggen de spelers zelf de ballen op de aanvangspunten, rode ballen aan de kant van het witte doel en de witte ballen aan de kant van het rode doel. De ballen worden op de buitenste punten en het middelpunt aan de grote driehoek geplaatst.</p>
            <p>Wanneer na aanvang van het spel vastgesteld wordt dat de ballen langs de verkeerde kant liggen (wit bij wit doel, rood bij rood doel), gaat het spel gewoon verder.</p>
            <p>Bij wedstrijden van club tegen club, speelt de thuisspelende ploeg de 1ste manche met de witte ballen.</p>
            <p>De speler gaat op en speelt met de witte bal. De bal hoeft de breedte-as niet te overschrijden. Elke geldige stoot is toegestaan. Na de opgangsstoot moet de middelste bal van de tegenstrever de grote driehoek verlaten.</p>
            <p>Bij toucher op eigen bal(len) tijdens de aanvangsstoot wordt de speelbal van de overtreder op het strafpunt geplaatst. Alle ballen, worden teruggeplaatst en men heeft beurtverlies.</p>
            <p>Bij toucher op de bal van de tegenstander tijdens de aanvangsstoot wordt deze in doel gestoken. De speelbal op het middelpunt van de grote driehoek wordt op het strafpunt geplaatst.</p>
            <p>Een beurt duurt 40 seconden, vanaf het stilvallen der ballen van de voorafgaande stoot. De wedstrijdleider kondigt "TIJD" aan na 30 seconden, waarna moet gespeeld worden binnen 10 seconden. Bij overtreding geldt beurtverlies. Tijdens het kaderspel duidt de tegenstander een bal aan om op het strafpunt te plaatsen. Bij duo wedstrijden krijgt men 10 seconden extra om te spelen.</p>

            <h3 class="mt-4">Art.3 Einde van een partij</h3>
            <p>Een manche is beëindigd als één van beide spelers zijn laatste bal scoort en alle ballen van de tegenstander stil liggen.</p>
            <p>Wanneer een manche door de speelwijze geen vooruitgang meer maakt, kondigt de wedstrijdleider "ELK NOG 3 BEURTEN" aan. Blijft dit na deze 6 beurten onveranderd, wordt de manche opnieuw gespeeld. Deze beslissing mag niet worden genomen tijdens het kaderspel.</p>

            <h3 class="mt-4">Art.4 Kaderspel</h3>
            <p>Voor het kaderspel is het biljart verdeeld in vijftien kaders. (zie figuur 1.) Het kaderspel vangt aan van zodra één der spelers nog over 1 bal beschikt. Wanneer een bal op een kaderlijn ligt, volstaat het dat deze de lijn verlaat. Een bal die na de stoot op de kaderlijn ligt is gekaderd. Een bal die in het doel verdwijnt, is van kader veranderd. Bij elke stoot moet minstens 1 bal zijn kader verlaten, maar mag terug in dezelfde kader komen. Bij overtreding wordt de speelbal op het strafpunt geplaatst. Alle andere ballen blijven liggen. Een speelbal in de grote aanvalsdriehoek moet niet van kader veranderen. De grote driehoek is voor de verdedigende ballen geen kader, voor de aanvallende ballen wel.</p>

            <h3 class="mt-4">Art.5 Doelen</h3>
            <p>Wanneer een bal valt alvorens hij stilligt, heeft men geldig gedoeld, ook als hij wordt aangespeeld met een andere bal of via de rubber. In andere gevallen werd hij niet geldig gedoeld. De bal wordt uit het biljart gehaald en teruggeplaatst.</p>
            <p>Een eigen bal in het doel van de tegenspeler spelen, is geldig. Wanneer men een bal van de tegenstander in één van de 2 doelen speelt, is deze geldig gedoeld.</p>
            <p>Een eigen bal in het verdedigingsdoel spelen, is niet geldig. De bal wordt uit het biljart gehaald, op het strafpunt geplaatst en de tegenstander is aan de beurt.</p>
            <p>Wanneer men een eigen bal geldig doelt, blijft men aan de beurt, als je enkel een bal van de tegenstander doelt, gaat de beurt naar de tegenstander.</p>
            <p>Wanneer één of beide spelers geen speelballen meer heeft, wordt de manche gewonnen door diegene wiens laatste bal eerst in doel verdwijnt.</p>
            <p>Doelen met een harde, snelle stoot is toegelaten als de bal in de kleine driehoek, of op de lijn van de kleine aanvalsdriehoek ligt of boven of op de stootlijn.</p>
            <p>Doelen met een harde, snelle stoot is niet toegelaten als de bal onder de stootlijn ligt.</p>
            <p>Een bal geklemd tussen de doppen van het aanvalsdoel en die het speelveld niet raakt, is geldig gedoeld.</p>
            <p>Wanneer de speelbal geen bocht beschrijft (massé) of niet eerst voorwaarts en daarna teruggehaald wordt (piqué), is een harde, snelle stoot niet toegelaten. Bij overtreding heeft men beurtverlies, worden de verplaatste ballen teruggeplaatst en de speelbal op het strafpunt geplaatst.</p>
            <p>Een bal naar doel rollen is regelmatig gespeeld.</p>

            <h3 class="mt-4">Art.6 Vliegeren</h3>
            <p>Hiervoor dient de kaderlijn langs de lange band. Wordt de bal "IN DE KADER LANGS DE LANGE BAND" aangekondigd, mag men niet over de dichtstbijzijnde band vliegeren. Bij overtreding heeft men beurtverlies, worden alle verplaatste ballen teruggeplaatst en de speelbal op het strafpunt geplaatst.</p>
            <p>Vliegeren is zowel als doelpoging of als verdedigingsstoot verboden.</p>

            <h3 class="mt-4">Art.7 Bal(len) in de grote verdedigingsdriehoek</h3>
            <p>Een bal op de lijn van de grote verdedigingsdriehoek wordt beschouwd als in de driehoek.</p>
            <p>Een eigen bal in de grote of kleine verdedigingsdriehoek moet bij het einde van de beurt de grote verdedigingsdriehoek rechtstreeks of onrechtstreeks verlaten hebben. Indien dit niet het geval is wordt de bal op het strafpunt geplaatst, alle verplaatste ballen worden teruggeplaatst en er geldt beurtverlies.</p>
            <p>Indien meerdere eigen ballen in de grote verdedigingsdriehoek liggen, moet bij het einde van de beurt minstens één bal de driehoek rechtstreeks of onrechtstreeks verlaten hebben. Als geen van deze ballen de driehoek verlaten heeft, worden ze allen op de strafpunten geplaatst, alle verplaatste ballen worden teruggeplaatst en geldt beurtverlies.</p>
            <p>Zo een bal van de verdediger in de grote of kleine verdedigingsdriehoek ligt, mag de bal van de aanvaller zich, na diens stoot, enkel in de grote aanvalsdriehoek bevinden wanneer de bal van de verdediger, na de stoot, niet in de kleine driehoek ligt. Bij overtreding wordt de overtredende bal op het strafpunt geplaatst.</p>

            <h3 class="mt-4">Art.8 Fouten</h3>
            <p>Men speelt met de keuspits. Bij elke andere speelwijze heeft men beurtverlies, worden alle verplaatste ballen teruggeplaatst en wordt de speelbal op het strafpunt geplaatst.</p>
            <p>Een fout die door derden veroorzaakt wordt, mag niet aan de speler aangerekend worden. Door zulk een fout verplaatste ballen worden door de wedstrijdleider teruggeplaatst.</p>
            <p>Men moet met minstens 1 voet de grond raken. Indien dit niet het geval is heeft men beurtverlies en worden alle verplaatste ballen teruggeplaatst. Bij kaderspel wordt de speelbal op het strafpunt geplaatst.</p>
            <p>Wanneer een speler een eigen bal klemt tussen de doppen van het verdedigingsdoel en het speelveld niet raakt wordt deze op het strafpunt geplaatst.</p>
            <p>Wanneer een speler een bal van de tegenstander klemt tussen de doppen van een doel wordt deze in het doel gestoken.</p>
            <p>Een bal geklemd tussen dop en band wordt los gelegd rakend aan band en dop.</p>
            <p>Bij rechtstreeks over doppen of ballen spelen, heeft men beurtverlies, worden alle verplaatste ballen teruggeplaatst en de speelbal op het strafpunt geplaatst.</p>
            <p>Onrechtstreeks over doppen of ballen spelen is toegelaten.</p>
            <p>Bij meerdere fouten in één stoot wordt alleen de eerste fout bestraft.</p>
            <p>Een eigen bal rechtstreeks op een andere eigen bal spelen, wordt bestraft met beurtverlies. De verplaatste ballen worden teruggeplaatst en de speelbal wordt op het strafpunt geplaatst.</p>

            <h3 class="mt-4">Art.9 Ballen uit het biljart</h3>
            <p>Wanneer men ballen uit het biljart speelt:</p>
            <p>Ballen van de tegenstander die uit het biljart gespeeld werden, worden in het doel gestoken. Eigen ballen die uit het biljart gespeeld werden, worden op het strafpunt geplaatst. De speelbal wordt op het strafpunt geplaatst.</p>
            <p>Alle verplaatste ballen op de biljart worden terug op hun plaats gelegd.</p>
            <p>Wanneer de tegenstander hierdoor geen ballen meer heeft, wint hij de manche.</p>
            <p>Een bal die via de rand terug op het speelveld terechtkomt, wordt niet bestraft.</p>
            <p>Een bal die op de band, dop en kap van het doel blijft liggen, wordt beschouwd als uit het biljart gespeeld.</p>

            <h3 class="mt-4">Art.10 Mikken en/of spelen</h3>
            <p>Zich achter een bal van de tegenstander opstellen met de keu in een stand om te spelen, wordt bestraft met beurtverlies. Bij kaderspel duidt de tegenstander een bal aan om op het strafpunt te plaatsen. Dit is niet van toepassing wanneer de te bespelen bal vlakbij of in dezelfde speelrichting als de bal van de tegenstander ligt.</p>
            <p>Met een bal van de tegenstander spelen wordt bestraft met beurtverlies. De verplaatste ballen worden teruggeplaatst, bij kaderspel duidt de tegenstander een bal aan om op het strafpunt te plaatsen.</p>
            <p>Mikken of spelen vóór de ballen van de voorgaande stoot stilliggen, wordt bestraft met beurtverlies. De verplaatste ballen worden teruggeplaatst. Tijdens het kaderspel wordt de speelbal op het strafpunt geplaatst. Indien er niet gespeeld werd mag de tegenstander een bal aanduiden die op het strafpunt wordt geplaatst.</p>
            <p>Wanneer men tweemaal na elkaar speelt, wordt de speelbal op het strafpunt geplaatst, en alle verplaatste ballen terug op hun plaats.</p>

            <h3 class="mt-4">Art.11 Toucher</h3>
            <p>Bij toucher heeft men beurtverlies, wordt de speelbal op het strafpunt geplaatst, ook wanneer men gedoeld heeft, en worden alle andere verplaatste ballen teruggeplaatst.</p>
            <p>Wanneer men toucher doet zonder dat men stoot, heeft men beurtverlies en worden alle verplaatste ballen teruggeplaatst.</p>
            <p>Toucher op eigen bal tijdens het kaderspel, wordt bestraft met beurtverlies. De verplaatste ballen worden teruggeplaatst, zelfs zo werd gedoeld. De speelbal wordt op het strafpunt geplaatst.</p>
            <p>Bij toucher op een bal van de tegenstander tijdens het kaderspel, heeft men beurtverlies, alle ballen worden teruggeplaatst, zelfs als men geldig heeft gedoeld en de tegenstander duidt een bal aan die op het strafpunt moet.</p>
            <p>Toucher door kledij of een voorwerp voor de stoot op eigen of bal van de tegenstander: alles terug op zijn plaats en beurtverlies. Tijdens kaderspel komt de bal waar men aanstalten maakte om met te spelen op het strafpunt.</p>
            <p>Toucher door kledij of een voorwerp na de stoot op eigen of bal van de tegenstander: speelbal op strafpunt, beurtverlies en alle verplaatste ballen op hun oorspronkelijke plaats.</p>
            <p>Een eigen bal tegenhouden, bijstoten of van richting doen veranderen, wordt bestraft met beurtverlies. De door de fout verplaatste ballen worden teruggeplaatst en de geraakte bal wordt op het strafpunt geplaatst.</p>
            <p>Een bal van de tegenstander tegenhouden, bijstoten of van richting doen veranderen, wordt bestraft met beurtverlies. Alle verplaatste ballen worden teruggeplaatst, de speelbal op het strafpunt geplaatst en de geraakte bal wordt in doel gestoken.</p>

            <h3 class="mt-4">Art.12 Doorstoot</h3>
            <p>De keuspits in aanraking laten met de speelbal tot deze een andere bal, band of dop raakt, wordt bestraft met beurtverlies. De verplaatste ballen worden teruggeplaatst en de speelbal wordt op het strafpunt geplaatst.</p>
            <p>De wedstrijdleider beslist zo twee ballen, bal - band of bal - dop elkaar raken of niet; de ene bal mag niet bewegen zo men de rakende bal speelt, tenzij men ervan wegspeelt.</p>
            <p>Men stoot niet door zo men een doelpoging onderneemt met een bal die raakt aan de voorste dop of de uiterste zijdop van het kruis en die over de aslijn van deze dop ligt langs de aanvalszijde.</p>

            <h3 class="mt-4">Art.13 Onbehoorlijk gedrag</h3>
            <p>Een opzettelijke fout begaan (een opzettelijke fout is een fout die men begaat, met het doel het resultaat van een stoot te veranderen).</p>
            <p>Roken (ook elektrisch) tussen en tijdens 2 manches, als beide spelers gaan roken tussen 2 manches geldt wedstrijdverlies voor beide spelers.</p>
            <p>Biljartkeu tijdens het spel op het biljart leggen.</p>
            <p>Biljartkeu tijdens het spel volledig uit elkaar schroeven, zonder verwittiging aan de wedstrijdleider.</p>
            <p>Tijdens het spel een bal of ballen met de hand verplaatsen. De rand van het biljart met krijt of enig ander voorwerp aftekenen.</p>
            <p>Op het biljart slaan.</p>
            <p>Meten met duim of vinger aan de doppen van het doel.</p>
            <p>Niet laten bestraffen van een fout.</p>
            <p>Gebruik van talkpoeder.</p>
            <p>Gebruik van oortjes.</p>
            <p>Meer oefenen dan voorzien tijdens kampioenschappen en officiële tornooien.</p>
            <p>Blazen naar ballen.</p>
            <p>Tijdens een wedstrijd op een ander biljart stoten.</p>
            <p>Bij overtreding geldt mancheverlies.</p>

            <h3 class="mt-4">Art.14 Beïnvloeding</h3>
            <p>Meten van de doelopening met een bal.</p>
            <p>Gebaren maken en roepen.</p>
            <p>Minder dan één meter van de tegenstander of het biljart staan zo deze aan de beurt is.</p>
            <p>De biljartkeu niet aan de grond houden zo men niet aan beurt is.</p>
            <p>Biljart aanraken of in het gezichtsveld van de tegenstander staan zo men niet aan beurt is.</p>
            <p>Krijt van het biljart nemen of terugplaatsen als de tegenstander aanstalten maakt om te stoten. Na de beurt zich niet dadelijk verwijderen van het biljart (controle van het al dan niet kunnen passeren van een bal is verboden).</p>
            <p>Bij overtreding geldt de 1e maal verwittiging, bij een tweede overtreding in dezelfde partij, wordt de manche verloren verklaard.</p>

            <h3 class="mt-4">Art.15 Strafpunten (fig 3)</h3>
            <p>Een strafbal legt men op strafpunt 1 of 2 naar keuze van de tegenstander.</p>
            <p>Met strafpunt 1 wordt bedoeld: de hoek, gevormd door de dop van het doel en de korte band, langs de zijde van het aanvalsdoel van de overtreder, aan de linkerkant. De bal moet de korte band raken en 3 millimeter los van de dop gelegd worden.</p>
            <p>Met strafpunt 2 wordt bedoeld: de hoek, gevormd door de dop van het doel en de korte band, langs de zijde van het aanvalsdoel van de overtreder, aan de rechterkant. De bal moet de korte band raken en 3 millimeter los van de dop gelegd worden.</p>
            <p>Strafpunt 3 wordt gebruikt als strafpunt 1 en 2 bezet zijn.</p>
            <p>Met strafpunt 3 wordt bedoeld: In de boskader op de lengteaslijn, rakend aan de 2de dop langs het aanvalsdoel van de overtreder. Strafpunt 3 mag langs alle vier de zijden van het bos verlaten worden.</p>
            <p>Indien door ligging van andere ballen de strafbal niet op deze strafpunten kan gelegd worden, legt men de bal op het volgende in aanmerking komende strafpunt namelijk strafpunt 4 of strafpunt 5.</p>
            <p>Met strafpunt 4 wordt bedoeld: achter en rakend aan de twee doppen van de breedteaslijn langs het verdedigingsdoel van de overtreder, aan de linkerkant.</p>
            <p>Met strafpunt 5 wordt bedoeld: achter en rakend aan de twee doppen van de breedteaslijn langs het verdedigingsdoel van de overtreder, aan de rechterkant.</p>
            <p>Indien tijdens het kaderspel een speler een foutieve stoot doet met een bal die op het strafpunt ligt of gelegd werd, wordt de speelbal verplaatst naar het eerst beschikbare strafpunt van waarop nog geen foutieve stoot gebeurde.</p>

            <h3 class="mt-4">Art.16 Duo wedstrijden</h3>
            <p>Vóór de 1ste manche bepaalt elk duo wie de aanvangsstoot zal uitvoeren. Bij de tweede manche is het de andere speler die zal aanvangen, bij niet navolging is er beurtverlies en de speelbal op het strafpunt. Bij een beslissende manche bepaalt elk duo wie de aanvangsstoot zal uitvoeren. Het duo wiens bal het dichtst bij het doel terechtkomt, blijft aan de beurt en de tweede speler gaat verder, tenzij met de aanvangsstoot geldig werd gedoeld.</p>
            <p>Men speelt om beurten. Zo men de volgorde niet naleeft, heeft men beurtverlies en worden de verplaatste ballen teruggeplaatst, zelfs zo werd gedoeld. Tijdens het kaderspel wordt de speelbal op het strafpunt geplaatst.</p>
            <p>Er mag onderling overleg gepleegd worden over de speelwijze maar er mag niet gewezen of aangeduid worden. De speelwijze van de ene speler mag niet beïnvloed worden door de andere, mondeling, noch door gebaren. Dit houdt in dat de medespeler op minstens 1 meter van het biljart en van zijn medespeler verwijderd blijft gedurende de beurt en geen overleg meer pleegt tijdens de stoot. Bij overtreding wordt dit beschouwd als beïnvloeding en alsdusdanig bestraft worden. (eerste keer verwittiging 2e inbreuk mancheverlies).</p>
            <p>Een beurt duurt 50 seconden, vanaf het stilvallen der ballen van de voorafgaande stoot. De wedstrijdleider kondigt "TIJD" aan na 40 seconden, waarna moet gespeeld worden binnen 10 seconden. Bij overtreding geldt beurtverlies. Tijdens het kaderspel duidt de tegenstander een bal aan om op het strafpunt te plaatsen.</p>

            <h3 class="mt-4">Art.17 Wedstrijdleiding</h3>
            <p><strong>Plichten</strong></p>
            <p>Deze is lid van de KBGB en wordt geacht het speelreglement te kennen en steeds correct toe te passen.</p>
            <p>Als scheidsrechter plaatst men zich rechtstaande aan het biljart en uit het gezichtsveld van de speler aan beurt. Als deze zich klaarmaakt om te stoten, houdt men zich stil en verbiedt doorgang aan derden.</p>
            <p>Wanneer een bal in de nabijheid van een andere bal, een band, een dop of een lijn (kleine of grote driehoek schietlijn kaderlijn) ligt, is het de taak van de scheidsrechter om, ongevraagd, aan te kondigen waar de bal zich bevindt. Een bal bevindt zich in de nabijheid van een andere bal, band of dop, wanneer de zijkant van de bal zich op minder dan 0,5 cm ervan bevindt. Een bal bevindt zich in de nabijheid van een lijn wanneer het middelpunt van de bal zich op minder dan 0,5 cm ervan bevindt.</p>
            <p>Het gebruik van multimedia is verboden tijdens de wedstrijd. Er mag een opmerking gemaakt worden door de ploegverantwoordelijke of de speler van de tegenpartij hieromtrent.</p>
            <p>Men zal zich noch tot de spelers, noch tot de omstanders richten tenzij in de gevallen, voorzien door het reglement.</p>
            <p>Bij vaststelling van een fout legt de wedstrijdleider het spel stil, kondigt duidelijk en luidop aan welke beslissing hij gaat nemen, bestraft de fout en laat daarna verder spelen.</p>
            <p>Als er verder gespeeld werd voordat de wedstrijdleider daartoe de toestemming gaf, heeft de overtreder beurtverlies en worden alle verplaatste ballen teruggeplaatst, zelfs bij een geldig doel.</p>
            <p>Een fout mag worden aangeduid door de wedstrijdleider, de 2 spelers aan de biljart en de kapiteins van beide ploegen.</p>
            <p>Als er verder gespeeld werd zonder dat de fout in dezelfde beurt werd aangeduid, gaat het spel gewoon verder.</p>
            <p>Supporteren vóór de stoot is verboden. Bij overtreding krijgt men een 1ste maal een verwittiging, een 2de maal beurtverlies en bij een derde maal wordt de manche verloren verklaard.</p>
            <p>Supporteren na de stoot is toegelaten op een kalme en serene wijze. Bij overtreding wordt men een 1ste maal verwittigd, de tweede maal worden alle verplaatste ballen teruggeplaatst. Bij een derde overtreding wordt de manche verloren verklaard.</p>

            <h3 class="mt-4">Art.18 Standaardnormen biljart</h3>
            <p>Lengte speelveld: 1800 mm</p>
            <p>Breedte speelveld: 900 mm</p>
            <p>Hoogte biljart: 720/800 mm</p>
            <p>Breedte band (rubber + hout): 120/130 mm</p>
            <p>Dikte Leisteen: Min. 35 mm</p>
            <p>Hoogte doppen: 45 mm</p>
            <p>Hoogte doppen onder de ring: 35 mm</p>
            <p>Doormeter doppen: 35 mm</p>
            <p>Afstand v/h middelpunt v/h biljart tot de binnenste dop in de breedte as: 90 mm</p>
            <p>Afstand tussen de binnenste en de buitenste dop in de breedte as: 90 mm</p>
            <p>Afstand v/h middelpunt v/h biljart tot de binnenste dop in de lengte as: 90 mm</p>
            <p>Afstand tussen de binnenste en de buitenste dop in de lengte as: 90 mm</p>
            <p>Afstand tussen de doeldoppen: 76,5 mm</p>
            <p>Afstand tussen middelpunt dop van doel en korte band: 65 mm</p>
            <p>Doormeter doelopening: 65 mm</p>
            <p>Afstand vlieglijn vanaf lange band: 92,3 mm</p>
            <p>Afstand aanvangspunten t.o.v. korte band: 75 mm</p>
            <p>Dikte van de lijnen: 0,7 mm</p>
            <p>Afstand tussen middelpunt biljart en middelpunt doelopening: 897 mm</p>
            <p>Rubberringen met het merkteken BGB.</p>
            <p>Hoogte verlichting boven biljart: minimum 80 cm boven biljart.</p>
            <p>Het biljartlaken bestaat uit 90% wol en 10% nylon. De kleur mag niet wit of rood zijn.</p>

            <h3 class="mt-4">Art.19 Biljartballen</h3>
            <p>De basisstof voor het vervaardigen van golfbiljartballen is fenolhars.</p>
            <p>De hardheid van golfbiljartballen wordt bepaald op minimum 72 HRH en maximum 88 HRH.</p>
            <p>De diameter van de golfbiljartballen bedraagt 61,5 mm., een afwijking van 0.25 mm op de standaard diameter wordt toegestaan.</p>
            <p>Het standaardgewicht van golfbiljartballen wordt bepaald tussen 207 en 213 gram, binnen 1 set golfbiljartballen mag het onderling verschil tussen de golfbiljartballen maximum 3 gram bedragen.</p>
            <p>De kleur van de golfbiljartballen is wit en rood.</p>
            <p>Ronde stippen en rechthoeken worden toegestaan, het aantal wordt bepaald op maximum 6 stuks, de diameter bedraagt maximum 8 mm. Andere symbolen worden niet toegestaan.</p>
            <p>Toegelaten ballen: Saluc Super Aramith, Saluc Super Aramith pro cup, Diamond, Dynaspheres bumper pool silver 615 en Dynaspheres bumper pool gold 615.</p>

            <p><strong>Procedure toelaten biljartballen:</strong></p>
            <p>Ieder op de markt actieve producent of invoerder van golfbiljartballen kan een schrijven richten aan het secretariaat van de KBGB vzw met de vraag om opgenomen te worden in de lijst van toegelaten golfbiljartballen voor de wedstrijden in de KBGB vzw.</p>
            <p>Samen met het schrijven bezorgt de producent of invoerder van golfbiljartballen een set ballen aan de KBGB vzw, samen met een testverslag of een attest door een onafhankelijk labo of erkenning in een buitenlandse golfbiljartcompetitie waaruit blijkt dat de golfbiljartballen voldoen aan de niet vaststelbare kenmerken of vereisten.</p>
            <p>De controle van de aanvraag, verslagen en erkenningen en de golfbiljartballen zal zo spoedig mogelijk en uiterlijk binnen de 5 dagen gebeuren.</p>
            <p>Als het product voldoet wordt het merk en type golfbiljartbal als goedgekeurd opgenomen in de reglementen en gepubliceerd op de website zodat alle clubs en spelers op de hoogte zijn van de toelating tot gebruik van deze golfbiljartballen binnen de week na aanvraag.</p>
            <p>Als het product niet voldoet aan de in reglement omschreven vaststelbare en niet vaststelbare criteria wordt de producent/invoerder binnen de week in kennis gesteld van de gemotiveerde weigering tot opname in de reglementen.</p>

            <h3 class="mt-4">Art.20 Meetinstrumenten</h3>
            <p>Zijn toegelaten: het kaliber, de doelmeter, de lijnmeter (lijnmeter met vijs), spiegel, lasermeter, plastieken brug.</p>
            <p><strong>Lasermeter:</strong> als beide lasers op de bal schijnen, ligt de bal op de lijn. Zelfs als de ene laser hoger op de bal schijnt dan de andere en zelfs als een deel van een van de lasers op het laken schijnt.</p>
            <p><strong>Multifunctioneel meettoestel (plastiek blokje):</strong> als de lijn zichtbaar is in het gaatje, zelfs gedeeltelijk, dan ligt de bal op de lijn.</p>
            <p>Kaliber opgangspunten</p>
            <p>Elk meetinstrument moet de goedkeuring van de KBGB vzw hebben.</p>

            <p><strong>Procedure toelating meetinstrumenten:</strong></p>
            <p>Ieder op de markt actieve producent of invoerder van meetinstrumenten kan een schrijven richten aan het secretariaat van de KBGB vzw met de vraag om opgenomen te worden in de lijst van toegelaten meetinstrumenten voor de wedstrijden in de KBGB vzw.</p>
            <p>De producent of invoerder van meetinstrumenten wordt uitgenodigd op de eerstvolgende vergadering van het bestuursorgaan van de KBGB vzw en stelt het meetinstrument en zijn werking voor.</p>
            <p>De juistheid wordt door het bestuursorgaan van de KBGB vzw nagegaan. De KBGB vzw kan hiervoor eventueel beroep doen op externe experts.</p>
            <p>De producent of invoerder van meetinstrumenten wordt zo spoedig mogelijk (na eventuele testperiode) in kennis gesteld of het meetinstrument wordt toegelaten. Bij niet toelating zal de KBGB vzw zijn beslissing motiveren.</p>

            <h3 class="mt-4">Art.21 Hulpmiddelen</h3>
            <p>Hulpmiddelen voor de keu, zoals steuntjes enz., zijn verboden. Een extension is toegelaten op voorwaarde dat hij geplaatst is voor aanvang van de wedstrijd of binnen de reguliere speeltijd. Personen met een fysieke beperking mogen attributen gebruiken mits voorlegging van een doktersattest, die zij volgens hun handicap nodig hebben, om hun wedstrijd te kunnen betwisten.</p>

            <h3 class="mt-4">Art.22 Krijt</h3>
            <p>Rood en wit krijt is verboden.</p>

            <h3 class="mt-4">Figuren</h3>

<p><strong>Fig 1:</strong> kaders driehoeken en opgangspunten</p>
<img src="{{ asset('images/kader_driehoeken_opgangspunten.png') }}" alt="Kaders driehoeken en opgangspunten" class="img-fluid">

<p><strong>Fig 2:</strong> driehoeken en stootlijnen</p>
<img src="{{ asset('images/driehoeken_stootlijnen.png') }}" alt="Driehoeken en stootlijnen" class="img-fluid">

<p><strong>Fig 3:</strong> strafpunten en aanvangspunten</p>
<img src="{{ asset('images/strafpunten_aanvangspunten.png') }}" alt="Strafpunten en aanvangspunten" class="img-fluid">

        </div>
    </div>
</div>
@endsection
