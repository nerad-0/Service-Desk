# Design brief rozhrani ServisDesk

## Cilovy uzivatel

ServisDesk pouzivaji tri skupiny uzivatelu:

- bezny uzivatel, ktery obcas nahlasi IT problem a chce rychle zjistit stav,
- technik, ktery s aplikaci pracuje opakovane a potrebuje prehled fronty pozadavku,
- administrator, ktery spravuje uzivatele, kategorie a kontroluje auditni zaznamy.

Nejcastejsi prace probiha na desktopu ve skolnim nebo kancelarskem prostredi. Mobilni verze musi umoznit zalozeni pozadavku a kontrolu stavu, ale hlavni pracovni rezim je desktop.

## Nejdulezitejsi ukoly

- rychle najit pozadavek podle nazvu, stavu, priority nebo kategorie,
- zalozit novy servisni pozadavek,
- otevrit detail a precist komentare,
- zmenit stav pozadavku,
- sledovat neuzavrene a urgentni pozadavky,
- spravovat uzivatele a kategorie.

## Informace viditelne okamzite

- stav a priorita pozadavku,
- kategorie,
- autor,
- datum posledni zmeny,
- pocet aktivnich a vyresenych pozadavku v administraci,
- posledni pozadavky ve fronte.

## Vizualni charakter

Rozhrani ma pusobit jako skolni servisni evidence: klidne, kompaktni, pracovni a bez marketingoveho dojmu. Prioritou jsou tabulky, filtry, stavove stitky a prehledne formulare. Dekorativni prvky nejsou potreba.

## Barevna paleta

- pozadi aplikace: `#f3f5f6`
- hlavni text: `#1f2933`
- pomocny text: `#5f6b76`
- ohraniceni: `#ccd5dd`
- hlavni akcent: `#0f5f57`
- svetly akcent: `#e5f0ee`
- chybovy stav: `#b42318`
- varovani: `#9a5b13`
- uspech: `#1b7f4a`
- informacni stav: `#2f5f8f`

Paleta je zamerne neutralni. Akcent je tlumeny zeleny odstin vhodny pro servisni a provozni system.

## Typografie

- hlavni nadpis stranky: 24 px
- nadpis sekce: 17 az 18 px
- bezny text: 15 px
- tabulky a pomocny text: 13 az 14 px
- popisky formularu: 13 px

Nadpisy nejsou hero prvky. Slouzi k orientaci v pracovni aplikaci.

## System mezer

Rozhrani pouziva kompaktni mezery:

- 6 px pro drobne vazby,
- 10 az 12 px mezi poli,
- 16 px mezi sekcemi,
- 20 az 24 px pro hlavni okraje stranky.

Cilem je dobra citelnost bez zbytecne prazdne plochy.

## Formulare, tabulky a tlacitka

- formulare jsou jednoduche, hranicene linkou a bez silnych stinu,
- tabulky jsou hlavni pracovni plocha,
- stavove stitky jsou male, hranatejsi a citelne,
- tlacitka maji jasnou hierarchii: hlavni akce plna, vedlejsi akce obrysova,
- zaobleni je stridme, nejcasteji 4 px.

## Layout

Desktop pouziva horni navigaci a dvousloupcovy pracovni layout:

- vlevo fronta pozadavku s filtry a tabulkou,
- vpravo detail vybraneho pozadavku,
- administrace ma taby a pracovni sekce podle typu dat.

Mobilni verze sklada obsah pod sebe. Tabulky zustavaji horizontalne posuvne, protoze zmenseni vsech sloupcu by zhorsilo citelnost.

## Kontrola proti generickemu SaaS vzhledu

Frontend se vyhyba:

- gradientum,
- glassmorphismu,
- velkym hero nadpisum,
- dekorativnim ilustracim,
- dashboardu slozenemu pouze z barevnych karet,
- nadbytecnym stinum,
- marketingovym textum.

