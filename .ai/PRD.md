Kanon PRD

wtorek, 26 sierpnia 2025
20:52

# Dokument wymagań produktu (PRD) - Katalog Obiektów Krajoznawczych Polski

## 1. Przegląd produktu
Celem projektu jest stworzenie otwartej aplikacji webowej prezentującej katalog obiektów krajoznawczych Polski, takich jak zabytki, parki narodowe i rezerwaty. Użytkownik będzie mógł filtrować obiekty wg województwa, kategorii i statusu UNESCO, wyszukiwać je po nazwie oraz przeglądać na mapie. Strona każdego obiektu zawierać będzie podstawowe informacje (tytuł, opis, zdjęcie) oraz opcjonalne dane praktyczne. Dodatkowo użytkownik zobaczy inne obiekty znajdujące się w pobliżu.

Aplikacja będzie obejmować także:
- publiczną stronę główną przedstawiającą projekt, jego cel i główne sekcje serwisu,
- sekcję blogową z aktualnościami i artykułami,
- moduł prezentujący najnowsze obiekty na stronie głównej i stronie bloga.

System umożliwi zespołowi nietechnicznemu łatwą edycję treści poprzez prosty CMS. CMS będzie obsługiwał zarówno obiekty krajoznawcze, jak i wpisy blogowe. Blog ma pełnić funkcję wspierającą katalog poprzez dostarczanie aktualności, kontekstu i dodatkowych treści redakcyjnych, bez przekształcania produktu w rozbudowaną platformę publikacyjną. Projekt realizowany jest przez zespół w składzie: 1 deweloper + 1 designer, z planowanym czasem dostarczenia wersji beta w ciągu 5 miesięcy.

## 2. Problem użytkownika
W Polsce brak jest centralnego, przyjaznego dla turystów i nauczycieli katalogu obiektów krajoznawczych. Obecnie informacje są rozproszone w wielu źródłach, często niespójne lub trudno dostępne. Aplikacja ma rozwiązać ten problem, oferując:
- uporządkowaną bazę obiektów krajoznawczych w jednym miejscu,
- możliwość łatwego filtrowania i wyszukiwania,
- kontekst geograficzny (mapa, obiekty w pobliżu),
- dostęp do podstawowych informacji praktycznych w przejrzystej formie,
- możliwość wydrukowania strony obiektu jako materiału dydaktycznego lub podróżniczego,
- czytelny punkt wejścia do projektu dzięki stronie głównej,
- aktualności i artykuły wspierające odkrywanie nowych obiektów oraz budujące zaangażowanie użytkowników.

## 3. Wymagania funkcjonalne
1. Katalog obiektów krajoznawczych:
   - Każdy obiekt zawiera tytuł, opis, min. jedno zdjęcie (obowiązkowe pola).
   - Opcjonalne dane: godziny otwarcia, ceny biletów, strona www.
   - Kategorie hierarchiczne do 3 poziomów.
   - Atrybut UNESCO jako dodatkowy filtr.
   - Każdy obiekt posiada datę dodania lub publikacji wykorzystywaną do wyznaczania listy najnowszych obiektów.
2. Filtry i wyszukiwanie:
   - Filtr wg województwa.
   - Filtr wg kategorii.
   - Filtr wg statusu UNESCO.
   - Wyszukiwanie obiektów po nazwie (fuzzy search).
3. Obiekty w pobliżu:
   - Funkcja geolokalizacji z domyślnym promieniem 5 km.
   - Jeśli brak wyników, system szuka w promieniu 20 km.
4. Mapa:
   - Wyświetlanie punktów obiektów.
   - Wyświetlanie uproszczonych poligonów dla obszarów (np. parki).
5. Strona obiektu:
   - Tytuł, opis, zdjęcie (obowiązkowe).
   - Dane opcjonalne (godziny, ceny, www).
   - Lista obiektów w pobliżu.
   - Opcja wydruku strony.
