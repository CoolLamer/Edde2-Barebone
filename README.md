Edde2-Barebone
==============

Edde2 Barebone je knihovna usnadňující práci s Nette Frameworkem a poskytuje některé další vychytávky, které
mohou běžnému smrtelníkovi ulehčit život.

Původní myšlenka spočívala ve vytvoření knihovny, která doplní některé ne úplně pohodlné nebo chybějící prvky
v Nette, které do něj samy o sobě nepatří - nebo dokonce z jistého úhlu pohledu mohou působit jako anti-patterna -
nicméně pro vývoj běžné aplikace zcela nepostradatelné.

Edde se postupně z knihovny začíná vyvíjet v tzv. barebone, blackbox aplikaci, jejímž hlavním smyslem je poskytnout
prostor pro vývoj, deploy a aktualizace hostované aplikace běžící na Edde.

Klasické Nette dává velkou tíhu na stranu programátora "uživatele" a nutí jej se o veškeré vychytávky postarat sám;
Edde tuto tíhu eliminuje a absorbuje ji - ve výsledku se jedná o velmi jednoduchou práci s veškerými službami a případnou
konfigurací aplikace.

Jaké jsou tedy killer-feature? Těžko říci, nicméně něco málo, co knihovna nabízí:
- vlastní robot-loader - lightweight, ultimátní rychlost (vychází z originálního RobotLoadera z Nette, některé "zbytečné" featury obchází, doplňuje podporu phar a poskytuje některé metody reflexe)
- služba ClassLoader pro vytváření instancí tříd - zajistí veškeré závislosti, včetně možnosti volání inject metod
- podpora automatických accessorů (MyService nelze instancovat přímo - pokud existuje MyServiceAccessor, který tuto třídu umí zajistit, automaticky se vytvoří a MyService vrátí - podpora injectu kontextových služeb)
- konfigurace: odpadá nutnost veškeré služby vyvěšovat do configu - mimo přebití výchozích služeb Nette a závislých služeb; závislosti se automaticky vytvoří a založí jako služba do kontejneru (výsledkem je konfigurace aplikace, nikoli její programování configem)
- klasický TemplateLoader s podporou pharů (EddeModul je součástí phar archivu, včetně načtení vnitřního layoutu)
- centralizovaná služba pro vytváření komponent dle názvu třídy (fragmentu, není třeba psát celý namespace)
- odladěný ajax - dostupná lightweight knihovna pro práci s ajaxem - velmi snadné odchytávání handle, včetně formulářových dat (+ podpora file input)
- flash messages - komponenta, kterou stačí pouze vložit do šablony, postará se o centralizované vykreslování zpráviček bez prasení
- automatické načítání konfigurace z libs (přípona .config.neon) - knihovny mohou izolovat svou konfiguraci od uživatele
- primitivní konfigurace modelů + vygenerování databáze, obslužné třídy a zdrojových souborů modelů
- velmi jednoduchý, ale velmi efektivní mechanismus sanitizeru (input/output) a validátorů s přímým "seamlessly" napojením na modely

Hlavní vychytávkou knihovny je jednoduchá modelová vrstva, kterou lze snadno nakonfigurovat a podporuje 1:N vazby, M:N vazby, inteligentní generování
getterů/setterů/isserů - rozlišuje bool, datum a modely; vygeneruje metody pro dostupné vazby 1:N a M:N. Pro naplnění výchozími daty lze použít
libovolný loader, výchozí implementací je CSV loader s podporou vazeb (není nutné zadávat ve vazbách tvrdá ID objektů, lze použít jednoduchý vyhledávací výraz).

Celá knihovna a projekt je koncipován tak, aby byl maximálně jednoduchý nejen na programávání (nic nenutil, ale maximálně pomohl), ale i na
uživatelskou práci (např. nasazení aplikace v podobě nahrání ZIP balíčku na serveru, automatické přegenerování databáze, import výchozích dat
a přegenerování zdrojových souborů modelů).

Filozofií knihovny je KISS, Vždy a všude. A ještě k tomu je to celé krásné, rychlé a voňavé :D.