6. Strona główna:
   - Publiczna strona startowa zawierająca opis projektu, jego celu i wartości dla użytkownika.
   - Strona główna zawiera czytelne wejścia do katalogu, mapy i bloga.
   - Strona główna prezentuje sekcję najnowszych obiektów z tytułem, zdjęciem i linkiem do strony szczegółowej.
   - Treści informacyjne strony głównej mogą być w pierwszej wersji zarządzane statycznie, natomiast lista najnowszych obiektów pobierana jest dynamicznie z katalogu.
7. Blog / aktualności:
   - Publiczna lista artykułów i aktualności publikowanych w odwrotnej kolejności chronologicznej.
   - Dedykowana strona szczegółowa artykułu.
   - Wpis blogowy zawiera minimum: tytuł, datę publikacji i treść.
   - Opcjonalnie wpis może zawierać zdjęcie główne / okładkę.
   - Strona bloga zawiera sekcję najnowszych obiektów powiązaną z katalogiem.
   - Wersja beta nie obejmuje tagów, komentarzy, profili autorów, harmonogramowania publikacji ani mechanizmów rekomendacji powiązanych wpisów.
8. CMS:
   - Prostota obsługi dla osób nietechnicznych.
   - Edycja i dodawanie obiektów.
   - Edycja, dodawanie i usuwanie wpisów blogowych.
   - Walidacja obowiązkowych pól.
   - Brak workflow akceptacyjnego.
   - Wspólna obsługa mediów dla obiektów i wpisów blogowych, jeśli zostanie zaimplementowana.
9. Dostęp:
   - Aplikacja otwarta – brak logowania dla użytkowników końcowych.
   - CMS zabezpieczony (dostęp tylko dla zespołu).
10. Komunikacja:
   - Formularz kontaktowy lub adres e-mail do zespołu.
   - Brak systemu opinii/recenzji.
11. Dostępność:
   - Zgodność z WCAG.
   - Strony drukowalne.
   - Strona główna i blog dostępne na urządzeniach mobilnych i desktopowych.

## 4. Granice produktu
- Brak logowania i personalizacji dla użytkowników końcowych.
- Brak możliwości zapisywania ulubionych obiektów czy tras.
- Brak systemu ocen i recenzji.
- Brak wielojęzyczności (tylko język polski).
- Dane i zdjęcia pochodzą wyłącznie z własnej bazy zespołu.
- Aktualizacja danych praktycznych (np. godziny otwarcia) w pełni zależy od zespołu.
- Brak planu wdrożenia wersji produkcyjnej w tej fazie – celem jest wersja beta.
- Blog w wersji beta nie obejmuje zaawansowanych funkcji publikacyjnych, takich jak tagowanie, komentarze, profile autorów, publikacja zaplanowana, newsletter czy personalizacja treści.
- Strona główna ma pełnić rolę informacyjną i nawigacyjną, a nie rozbudowanego serwisu marketingowego.

## 5. Historyjki użytkowników

### Użytkownik końcowy
US-001
Tytuł: Przeglądanie katalogu obiektów
Opis: Jako turysta chcę zobaczyć listę wszystkich obiektów, aby móc przeglądać dostępne atrakcje.
Kryteria akceptacji:
- System wyświetla listę obiektów z tytułem, krótkim opisem i zdjęciem.
- Lista zawiera wszystkie obiekty dostępne w bazie.

US-002
Tytuł: Filtrowanie wg województwa
Opis: Jako turysta chcę filtrować obiekty po województwie, aby zawęzić wyszukiwanie do konkretnego regionu.
Kryteria akceptacji:
- Użytkownik może wybrać jedno województwo z listy.
- System wyświetla tylko obiekty z wybranego województwa.

US-003
Tytuł: Filtrowanie wg kategorii
Opis: Jako turysta chcę filtrować obiekty po kategorii, aby znaleźć interesujące mnie typy obiektów.
Kryteria akceptacji:
- Kategorie są prezentowane w hierarchii do 3 poziomów.
- System filtruje obiekty zgodnie z wybraną kategorią.

US-004
Tytuł: Filtrowanie wg statusu UNESCO
Opis: Jako turysta chcę filtrować obiekty z listy UNESCO, aby znaleźć obiekty o szczególnej wartości.
Kryteria akceptacji:
- Filtr „UNESCO” dostępny jako osobna opcja.
- System wyświetla tylko obiekty oznaczone statusem UNESCO.

US-005
Tytuł: Wyszukiwanie po nazwie
Opis: Jako turysta chcę wyszukać obiekt po nazwie, aby szybko znaleźć interesujące miejsce.
Kryteria akceptacji:
- Wyszukiwarka obsługuje fragmenty nazw (fuzzy search).
- Wyniki zawierają obiekty pasujące do wpisanej frazy.

US-006
Tytuł: Obiekty w pobliżu
Opis: Jako turysta chcę zobaczyć obiekty w pobliżu mojej lokalizacji, aby odkrywać atrakcje w okolicy.
Kryteria akceptacji:
- Domyślny promień wyszukiwania: 5 km.
- Jeśli brak wyników, system automatycznie zwiększa promień do 20 km.
- Lista wyników pokazuje obiekty wraz z odległością.

US-007
Tytuł: Przeglądanie mapy
Opis: Jako turysta chcę zobaczyć obiekty na mapie, aby lepiej zrozumieć ich lokalizację.
Kryteria akceptacji:
- Mapa wyświetla punkty lokalizacji obiektów.
- Obszary (np. parki) prezentowane są jako uproszczone poligony.

US-008
Tytuł: Strona szczegółowa obiektu
Opis: Jako turysta chcę zobaczyć szczegółową stronę obiektu, aby uzyskać więcej informacji.
Kryteria akceptacji:
- Strona zawiera: tytuł, opis, min. 1 zdjęcie.
- Strona może zawierać opcjonalne informacje: godziny otwarcia, ceny, strona www.
- System wyświetla listę obiektów w pobliżu.

US-009
Tytuł: Drukowanie strony obiektu
Opis: Jako nauczyciel chcę wydrukować stronę obiektu, aby użyć jej jako materiału dydaktycznego.
Kryteria akceptacji:
- Strona obiektu posiada przycisk „drukuj”.
- Wydruk zawiera tytuł, opis, zdjęcie i podstawowe informacje.

US-015
Tytuł: Zrozumienie projektu na stronie głównej
Opis: Jako odwiedzający chcę zobaczyć stronę główną wyjaśniającą, czym jest projekt, aby szybko zrozumieć, co oferuje serwis.
Kryteria akceptacji:
- Strona główna zawiera opis projektu i jego celu.
- Strona główna zawiera czytelne linki do katalogu, mapy i bloga.
- Strona działa poprawnie na urządzeniach mobilnych i desktopowych.

US-016
Tytuł: Przeglądanie najnowszych obiektów
Opis: Jako odwiedzający chcę zobaczyć najnowsze obiekty, aby odkrywać ostatnio dodane miejsca.
Kryteria akceptacji:
- Strona główna wyświetla listę najnowszych obiektów.
- Każdy element listy zawiera tytuł, zdjęcie i link do strony obiektu.
- Kolejność obiektów wynika z daty dodania lub publikacji w katalogu.

US-017
Tytuł: Przeglądanie aktualności i artykułów
Opis: Jako odwiedzający chcę przeglądać artykuły i aktualności, aby być na bieżąco z nowościami i odkryciami.
Kryteria akceptacji:
- System wyświetla listę opublikowanych artykułów w odwrotnej kolejności chronologicznej.
- Każdy artykuł na liście zawiera tytuł, datę publikacji i skrót lub zdjęcie główne.
- Użytkownik może przejść do strony szczegółowej artykułu.

US-018
Tytuł: Czytanie artykułu
Opis: Jako odwiedzający chcę otworzyć stronę artykułu, aby przeczytać pełną treść aktualności.
Kryteria akceptacji:
- Strona artykułu zawiera tytuł, datę publikacji i pełną treść.
- Jeśli wpis zawiera zdjęcie główne, jest ono widoczne na stronie artykułu.
- Ze strony artykułu użytkownik może przejść do bloga lub katalogu.

### CMS (zespół edytorski)
US-010
Tytuł: Dodawanie nowego obiektu
Opis: Jako redaktor chcę dodać nowy obiekt do katalogu, aby baza była kompletna.
Kryteria akceptacji:
- Formularz dodawania obiektu zawiera obowiązkowe pola: tytuł, opis, zdjęcie.
- System waliduje wypełnienie obowiązkowych pól.
- Obiekt pojawia się w katalogu po zapisaniu.

US-011
Tytuł: Edycja obiektu
Opis: Jako redaktor chcę edytować istniejący obiekt, aby aktualizować dane praktyczne.
Kryteria akceptacji:
- Formularz edycji dostępny z poziomu CMS.
- Możliwość edycji wszystkich pól obiektu.
- Zmiany są widoczne natychmiast po zapisaniu.

US-012
Tytuł: Usuwanie obiektu
Opis: Jako redaktor chcę usunąć obiekt, aby baza była aktualna i poprawna.
Kryteria akceptacji:
- CMS umożliwia trwałe usunięcie obiektu.
- Po usunięciu obiekt znika z katalogu i mapy.

US-019
Tytuł: Dodawanie wpisu blogowego
Opis: Jako redaktor chcę utworzyć i opublikować wpis blogowy, aby komunikować aktualności i publikować artykuły.
Kryteria akceptacji:
- Formularz dodawania wpisu zawiera obowiązkowe pola: tytuł, data publikacji, treść.
- Zdjęcie główne pozostaje polem opcjonalnym.
- Po zapisaniu i publikacji wpis pojawia się na stronie bloga.

US-020
Tytuł: Edycja wpisu blogowego
Opis: Jako redaktor chcę edytować wpis blogowy, aby aktualizować treści i poprawiać błędy.
Kryteria akceptacji:
- Formularz edycji wpisu jest dostępny z poziomu CMS.
- Redaktor może edytować wszystkie pola wpisu.
- Zmiany są widoczne na stronie publicznej po zapisaniu.

US-021
Tytuł: Usuwanie wpisu blogowego
Opis: Jako redaktor chcę usunąć wpis blogowy, aby utrzymać aktualność sekcji aktualności.
Kryteria akceptacji:
- CMS umożliwia usunięcie wpisu blogowego.
- Po usunięciu wpis znika z listy artykułów i nie jest dostępny publicznie.

### Bezpieczeństwo i dostęp
US-013
Tytuł: Bezpieczny dostęp do CMS
Opis: Jako redaktor chcę logować się do CMS przy użyciu hasła, aby chronić dane przed nieuprawnionym dostępem.
Kryteria akceptacji:
- CMS wymaga logowania (login + hasło).
- Hasła przechowywane w bezpieczny sposób (hashowanie).
- Brak dostępu do CMS dla użytkowników niezalogowanych.

### Komunikacja
US-014
Tytuł: Kontakt z zespołem
Opis: Jako użytkownik chcę wysłać wiadomość e-mail do zespołu, aby zgłosić uwagi lub błędy.
Kryteria akceptacji:
- Strona zawiera formularz kontaktowy lub adres e-mail.
- Po wysłaniu wiadomości użytkownik otrzymuje potwierdzenie wysyłki.

## 6. Metryki sukcesu
- Liczba aktywnych użytkowników aplikacji.
- Liczba wyszukiwań i filtracji obiektów.
- Średni czas spędzony na stronie obiektu.
- Liczba wydrukowanych stron obiektów.
- Liczba zgłoszeń e-mailowych i ich treść (pozytywny feedback).
- Spójność bazy danych (100% obiektów z tytułem, opisem i min. jednym zdjęciem).
- Zgodność z WCAG (min. poziom AA).
- Dostarczenie wersji beta w zakładanym terminie (5 miesięcy).
- Liczba wejść na stronę główną i przejść z niej do katalogu, mapy lub bloga.
- Liczba odsłon artykułów i średni czas spędzony na blogu.
- Liczba przejść z bloga i strony głównej do stron obiektów.

## 7. Klasyfikacja zmian

### Zmiana 1: Strona główna z informacjami o projekcie
- Kategoria: New Feature
- Rozmiar: Medium
- Priorytet: Must have dla wersji beta jako główny punkt wejścia do produktu
- Opis: Dodanie publicznej strony głównej z opisem projektu, główną nawigacją i sekcją najnowszych obiektów.

### Zmiana 2: Blog z aktualnościami / artykułami i sekcją najnowszych obiektów
- Kategoria: New Feature
- Rozmiar: Medium / Large
- Priorytet: Should have
- Opis: Dodanie prostego, zarządzanego przez CMS modułu blogowego wspierającego katalog i kierującego ruch do obiektów.

## 8. Analiza wpływu

### Komponenty i funkcje objęte zmianą
- Nawigacja publiczna i architektura informacji serwisu.
- Strona główna.
- Strona listy artykułów.
- Strona szczegółowa artykułu.
- CMS i model treści dla wpisów blogowych.
- Źródło danych dla sekcji najnowszych obiektów.

### Wpływ na harmonogram i zasoby
- Zmiany rozszerzają zakres wersji beta, ale przy obecnym stanie projektu pozostają niskoryzykowne.
- Główny wpływ po stronie designu dotyczy strony głównej, listy artykułów i szablonu artykułu.
- Główny wpływ po stronie developmentu dotyczy routingu, modelu wpisów blogowych, CMS i integracji sekcji najnowszych obiektów.

### Zależności techniczne i efekty uboczne
- Konieczne jest dodanie nowego typu treści: Article / Post.
- Lista najnowszych obiektów musi bazować na dacie dodania lub publikacji obiektu.
- Blog ma korzystać z istniejących danych katalogowych dla sekcji najnowszych obiektów.
- Zakres CMS należy utrzymać w wersji lekkiej, bez rozbudowanych workflow publikacyjnych.

### Wpływ na prace już wykonane lub rozpoczęte
- Na podstawie repozytorium przyjęto, że projekt jest na etapie PRD.
- Zmiany nie wymagają refaktoryzacji istniejącego kodu, ale powinny zostać uwzględnione przed rozpoczęciem implementacji architektury informacji i CMS.

### Wpływ na doświadczenie użytkownika i spójność produktu
- Strona główna wzmacnia zrozumienie celu produktu i porządkuje pierwszy kontakt użytkownika z serwisem.
- Blog zwiększa aktualność i wiarygodność projektu, ale pozostaje warstwą wspierającą katalog, a nie osobnym produktem.
- Spójność produktu wymaga utrzymania katalogu, mapy i stron obiektów jako głównych ścieżek wartości.

## 9. Strategia wdrożenia zmian

### Rekomendacja wdrożeniowa
- Stronę główną należy wdrożyć w bieżącym zakresie wersji beta.
- Blog i wpisy artykułowe należy wdrożyć w bieżącym zakresie wersji beta jako uproszczoną funkcję redakcyjną.
- Funkcje zaawansowane bloga należy odroczyć do późniejszej wersji produktu.

### Potrzeby refaktoryzacyjne
- Na aktualnym etapie brak potrzeb refaktoryzacji.
- Jeśli implementacja ruszy poza repozytorium przed aktualizacją wymagań, największe ryzyko zmian dotyczyć będzie routingu, głównej nawigacji, modelu treści i panelu CMS.

### Strategia testów
- Testy strony głównej:
  - poprawne wyświetlenie opisu projektu i głównych linków nawigacyjnych,
  - poprawne pobieranie i wyświetlanie najnowszych obiektów,
  - poprawne działanie na urządzeniach mobilnych i desktopowych.
- Testy bloga:
  - poprawna kolejność wpisów na liście,
  - poprawne renderowanie strony artykułu,
  - poprawne linkowanie do stron obiektów z sekcji najnowszych obiektów.
- Testy CMS:
  - możliwość dodania, edycji i usunięcia wpisu,
  - walidacja pól obowiązkowych dla wpisów.
- Testy regresyjne:
  - brak negatywnego wpływu na katalog, filtrowanie, mapę, stronę obiektu i druk.

## 10. Zaktualizowane specyfikacje techniczne i zakres beta
- Nowy typ treści: Article / Post.
- Nowe publiczne widoki: strona główna, lista artykułów, szczegół artykułu.
- Sekcja najnowszych obiektów korzysta z istniejącego źródła danych obiektów.
- Publiczny dostęp pozostaje otwarty, bez kont użytkowników końcowych.
- Wersja beta obejmuje już nie tylko katalog i CMS obiektów, ale także cienką warstwę prezentacyjną i redakcyjną.

## 11. Wpływ na interesariuszy i komunikację

### Interesariusze objęci zmianą
- Product owner / inicjator projektu.
- Deweloper.
- Designer.
- Zespół redakcyjny korzystający z CMS.
- Użytkownicy końcowi odwiedzający serwis.

### Rekomendowana komunikacja do zespołu
- Zmianę należy komunikować jako kontrolowane rozszerzenie zakresu, które poprawia pierwszy kontakt z produktem i świeżość treści.
- Należy podkreślić, że blog w wersji beta ma pozostać prosty i wspierający katalog.
- Priorytetem nadal pozostają katalog, wyszukiwarka, mapa i strony obiektów.

## 12. Ryzyka i działania ograniczające

### Ryzyka wdrożenia zmian w trakcie planowania
- Rozszerzenie prostego bloga do zbyt rozbudowanej platformy publikacyjnej.
- Rozrost zakresu CMS ponad pierwotne założenie prostoty.
- Rozproszenie uwagi zespołu między katalog a dodatkowe szablony publiczne.
- Ryzyko opóźnienia wersji beta, jeśli warstwa prezentacyjna zacznie dominować nad funkcjami podstawowymi.

### Działania ograniczające
- Utrzymać ścisły zakres v1 dla bloga: lista wpisów, szczegół wpisu, podstawowe pola i brak funkcji społecznościowych.
- Wykorzystać istniejący model danych obiektów do sekcji najnowszych obiektów.
- Traktować stronę główną i blog jako warstwę wejścia i odkrywania, a nie osobne cele produktowe.
- Potwierdzić zakres beta przed rozpoczęciem projektowania ekranów i modelu CMS.

### Ryzyka biznesowe braku wdrożenia zmian
- Słabsze pierwsze wrażenie użytkownika bez strony głównej objaśniającej projekt.
- Niższa odkrywalność i mniejsza możliwość budowania narracji wokół projektu.
- Mniej powodów do regularnych powrotów użytkowników bez sekcji aktualności.

## 13. Rekomendacja końcowa
- Włączyć stronę główną do obowiązkowego zakresu wersji beta.
- Włączyć blog / aktualności do zakresu wersji beta jako prosty moduł CMS-managed.
- Zachować ograniczony zakres funkcjonalny bloga i nie rozszerzać go o funkcje społecznościowe ani zaawansowane workflow publikacyjne.
- Utrzymać katalog obiektów, mapę i strony szczegółowe jako główny rdzeń produktu.
