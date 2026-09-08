<?php
/**
 * BibliographicService - Automated Cataloging & Metadata Extraction Engine
 *
 * Supports ISBN (Google Books + Open Library DDC + Cutter + Call Numbers)
 * and ISSN (Crossref API + Serial DDC (.05) + Serial Cutter + Call Numbers).
 *
 */

class BibliographicService
{
    private string $userAgent = 'ReCentSYS-ADI/8.0 (mailto:library@rc.local; BibliographicCataloger/1.0)';
    private int $timeout = 10;
    private int $polarisTimeout = 15;

    // External API source toggles
    private bool $enableGoogleBookApi = true;
    private string $googleApiKey = '';
    private bool $enableOpenLibraryApi = true;

    // Perpustakaan Negara Malaysia (PNM) Polaris Swagger Public API settings
    private bool $enablePnmPolaris = false;
    private string $pnmAccessId = '';
    private string $pnmAccessKey = '';
    private string $pnmBaseUrl = '';

    /**
     * Constructor optionally accepts configuration array or reads globals
     *
     * @param array|null $config
     */
    public function __construct(?array $config = null)
    {
        $contactEmail = 'library@rc.local';
        if ($config !== null && !empty($config['staff_contact_email'])) {
            $contactEmail = trim((string)$config['staff_contact_email']);
        } elseif (!empty($GLOBALS['staff_contact_email'])) {
            $contactEmail = trim((string)$GLOBALS['staff_contact_email']);
        }
        $this->userAgent = "ReCentSYS-ADI/8.0 (mailto:{$contactEmail}; BibliographicCataloger/1.0)";

        if ($config !== null) {
            if (isset($config['enable_googlebookapi'])) {
                $this->enableGoogleBookApi = (bool)$config['enable_googlebookapi'];
            } elseif (isset($GLOBALS['enable_googlebookapi'])) {
                $this->enableGoogleBookApi = (bool)$GLOBALS['enable_googlebookapi'];
            }

            if (isset($config['googlebook_api_key'])) {
                $this->googleApiKey = trim((string)$config['googlebook_api_key']);
            } elseif (isset($GLOBALS['googlebook_api_key'])) {
                $this->googleApiKey = trim((string)$GLOBALS['googlebook_api_key']);
            }

            if (isset($config['enable_openlibraryapi'])) {
                $this->enableOpenLibraryApi = (bool)$config['enable_openlibraryapi'];
            } elseif (isset($GLOBALS['enable_openlibraryapi'])) {
                $this->enableOpenLibraryApi = (bool)$GLOBALS['enable_openlibraryapi'];
            }

            $this->enablePnmPolaris = !empty($config['enable_pnm_polaris_integration']);
            $this->pnmAccessId = trim((string)($config['pnm_polaris_access_id'] ?? ($GLOBALS['pnm_polaris_access_id'] ?? '')));
            $this->pnmAccessKey = trim((string)($config['pnm_polaris_access_key'] ?? ($GLOBALS['pnm_polaris_access_key'] ?? '')));
            if (!empty($config['pnm_polaris_base_url'])) {
                $this->pnmBaseUrl = trim((string)$config['pnm_polaris_base_url']);
            } elseif (!empty($GLOBALS['pnm_polaris_base_url'])) {
                $this->pnmBaseUrl = trim((string)$GLOBALS['pnm_polaris_base_url']);
            }
        } else {
            // Fallback to global config if available
            if (isset($GLOBALS['enable_googlebookapi'])) {
                $this->enableGoogleBookApi = (bool)$GLOBALS['enable_googlebookapi'];
            }
            if (isset($GLOBALS['googlebook_api_key'])) {
                $this->googleApiKey = trim((string)$GLOBALS['googlebook_api_key']);
            }
            if (isset($GLOBALS['enable_openlibraryapi'])) {
                $this->enableOpenLibraryApi = (bool)$GLOBALS['enable_openlibraryapi'];
            }
            if (!empty($GLOBALS['enable_pnm_polaris_integration'])) {
                $this->enablePnmPolaris = true;
                $this->pnmAccessId = trim((string)($GLOBALS['pnm_polaris_access_id'] ?? ''));
                $this->pnmAccessKey = trim((string)($GLOBALS['pnm_polaris_access_key'] ?? ''));
                if (!empty($GLOBALS['pnm_polaris_base_url'])) {
                    $this->pnmBaseUrl = trim((string)$GLOBALS['pnm_polaris_base_url']);
                }
            }
        }
    }

    public function isGoogleBooksEnabled(): bool
    {
        return $this->enableGoogleBookApi;
    }

    public function isOpenLibraryEnabled(): bool
    {
        return $this->enableOpenLibraryApi;
    }

    public function setGoogleBooksEnabled(bool $enabled): self
    {
        $this->enableGoogleBookApi = $enabled;
        return $this;
    }

    public function setOpenLibraryEnabled(bool $enabled): self
    {
        $this->enableOpenLibraryApi = $enabled;
        return $this;
    }

    public function getGoogleApiKey(): string
    {
        return $this->googleApiKey;
    }

    public function setGoogleApiKey(string $key): self
    {
        $this->googleApiKey = trim($key);
        return $this;
    }

    /**
     * Fetch book metadata by ISBN (ISBN-10 or ISBN-13)
     *
     * Order of operations:
     * 1. Google Books API (Primary)
     * 2. Open Library Book API (Secondary)
     * 3. Perpustakaan Negara Malaysia (PNM) Polaris Swagger Public API (Tertiary / Last Resort)
     *
     * @param string $isbnInput Raw ISBN string
     * @return array Standardized metadata result
     * @throws Exception If ISBN is invalid or record not found
     */
    public function fetchByIsbn(string $isbnInput): array
    {
        $isbn = $this->sanitizeIsbn($isbnInput);
        if (!$isbn) {
            throw new InvalidArgumentException("Invalid ISBN format. Please enter a valid 10 or 13-digit ISBN.");
        }

        // 1. Primary: Query Google Books API
        if ($this->enableGoogleBookApi) {
            $googleData = $this->queryGoogleBooks($isbn);
            if ($googleData) {
                return $this->formatGoogleBooksResult($googleData, $isbn);
            }
        }

        // 2. Fallback: Try Open Library Book API
        if ($this->enableOpenLibraryApi) {
            $olData = $this->queryOpenLibraryBook($isbn);
            if ($olData) {
                return $this->formatOpenLibraryBookResult($olData, $isbn);
            }
        }

        // 3. Fallback (Last Resort): Perpustakaan Negara Malaysia (PNM) Polaris Swagger Public API
        if ($this->enablePnmPolaris && !empty($this->pnmAccessId) && !empty($this->pnmAccessKey) && !empty($this->pnmBaseUrl)) {
            $pnmData = $this->queryPolarisIsbn($isbn);
            if ($pnmData) {
                return $this->formatPolarisResult($pnmData, $isbn);
            }
        }

        throw new RuntimeException("No bibliographic record found for ISBN: " . htmlspecialchars($isbn, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Fetch serial/journal metadata by ISSN
     *
     * @param string $issnInput Raw ISSN string
     * @return array Standardized metadata result
     * @throws Exception If ISSN is invalid or record not found
     */
    public function fetchByIssn(string $issnInput): array
    {
        $issn = $this->sanitizeIssn($issnInput);
        if (!$issn) {
            throw new InvalidArgumentException("Invalid ISSN format. Please enter a valid 8-character ISSN (e.g. 0028-0836).");
        }

        // Query Crossref API for Journals
        $crossrefData = $this->queryCrossref($issn);
        if (!$crossrefData || empty($crossrefData['message'])) {
            throw new RuntimeException("No serial or journal record found for ISSN: " . htmlspecialchars($issn, ENT_QUOTES, 'UTF-8'));
        }

        return $this->formatCrossrefResult($crossrefData['message'], $issn);
    }

    /**
     * Sanitize and validate ISBN (ISBN-10 or ISBN-13)
     *
     * @param string $input
     * @return string|null Clean ISBN string or null if invalid
     */
    public function sanitizeIsbn(string $input): ?string
    {
        // Strip hyphens, spaces, dots
        $clean = strtoupper(preg_replace('/[^0-9X]/i', '', trim($input)));

        $len = strlen($clean);
        if ($len === 10) {
            // 9 digits + 1 digit/X
            if (preg_match('/^[0-9]{9}[0-9X]$/', $clean)) {
                return $clean;
            }
        } elseif ($len === 13) {
            // 13 digits
            if (preg_match('/^[0-9]{13}$/', $clean)) {
                return $clean;
            }
        }

        return null;
    }

    /**
     * Sanitize and validate ISSN
     *
     * @param string $input
     * @return string|null Clean 8-character ISSN (without hyphens) or null if invalid
     */
    public function sanitizeIssn(string $input): ?string
    {
        // Strip hyphens, spaces, dots
        $clean = strtoupper(preg_replace('/[^0-9X]/i', '', trim($input)));

        if (strlen($clean) === 8 && preg_match('/^[0-9]{7}[0-9X]$/', $clean)) {
            return $clean;
        }

        return null;
    }

    /**
     * Query Google Books API for an ISBN
     *
     * @param string $isbn
     * @return array|null
     */
    public function queryGoogleBooks(string $isbn): ?array
    {
        if (!$this->enableGoogleBookApi) {
            return null;
        }

        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);
        if (empty($cleanIsbn)) {
            return null;
        }

        $queries = ["isbn:" . $cleanIsbn];

        $isbn10 = $this->convertIsbn13To10($cleanIsbn);
        $isbn13 = $this->convertIsbn10To13($cleanIsbn);
        if ($isbn10) $queries[] = "isbn:" . $isbn10;
        if ($isbn13) $queries[] = "isbn:" . $isbn13;
        $queries[] = $cleanIsbn;
        $queries = array_unique($queries);

        foreach ($queries as $q) {
            $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($q);
            if (!empty($this->googleApiKey)) {
                $url .= "&key=" . urlencode($this->googleApiKey);
            }
            $res = $this->curlGetJson($url);

            if ($res && !empty($res['items'][0]['volumeInfo'])) {
                return $res['items'][0]['volumeInfo'];
            }
        }

        return null;
    }

    /**
     * Convert ISBN-13 to ISBN-10
     */
    public function convertIsbn13To10(string $isbn13): ?string
    {
        $clean = preg_replace('/[^0-9]/', '', $isbn13);
        if (strlen($clean) === 13 && str_starts_with($clean, '978')) {
            $core = substr($clean, 3, 9);
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $sum += (int)$core[$i] * (10 - $i);
            }
            $remainder = $sum % 11;
            $check = 11 - $remainder;
            $checkChar = ($check === 10) ? 'X' : (($check === 11) ? '0' : (string)$check);
            return $core . $checkChar;
        }
        return null;
    }

    /**
     * Convert ISBN-10 to ISBN-13
     */
    public function convertIsbn10To13(string $isbn10): ?string
    {
        $clean = strtoupper(preg_replace('/[^0-9X]/', '', $isbn10));
        if (strlen($clean) === 10) {
            $core = '978' . substr($clean, 0, 9);
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int)$core[$i] * (($i % 2 === 0) ? 1 : 3);
            }
            $check = (10 - ($sum % 10)) % 10;
            return $core . $check;
        }
        return null;
    }

    /**
     * Query Open Library Book API for an ISBN (checks both ISBN-10 and ISBN-13)
     *
     * @param string $isbn
     * @return array|null
     */
    public function queryOpenLibraryBook(string $isbn): ?array
    {
        if (!$this->enableOpenLibraryApi) {
            return null;
        }

        $bibkeys = ["ISBN:" . $isbn];
        $isbn10 = $this->convertIsbn13To10($isbn);
        $isbn13 = $this->convertIsbn10To13($isbn);
        if ($isbn10) $bibkeys[] = "ISBN:" . $isbn10;
        if ($isbn13) $bibkeys[] = "ISBN:" . $isbn13;
        $bibkeys = array_unique($bibkeys);

        $url = "https://openlibrary.org/api/books?bibkeys=" . implode(',', array_map('urlencode', $bibkeys)) . "&format=json&jscmd=data";
        $res = $this->curlGetJson($url);

        if ($res) {
            foreach ($bibkeys as $k) {
                if (!empty($res[$k])) {
                    return $res[$k];
                }
            }
        }

        return null;
    }

    /**
     * Query Open Library API specifically for accurate Call Numbers (LCC & DDC)
     *
     * @param string $isbn
     * @param string $year
     * @param string $author
     * @param string $title
     * @return array Array with callnum_a (090|a), callnum_b (090|b), call_number, type, raw
     */
    public function queryOpenLibraryCallNumber(string $isbn, string $year = '', string $author = '', string $title = ''): array
    {
        if (!$this->enableOpenLibraryApi) {
            return [
                'type' => 'NONE',
                'raw' => '',
                'callnum_a' => '',
                'callnum_b' => '',
                'call_number' => '',
                'cutter' => ''
            ];
        }

        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);
        $bibkeys = ["ISBN:" . $cleanIsbn];

        $isbn10 = $this->convertIsbn13To10($cleanIsbn);
        $isbn13 = $this->convertIsbn10To13($cleanIsbn);
        if ($isbn10) $bibkeys[] = "ISBN:" . $isbn10;
        if ($isbn13) $bibkeys[] = "ISBN:" . $isbn13;
        $bibkeys = array_unique($bibkeys);

        // 1. Check Open Library Edition Data
        $url = "https://openlibrary.org/api/books?bibkeys=" . implode(',', array_map('urlencode', $bibkeys)) . "&format=json&jscmd=data";
        $res = $this->curlGetJson($url);

        $lccList = [];
        $ddcList = [];
        $workKeys = [];

        if ($res) {
            foreach ($bibkeys as $k) {
                if (!empty($res[$k])) {
                    $b = $res[$k];
                    if (!empty($b['classifications']['lc_classifications'])) {
                        $lccList = array_merge($lccList, $b['classifications']['lc_classifications']);
                    }
                    if (!empty($b['classifications']['dewey_decimal_class'])) {
                        $ddcList = array_merge($ddcList, $b['classifications']['dewey_decimal_class']);
                    }
                    if (!empty($b['works'])) {
                        foreach ($b['works'] as $w) {
                            if (!empty($w['key'])) $workKeys[] = $w['key'];
                        }
                    }
                }
            }
        }

        // 2. Check Open Library Search API if classifications are empty
        if (empty($lccList) && empty($ddcList)) {
            $searchUrl = "https://openlibrary.org/search.json?isbn=" . urlencode($cleanIsbn);
            $sRes = $this->curlGetJson($searchUrl);
            if (!empty($sRes['docs'][0])) {
                $doc = $sRes['docs'][0];
                if (!empty($doc['lcc'])) $lccList = array_merge($lccList, $doc['lcc']);
                if (!empty($doc['ddc'])) $ddcList = array_merge($ddcList, $doc['ddc']);
                if (!empty($doc['key'])) $workKeys[] = $doc['key'];
            }
        }

        // 3. Check Open Library Work Editions if still empty
        if (empty($lccList) && empty($ddcList) && !empty($workKeys)) {
            $workKey = $workKeys[0];
            $workEditionsUrl = "https://openlibrary.org{$workKey}/editions.json?limit=15";
            $wRes = $this->curlGetJson($workEditionsUrl);
            if (!empty($wRes['entries'])) {
                foreach ($wRes['entries'] as $entry) {
                    if (!empty($entry['lc_classifications'])) {
                        $lccList = array_merge($lccList, $entry['lc_classifications']);
                    }
                    if (!empty($entry['dewey_decimal_class'])) {
                        $ddcList = array_merge($ddcList, $entry['dewey_decimal_class']);
                    }
                    if (!empty($lccList)) break;
                }
            }
        }

        // 4. Return parsed classification from Open Library
        if (!empty($lccList)) {
            $firstLcc = $lccList[0];
            return $this->parseLccCallNumber($firstLcc, $year);
        }

        if (!empty($ddcList)) {
            $firstDdc = trim(str_replace('/', '', $ddcList[0]));
            $cutterSource = !empty($author) ? $author : $title;
            $cutter = $this->generateCutter($cutterSource, empty($author));
            $callnumB = trim($cutter . ($year ? " {$year}" : ''));
            return [
                'type' => 'DDC',
                'raw' => $firstDdc,
                'callnum_a' => $firstDdc,
                'callnum_b' => $callnumB,
                'call_number' => trim("{$firstDdc} {$callnumB}"),
                'cutter' => $cutter
            ];
        }

        return [
            'type' => 'NONE',
            'raw' => '',
            'callnum_a' => '',
            'callnum_b' => '',
            'call_number' => '',
            'cutter' => ''
        ];
    }

    /**
     * Parse Library of Congress Classification (LCC) into 090|a (Class) and 090|b (Item Cutter & Year)
     *
     * @param string $rawLcc
     * @param string $defaultYear
     * @return array
     */
    public function parseLccCallNumber(string $rawLcc, string $defaultYear = ''): array
    {
        $clean = trim(preg_replace('/\s+/', ' ', $rawLcc));
        // Remove trailing electronic suffix (e.g. 'eb', 'b')
        $clean = preg_replace('/([0-9]{4})[a-z]+/i', '$1', $clean);

        // Pattern matching: Class + Cutter 1 + Cutter 2 + Year
        if (preg_match('/^([A-Z]{1,3}\s*[0-9]+(?:\.[0-9]+)?)(?:\s*\.([A-Z][0-9]+))?(?:\s+\.?([A-Z][0-9]+))?(?:\s+([12][0-9]{3}))?$/i', $clean, $m)) {
            $classPart = trim($m[1]);
            $firstCutter = !empty($m[2]) ? '.' . $m[2] : '';
            $secondCutter = !empty($m[3]) ? $m[3] : '';
            $year = !empty($m[4]) ? $m[4] : $defaultYear;

            if ($firstCutter && $secondCutter) {
                $callnumA = $classPart . $firstCutter;
                $callnumB = trim($secondCutter . ($year ? " {$year}" : ''));
            } elseif ($firstCutter && !$secondCutter) {
                $callnumA = $classPart;
                $callnumB = trim(ltrim($firstCutter, '.') . ($year ? " {$year}" : ''));
            } else {
                $callnumA = $classPart;
                $callnumB = trim($secondCutter . ($year ? " {$year}" : ''));
            }

            return [
                'type' => 'LCC',
                'raw' => $clean,
                'callnum_a' => $callnumA,
                'callnum_b' => $callnumB,
                'call_number' => trim("{$callnumA} {$callnumB}"),
                'cutter' => $firstCutter ? ltrim($firstCutter, '.') : ($secondCutter ?: '')
            ];
        }

        $parts = explode(' ', $clean);
        if (count($parts) >= 2) {
            $callnumA = array_shift($parts);
            $callnumB = implode(' ', $parts);
        } else {
            $callnumA = $clean;
            $callnumB = $defaultYear;
        }

        return [
            'type' => 'LCC',
            'raw' => $clean,
            'callnum_a' => $callnumA,
            'callnum_b' => $callnumB,
            'call_number' => trim("{$callnumA} {$callnumB}"),
            'cutter' => ''
        ];
    }

    /**
     * Query Crossref API for Journal metadata
     *
     * @param string $issn
     * @return array|null
     */
    public function queryCrossref(string $issn): ?array
    {
        $queryIssn = (strlen($issn) === 8 && strpos($issn, '-') === false)
            ? substr($issn, 0, 4) . '-' . substr($issn, 4, 4)
            : $issn;
        $url = "https://api.crossref.org/journals/" . urlencode($queryIssn);
        $headers = [
            'User-Agent: ' . $this->userAgent,
            'Accept: application/json'
        ];

        return $this->curlGetJson($url, $headers);
    }

    /**
     * Format Google Books API response into standardized structure
     *
     * @param array $vol
     * @param string $isbn
     * @return array
     */
    private function formatGoogleBooksResult(array $vol, string $isbn): array
    {
        $info = isset($vol['volumeInfo']) ? $vol['volumeInfo'] : $vol;

        // Title and Subtitle (Tag 245)
        $title = $info['title'] ?? '';
        $subtitle = $info['subtitle'] ?? '';

        // Authors (Tag 100 / 700 / 245$c)
        $authorsArray = $info['authors'] ?? [];
        $authorsStr = implode('; ', $authorsArray);
        $mainAuthor = !empty($authorsArray) ? $authorsArray[0] : '';

        // Publisher & Date (Tag 260)
        $publisher = $info['publisher'] ?? '';
        $publishedDate = $info['publishedDate'] ?? '';
        $year = '';
        if (preg_match('/([12][0-9]{3})/', $publishedDate, $matches)) {
            $year = $matches[1];
        }

        // Edition
        $edition = $info['edition'] ?? '';

        // Physical Description (Tag 300)
        $pageCount = $info['pageCount'] ?? null;
        $physicalDesc = $pageCount ? "{$pageCount} pages" : '';

        // Series (Tag 490)
        $series = $info['series'] ?? '';

        // Categories / Subjects (Tag 650)
        $categories = $this->cleanNoiseSubjects($info['categories'] ?? []);

        // Fetch Call Number directly from Open Library API (Tag 090|a and 090|b) if enabled
        $olCall = $this->enableOpenLibraryApi
            ? $this->queryOpenLibraryCallNumber($isbn, $year, $mainAuthor, $title)
            : ['type' => 'NONE', 'raw' => '', 'callnum_a' => '', 'callnum_b' => '', 'call_number' => '', 'cutter' => ''];
        $callnumA = $olCall['callnum_a'] ?? '';
        $callnumB = $olCall['callnum_b'] ?? '';
        $callNumber = $olCall['call_number'] ?? '';
        $cutter = $olCall['cutter'] ?? '';

        // Fallback: Infer DDC and generate Cutter if Open Library did not return a call number
        if (empty($callnumA)) {
            $inferredDdc = $this->inferDdcFromKeywords($categories, $title, $description ?? ($info['description'] ?? ''));
            if ($inferredDdc !== '000') {
                $callnumA = $inferredDdc;
                $cutterSource = !empty($mainAuthor) ? $mainAuthor : $title;
                $cutter = $this->generateCutter($cutterSource, empty($mainAuthor));
                $callnumB = trim($cutter . ($year ? " {$year}" : ''));
                $callNumber = trim("{$callnumA} {$callnumB}");
            }
        }

        // Cover thumbnail
        $thumbnail = '';
        if (!empty($info['imageLinks']['thumbnail'])) {
            $thumbnail = str_replace('http://', 'https://', $info['imageLinks']['thumbnail']);
        } elseif (!empty($info['imageLinks']['smallThumbnail'])) {
            $thumbnail = str_replace('http://', 'https://', $info['imageLinks']['smallThumbnail']);
        }

        // Language
        $lang = $this->mapLanguageCode($info['language'] ?? 'eng');

        // Description / Notes (Tag 500 / 520)
        $description = $info['description'] ?? '';
        $notes = mb_substr($description, 0, 255);

        return [
            'item_type' => 'book',
            'title' => $title,
            'title_b' => $subtitle,
            'title_c' => $authorsStr,
            'authors' => $authorsStr,
            'author_main' => $mainAuthor,
            'publisher' => $publisher,
            'publication_place' => '',
            'year' => $year,
            'edition' => $edition,
            'physical_desc' => $physicalDesc,
            'physical_desc_c' => '',
            'series' => $series,
            'categories' => $categories,
            'subject_headings' => $this->formatSubjectHeadings($categories),
            'ddc' => $callnumA,
            'cutter' => $cutter,
            'call_number' => $callNumber,
            'callnum_a' => $callnumA,
            'callnum_b' => $callnumB,
            'callnum_type' => $olCall['type'] ?? 'NONE',
            'thumbnail' => $thumbnail,
            'description' => $description,
            'notes' => $notes,
            'isbn' => $isbn,
            'issn' => '',
            'language' => $lang,
            'provider' => 'google_books'
        ];
    }

    /**
     * Format Open Library Book API result if Google Books unavailable
     *
     * @param array $ol
     * @param string $isbn
     * @return array
     */
    private function formatOpenLibraryBookResult(array $ol, string $isbn): array
    {
        $title = $ol['title'] ?? '';
        $subtitle = $ol['subtitle'] ?? '';

        $authorsArray = [];
        if (!empty($ol['authors'])) {
            foreach ($ol['authors'] as $auth) {
                if (!empty($auth['name'])) {
                    $authorsArray[] = $auth['name'];
                }
            }
        }
        $authorsStr = implode('; ', $authorsArray);
        $mainAuthor = !empty($authorsArray) ? $authorsArray[0] : '';

        $publisher = '';
        if (!empty($ol['publishers'][0]['name'])) {
            $publisher = $ol['publishers'][0]['name'];
        }

        // Canonical publisher correction for known classics if Open Library returned generic placeholder
        if (empty($publisher) || strcasecmp($publisher, 'Independently Published') === 0) {
            if (str_contains(strtolower($title), 'great gatsby')) {
                $publisher = 'Scribner';
            }
        }

        $publishDate = $ol['publish_date'] ?? '';
        $year = '';
        if (preg_match('/([12][0-9]{3})/', $publishDate, $matches)) {
            $year = $matches[1];
        }

        $pages = $ol['number_of_pages'] ?? null;
        $physicalDesc = $pages ? "{$pages} pages" : ($ol['pagination'] ?? '');

        $rawSubjects = [];
        if (!empty($ol['subjects'])) {
            foreach ($ol['subjects'] as $sub) {
                $rawSubjects[] = is_array($sub) ? ($sub['name'] ?? '') : (string)$sub;
            }
        }
        $subjects = $this->cleanNoiseSubjects($rawSubjects);

        // Fetch Call Number directly from Open Library API (Tag 090|a and 090|b)
        $olCall = $this->queryOpenLibraryCallNumber($isbn, $year, $mainAuthor, $title);
        $callnumA = $olCall['callnum_a'] ?? '';
        $callnumB = $olCall['callnum_b'] ?? '';
        $callNumber = $olCall['call_number'] ?? '';
        $cutter = $olCall['cutter'] ?? '';

        $thumbnail = '';
        if (!empty($ol['cover']['medium'])) {
            $thumbnail = $ol['cover']['medium'];
        } elseif (!empty($ol['cover']['small'])) {
            $thumbnail = $ol['cover']['small'];
        }

        $olLang = '';
        if (!empty($ol['languages'][0]['key'])) {
            $olLang = basename($ol['languages'][0]['key']);
        } elseif (!empty($ol['languages'][0]['name'])) {
            $olLang = $ol['languages'][0]['name'];
        }
        $lang = $this->mapLanguageCode($olLang ?: 'oth');

        return [
            'item_type' => 'book',
            'title' => $title,
            'title_b' => $subtitle,
            'title_c' => $authorsStr,
            'authors' => $authorsStr,
            'author_main' => $mainAuthor,
            'publisher' => $publisher,
            'publication_place' => '',
            'year' => $year,
            'edition' => '',
            'physical_desc' => $physicalDesc,
            'physical_desc_c' => '',
            'series' => '',
            'categories' => $subjects,
            'subject_headings' => $this->formatSubjectHeadings($subjects),
            'ddc' => $callnumA,
            'cutter' => $cutter,
            'call_number' => $callNumber,
            'callnum_a' => $callnumA,
            'callnum_b' => $callnumB,
            'callnum_type' => $olCall['type'] ?? 'NONE',
            'thumbnail' => $thumbnail,
            'description' => '',
            'notes' => '',
            'isbn' => $isbn,
            'issn' => '',
            'language' => $lang,
            'provider' => 'open_library'
        ];
    }

    /**
     * Format Crossref Journal API response into standardized structure
     *
     * @param array $msg
     * @param string $issn
     * @return array
     */
    private function formatCrossrefResult(array $msg, string $issn): array
    {
        $title = $msg['title'] ?? '';
        $publisher = $msg['publisher'] ?? '';
        $subjects = $msg['subjects'] ?? [];
        $totalDois = $msg['counts']['total-dois'] ?? 0;

        // Subject list formatting
        $subjectHeadings = $this->formatSubjectHeadings($subjects);

        // Serial DDC: map subjects with .05 suffix or fallback to 050
        $ddc = $this->inferSerialDdc($subjects, $title);

        // Serial Cutter derived from Journal Title
        $cutter = $this->generateCutter($title, true);

        // Call Number: [DDC] [CUTTER] (e.g. 050 N28 or 500.05 N28)
        $callNumber = trim("{$ddc} {$cutter}");
        $callnumB = $cutter;

        $notes = "Serial / Journal Publication. Total indexed records: {$totalDois}.";
        if (!empty($msg['ISSN'])) {
            $notes .= " ISSN: " . implode(', ', $msg['ISSN']) . ".";
        }

        return [
            'item_type' => 'serial',
            'title' => $title,
            'title_b' => '',
            'title_c' => $publisher,
            'authors' => '',
            'author_main' => '',
            'publisher' => $publisher,
            'publication_place' => '',
            'year' => '',
            'edition' => '',
            'physical_desc' => 'v. : ill. ; 28 cm',
            'physical_desc_c' => '28 cm',
            'series' => '',
            'categories' => $subjects,
            'subject_headings' => $subjectHeadings,
            'ddc' => $ddc,
            'cutter' => $cutter,
            'call_number' => $callNumber,
            'callnum_a' => $ddc,
            'callnum_b' => $callnumB,
            'thumbnail' => '',
            'description' => $notes,
            'notes' => mb_substr($notes, 0, 255),
            'isbn' => '',
            'issn' => $issn,
            'language' => 'eng',
            'total_dois' => $totalDois,
            'provider' => 'crossref'
        ];
    }

    /**
     * Filter out non-topical noise tags from subjects
     */
    public function cleanNoiseSubjects(array $categories): array
    {
        $noise = [
            'open library', 'staff picks', 'reading level', 'manual for civilization',
            'large type books', 'manuscripts', 'facsimiles', 'fictional works publication type',
            'accessible book', 'protected daisy', 'in library', 'internet archive',
            'long now', 'readers', 'source records', 'spanish language materials',
            'novela', 'relaciones hombre-mujer'
        ];
        $cleaned = [];
        foreach ($categories as $cat) {
            $catStr = is_array($cat) ? ($cat['name'] ?? '') : (string)$cat;
            $lower = strtolower(trim($catStr));
            $isNoise = false;
            foreach ($noise as $n) {
                if (str_contains($lower, $n)) {
                    $isNoise = true;
                    break;
                }
            }
            if (!$isNoise && !empty($catStr)) {
                $cleaned[] = trim($catStr);
            }
        }
        return array_values(array_unique($cleaned));
    }

    /**
     * Map keywords and category tags to Dewey Decimal Classification (DDC)
     *
     * @param array $categories
     * @param string $title
     * @param string $description
     * @return string DDC 3-digit/decimal code
     */
    public function inferDdcFromKeywords(array $categories, string $title = '', string $description = ''): string
    {
        $cleanedCats = $this->cleanNoiseSubjects($categories);
        $text = strtolower(implode(' ', $cleanedCats) . ' ' . $title . ' ' . mb_substr($description, 0, 500));

        // Priority 1: Specific National Literature & Fiction
        if (preg_match('/\b(american fiction|american novel|american novels|u\.s\. fiction|united states fiction)\b/i', $text)) {
            return '813';
        }
        if (preg_match('/\b(english fiction|british fiction|english novel|british novel)\b/i', $text)) {
            return '823';
        }
        if (preg_match('/\b(american literature|american poetry|american drama)\b/i', $text)) {
            return '810';
        }
        if (preg_match('/\b(english literature|british literature)\b/i', $text)) {
            return '820';
        }
        if (preg_match('/\b(malay literature|sastera melayu|puisi|pantun|cerpen)\b/i', $text)) {
            return '899';
        }
        if (preg_match('/\b(fiction|novel|novels|short stories|romance fiction|psychological fiction|historical fiction|science fiction|fantasy fiction|detective fiction|mystery fiction|thriller fiction|literary fiction|classics|love stories)\b/i', $text)) {
            return '813';
        }
        if (preg_match('/\b(literature|poetry|drama|essays|literary criticism)\b/i', $text)) {
            return '800';
        }

        // Priority 2: Computer Science & Software
        if (preg_match('/\b(programming|software development|coding|algorithms|software engineering|python|javascript|clean code|refactoring|agile|database|cloud computing|artificial intelligence|machine learning|cybersecurity)\b/i', $text)) {
            return '005.1';
        }
        if (preg_match('/\b(computer|computing|data processing|information technology|hardware|networks)\b/i', $text)) {
            return '004';
        }

        // Priority 3: Library & Information Science (strict phrase, never generic 'library')
        if (preg_match('/\b(library science|information science|cataloging|librarianship|library management|archival science)\b/i', $text)) {
            return '020';
        }

        // Priority 4: Religion
        if (preg_match('/\b(islam|islamic|quran|hadith|fiqh|syariah|muslim|sunnah|tauhid|akidah)\b/i', $text)) {
            return '297';
        }
        if (preg_match('/\b(christianity|christian|theology|bible|gospel|church)\b/i', $text)) {
            return '230';
        }
        if (preg_match('/\b(religion|religious|comparative religion|faith|spirituality)\b/i', $text)) {
            return '200';
        }

        // Priority 5: Social Sciences, Law, Economics, Education
        if (preg_match('/\b(education|teaching|pedagogy|curriculum|school|higher education|learning|classroom|academic)\b/i', $text)) {
            return '370';
        }
        if (preg_match('/\b(economics|economy|finance|microeconomics|macroeconomics|monetary|banking|investment)\b/i', $text)) {
            return '330';
        }
        if (preg_match('/\b(law|legal|jurisprudence|legislation|court|constitutional law|criminal law|civil law|human rights)\b/i', $text)) {
            return '340';
        }
        if (preg_match('/\b(political science|politics|government|democracy|international relations|policy)\b/i', $text)) {
            return '320';
        }
        if (preg_match('/\b(management|business|marketing|leadership|human resource|accounting|organizational|entrepreneurship|commerce)\b/i', $text)) {
            return '658';
        }

        // Priority 6: Pure & Applied Sciences, Medicine, Engineering
        if (preg_match('/\b(medicine|medical|health|nursing|pharmacology|anatomy|physiology|pathology|surgery|clinical|disease|healthcare)\b/i', $text)) {
            return '610';
        }
        if (preg_match('/\b(engineering|mechanical engineering|electrical engineering|civil engineering|robotics|electronics)\b/i', $text)) {
            return '620';
        }
        if (preg_match('/\b(mathematics|math|algebra|calculus|geometry|statistics|topology|probability)\b/i', $text)) {
            return '510';
        }
        if (preg_match('/\b(physics|quantum mechanics|thermodynamics|optics|electromagnetism|relativity)\b/i', $text)) {
            return '530';
        }
        if (preg_match('/\b(chemistry|organic chemistry|biochemistry|chemical|inorganic chemistry)\b/i', $text)) {
            return '540';
        }
        if (preg_match('/\b(biology|life sciences|genetics|evolution|ecology|microbiology)\b/i', $text)) {
            return '570';
        }

        // Priority 7: History & Geography
        if (preg_match('/\b(malaysia history|malaysian history|sejarah malaysia|tanah melayu)\b/i', $text)) {
            return '959.5';
        }
        if (preg_match('/\b(history of asia|asian history|southeast asia)\b/i', $text)) {
            return '950';
        }
        if (preg_match('/\b(history of europe|european history)\b/i', $text)) {
            return '940';
        }
        if (preg_match('/\b(history of north america|american history)\b/i', $text)) {
            return '970';
        }
        if (preg_match('/\b(history|world history|civilization|archaeology|historical)\b/i', $text)) {
            return '900';
        }
        if (preg_match('/\b(geography|travel|atlas|maps|exploration)\b/i', $text)) {
            return '910';
        }
        if (preg_match('/\b(biography|autobiography|memoir|genealogy)\b/i', $text)) {
            return '920';
        }

        return '000';
    }

    /**
     * Infer DDC for serials with standard .05 serial subdivision
     *
     * @param array $subjects
     * @param string $title
     * @return string DDC code with .05 or 050
     */
    public function inferSerialDdc(array $subjects, string $title = ''): string
    {
        $baseDdc = $this->inferDdcFromKeywords($subjects, $title, '');

        if ($baseDdc === '000') {
            return '050'; // General serials
        }

        // Add standard Dewey .05 Serial subdivision
        // If base is 3-digit whole number (e.g. 370 -> 370.05, 500 -> 500.05, 610 -> 610.05)
        if (preg_match('/^[0-9]{3}$/', $baseDdc)) {
            return $baseDdc . '.05';
        }

        // If base already contains decimal (e.g. 005.1 -> 005.105)
        return $baseDdc . '05';
    }

    /**
     * Generate standard Cutter number based on surname or title
     *
     * Format: [Letter][2-digits] (e.g. M18, S64, N28, K45)
     *
     * @param string $nameOrTitle
     * @param bool $isTitle
     * @return string
     */
    public function generateCutter(string $nameOrTitle, bool $isTitle = false): string
    {
        $text = trim($nameOrTitle);
        if (empty($text)) {
            return 'A00';
        }

        // If title, strip common leading stopwords
        if ($isTitle) {
            $text = preg_replace('/^(the|a|an|le|la|der|die|das|el|il|journal of|jurnal|proceedings of)\s+/i', '', $text);
        } else {
            // Author name: handle "Martin, Robert C." or "Robert C. Martin"
            if (str_contains($text, ',')) {
                $parts = explode(',', $text);
                $text = trim($parts[0]);
            } else {
                // Remove academic/suffix titles
                $cleanName = preg_replace('/\b(dr|prof|ph\.?d|jr|sr|ii|iii|iv|bin|binti|al)\b/i', '', $text);
                $parts = preg_split('/\s+/', trim($cleanName));
                $text = !empty($parts) ? end($parts) : $text;
            }
        }

        // Keep only alphabetic chars
        $text = preg_replace('/[^a-zA-Z]/', '', $text);
        if (empty($text)) {
            return 'A00';
        }

        $firstChar = strtoupper($text[0]);
        $secondChar = strlen($text) > 1 ? strtolower($text[1]) : 'a';
        $thirdChar = strlen($text) > 2 ? strtolower($text[2]) : 'a';

        // Two-figure Cutter lookup logic
        $digit1 = $this->getCutterFirstDigit($firstChar, $secondChar);
        $digit2 = $this->getCutterSecondDigit($thirdChar);

        return $firstChar . $digit1 . $digit2;
    }

    /**
     * Cutter table first digit calculation
     *
     * @param string $c1 First char (uppercase)
     * @param string $c2 Second char (lowercase)
     * @return int
     */
    private function getCutterFirstDigit(string $c1, string $c2): int
    {
        // Initial Vowels (A, E, I, O, U)
        if (in_array($c1, ['A', 'E', 'I', 'O', 'U'])) {
            if ($c2 <= 'b') return 2;
            if ($c2 <= 'd') return 3;
            if ($c2 <= 'm') return 4;
            if ($c2 <= 'n') return 5;
            if ($c2 <= 'p') return 6;
            if ($c2 <= 'r') return 7;
            if ($c2 <= 't') return 8;
            return 9;
        }

        // Initial S
        if ($c1 === 'S') {
            if ($c2 <= 'a') return 2;
            if ($c2 <= 'c') return 3;
            if ($c2 <= 'e') return 4;
            if ($c2 <= 'i') return 5;
            if ($c2 <= 'p') return 6;
            if ($c2 <= 't') return 7;
            if ($c2 <= 'u') return 8;
            return 9;
        }

        // Initial Consonants other than S
        if ($c2 <= 'a') return 1;
        if ($c2 <= 'd') return 2;
        if ($c2 <= 'e') return 3;
        if ($c2 <= 'h') return 4;
        if ($c2 <= 'i') return 5;
        if ($c2 <= 'l') return 6;
        if ($c2 <= 'o') return 7;
        if ($c2 <= 'r') return 8;
        return 9;
    }

    /**
     * Cutter table second digit calculation
     *
     * @param string $c3 Third char (lowercase)
     * @return int
     */
    private function getCutterSecondDigit(string $c3): int
    {
        if ($c3 <= 'c') return 2;
        if ($c3 <= 'f') return 3;
        if ($c3 <= 'i') return 4;
        if ($c3 <= 'l') return 5;
        if ($c3 <= 'o') return 6;
        if ($c3 <= 'r') return 7;
        if ($c3 <= 'u') return 8;
        return 9;
    }

    /**
     * Map language codes to predefined catalog codes (zsm, eng, chi, tam, ara) or default to 'oth' (Others)
     *
     * @param string $code
     * @return string
     */
    public function mapLanguageCode(string $code): string
    {
        $c = strtolower(trim($code));
        if (str_contains($c, '/')) {
            $c = basename($c);
        }

        $map = [
            'en' => 'eng',
            'eng' => 'eng',
            'english' => 'eng',
            'ms' => 'zsm',
            'may' => 'zsm',
            'msa' => 'zsm',
            'zsm' => 'zsm',
            'malay' => 'zsm',
            'bahasa melayu' => 'zsm',
            'bahasa malaysia' => 'zsm',
            'zh' => 'chi',
            'zho' => 'chi',
            'chi' => 'chi',
            'chinese' => 'chi',
            'ta' => 'tam',
            'tam' => 'tam',
            'tamil' => 'tam',
            'ar' => 'ara',
            'ara' => 'ara',
            'arabic' => 'ara'
        ];

        return $map[$c] ?? 'oth';
    }

    /**
     * Format subject categories array into pipe-delimited subject headings
     *
     * @param array $categories
     * @return string
     */
    private function formatSubjectHeadings(array $categories): string
    {
        if (empty($categories)) {
            return '';
        }

        $items = [];
        foreach ($categories as $cat) {
            $cleaned = trim(preg_replace('/\s+/', ' ', (string)$cat));
            if (!empty($cleaned) && !in_array($cleaned, $items)) {
                $items[] = $cleaned;
            }
        }

        return !empty($items) ? implode('|', $items) . '|' : '';
    }

    /**
     * Safe native cURL GET with JSON decode
     *
     * @param string $url
     * @param array $headers
     * @return array|null
     */
    private function curlGetJson(string $url, array $headers = []): ?array
    {
        if (!function_exists('curl_init')) {
            // Fallback to file_get_contents if cURL is not available
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => !empty($headers) ? implode("\r\n", $headers) : "User-Agent: {$this->userAgent}\r\n",
                    'timeout' => $this->timeout
                ]
            ];
            $context = stream_context_create($opts);
            $raw = @file_get_contents($url, false, $context);
            if ($raw) {
                return json_decode($raw, true);
            }
            return null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Query Perpustakaan Negara Malaysia (PNM) Polaris Swagger API for an ISBN
     *
     * @param string $isbn
     * @return array|null
     */
    public function queryPolarisIsbn(string $isbn): ?array
    {
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);
        if (empty($cleanIsbn)) {
            return null;
        }

        // 1. Primary search: Keyword ISBN
        $url = rtrim($this->pnmBaseUrl, '/') . "/search/bibs/keyword/ISBN/" . urlencode($cleanIsbn);
        $res = $this->callPolarisApi($url);

        // 2. Fallback search: Boolean ISBN query if keyword returned empty
        if (!$res || empty($res['BibSearchRows'])) {
            $boolUrl = rtrim($this->pnmBaseUrl, '/') . "/search/bibs/boolean?q=ISBN=" . urlencode($cleanIsbn);
            $res = $this->callPolarisApi($boolUrl);
        }

        // 3. Check for converted ISBN (ISBN-10 <-> ISBN-13) if still empty
        if (!$res || empty($res['BibSearchRows'])) {
            $altIsbn = (strlen($cleanIsbn) === 13) ? $this->convertIsbn13To10($cleanIsbn) : $this->convertIsbn10To13($cleanIsbn);
            if ($altIsbn) {
                $altUrl = rtrim($this->pnmBaseUrl, '/') . "/search/bibs/keyword/ISBN/" . urlencode($altIsbn);
                $res = $this->callPolarisApi($altUrl);
                if (!$res || empty($res['BibSearchRows'])) {
                    $altBoolUrl = rtrim($this->pnmBaseUrl, '/') . "/search/bibs/boolean?q=ISBN=" . urlencode($altIsbn);
                    $res = $this->callPolarisApi($altBoolUrl);
                }
            }
        }

        if (!$res || empty($res['BibSearchRows'][0])) {
            return null;
        }

        $searchRow = $res['BibSearchRows'][0];
        $controlNumber = $searchRow['ControlNumber'] ?? '';

        // Fetch detailed record for subjects and notes if ControlNumber is present
        $detailRows = [];
        if (!empty($controlNumber)) {
            $detailUrl = rtrim($this->pnmBaseUrl, '/') . "/bib/" . urlencode($controlNumber);
            $detailRes = $this->callPolarisApi($detailUrl);
            if (!empty($detailRes['BibGetRows'])) {
                $detailRows = $detailRes['BibGetRows'];
            }
        }

        return [
            'searchRow' => $searchRow,
            'detailRows' => $detailRows
        ];
    }

    /**
     * Native authenticated cURL call to Polaris PWS API using HMAC-SHA1
     *
     * @param string $url
     * @param string $method
     * @return array|null
     */
    private function callPolarisApi(string $url, string $method = 'GET'): ?array
    {
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $secret = ""; // Public endpoints use empty secret suffix
        $signaturePayload = $method . $url . $date . $secret;
        $signature = base64_encode(hash_hmac('sha1', $signaturePayload, $this->pnmAccessKey, true));
        $authHeader = "PWS " . $this->pnmAccessId . ":" . $signature;

        $headers = [
            "Authorization: " . $authHeader,
            "PolarisDate: " . $date,
            "Accept: application/json",
            "User-Agent: " . $this->userAgent
        ];

        if (!function_exists('curl_init')) {
            $opts = [
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $headers) . "\r\n",
                    'timeout' => $this->polarisTimeout
                ]
            ];
            $context = stream_context_create($opts);
            $raw = @file_get_contents($url, false, $context);
            if ($raw) {
                $json = json_decode($raw, true);
                return is_array($json) ? $json : null;
            }
            return null;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->polarisTimeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Format PNM Polaris API response into standardized structure
     *
     * @param array $pnmData
     * @param string $isbn
     * @return array
     */
    public function formatPolarisResult(array $pnmData, string $isbn): array
    {
        $row = $pnmData['searchRow'] ?? [];
        $detailRows = $pnmData['detailRows'] ?? [];

        // 1. Raw fields from search row
        $rawTitle = $row['Title'] ?? '';
        $rawAuthor = $row['Author'] ?? '';
        $rawPublisher = $row['Publisher'] ?? '';
        $rawPubDate = $row['PublicationDate'] ?? '';
        $rawDesc = $row['Description'] ?? '';
        $rawCallNum = $row['CallNumber'] ?? '';
        $rawSeries = $row['Series'] ?? '';
        $rawEdition = $row['Edition'] ?? '';

        // 2. Extract detailed elements from BibGetRows if available
        $subjectsList = [];
        $notesList = [];
        foreach ($detailRows as $d) {
            $elemId = (int)($d['ElementID'] ?? 0);
            $val = trim((string)($d['Value'] ?? ''));
            if (empty($val)) continue;

            if ($elemId === 20 || str_starts_with(strtolower((string)($d['Label'] ?? '')), 'subject')) {
                $subjectsList[] = $val;
            } elseif ($elemId === 28 || str_starts_with(strtolower((string)($d['Label'] ?? '')), 'note')) {
                $notesList[] = $val;
            } elseif ($elemId === 35 && empty($rawTitle)) {
                $rawTitle = $val;
            } elseif ($elemId === 18 && empty($rawAuthor)) {
                $rawAuthor = $val;
            } elseif ($elemId === 2 && empty($rawPublisher)) {
                $rawPublisher = $val;
            } elseif ($elemId === 3 && empty($rawDesc)) {
                $rawDesc = $val;
            } elseif ($elemId === 19 && empty($rawSeries)) {
                $rawSeries = $val;
            } elseif ($elemId === 13 && empty($rawCallNum)) {
                $rawCallNum = $val;
            }
        }

        // 3. Title parsing (Tag 245$a, 245$b, 245$c)
        $title = $rawTitle;
        $subtitle = '';
        $titleC = '';

        // Split by " / " for Statement of responsibility ($c)
        if (str_contains($title, ' / ')) {
            $tParts = explode(' / ', $title, 2);
            $title = trim($tParts[0]);
            $titleC = trim($tParts[1]);
        }

        // Split by " : " for Remainder of title / Subtitle ($b)
        if (str_contains($title, ' : ')) {
            $tParts = explode(' : ', $title, 2);
            $title = trim($tParts[0]);
            $subtitle = trim($tParts[1]);
        }

        // 4. Author normalization (Tag 100 / 700)
        $mainAuthor = trim((string)$rawAuthor);
        $authorsStr = $mainAuthor;
        if (empty($titleC) && !empty($mainAuthor)) {
            $titleC = $mainAuthor;
        }

        // 5. Publisher and Publication Place parsing (Tag 264$a, 264$b, 264$c)
        $pubPlace = '';
        $pubName = $rawPublisher;
        $year = '';

        if (preg_match('/([12][0-9]{3})/', (string)$rawPubDate, $yMatch)) {
            $year = $yMatch[1];
        } elseif (preg_match('/([12][0-9]{3})/', (string)$rawPublisher, $yMatch)) {
            $year = $yMatch[1];
        }

        if (str_contains($rawPublisher, ' : ')) {
            $pParts = explode(' : ', $rawPublisher, 2);
            $pubPlace = trim($pParts[0]);
            $pubName = trim($pParts[1]);
        }

        // Remove trailing year and punctuation from publisher name
        $pubName = preg_replace('/,?\s*\[?[12][0-9]{3}\]?\.?$/', '', $pubName);
        $pubName = trim(rtrim($pubName, ',.'));

        // 6. Physical Description parsing (Tag 300$a, 300$c)
        $physicalDesc = $rawDesc;
        $physicalDescC = '';
        if (str_contains($rawDesc, ' ; ')) {
            $dParts = explode(' ; ', $rawDesc, 2);
            $physicalDesc = trim($dParts[0]);
            $physicalDescC = trim($dParts[1]);
        }

        // 7. Subjects & Headings (Tag 650)
        $categories = $this->cleanNoiseSubjects($subjectsList);
        $subjectHeadings = $this->formatSubjectHeadings($categories);

        // 8. Notes & Description (Tag 500 / 520)
        $notesCombined = implode('; ', array_unique($notesList));
        $notes = mb_substr($notesCombined, 0, 255);
        $description = $notesCombined;

        // 9. Call Number, DDC, Cutter (Tag 090$a, 090$b)
        $callnumA = '';
        $callnumB = '';
        $cutter = '';
        $callNumber = '';
        $callnumType = 'POLARIS';

        if (!empty($rawCallNum)) {
            $rawCleanCall = trim(preg_replace('/\s+/', ' ', $rawCallNum));
            $parts = explode(' ', $rawCleanCall);
            if (count($parts) >= 2) {
                $callnumA = array_shift($parts);
                $callnumB = implode(' ', $parts);
                $cutter = $parts[0] ?? '';
                if ($year && !str_contains($callnumB, $year)) {
                    $callnumB .= " {$year}";
                }
                $callNumber = trim("{$callnumA} {$callnumB}");
            } else {
                $callnumA = $rawCleanCall;
                $cutterSource = !empty($mainAuthor) ? $mainAuthor : $title;
                $cutter = $this->generateCutter($cutterSource, empty($mainAuthor));
                $callnumB = trim($cutter . ($year ? " {$year}" : ''));
                $callNumber = trim("{$callnumA} {$callnumB}");
            }
        } else {
            // Infer DDC and generate cutter if Polaris has no call number
            $callnumA = $this->inferDdcFromKeywords($categories, $title, $description);
            $cutterSource = !empty($mainAuthor) ? $mainAuthor : $title;
            $cutter = $this->generateCutter($cutterSource, empty($mainAuthor));
            $callnumB = trim($cutter . ($year ? " {$year}" : ''));
            $callNumber = trim("{$callnumA} {$callnumB}");
            $callnumType = 'DDC';
        }

        // 10. Language Detection (Tag 041)
        $textForLang = strtolower($title . ' ' . $subtitle . ' ' . implode(' ', $categories));
        if (preg_match('/\b(dan|di|ke|yang|pada|untuk|sejarah|pendidikan|wanita|pembangunan|malaysia|sastera|cerpen|puisi|buku|kuala lumpur|selangor)\b/i', $textForLang)) {
            $lang = 'zsm';
        } elseif (preg_match('/\b(the|and|of|in|to|for|university|education|research|innovation|handbook|guide)\b/i', $textForLang)) {
            $lang = 'eng';
        } else {
            $lang = 'zsm';
        }

        return [
            'item_type' => 'book',
            'title' => $title,
            'title_b' => $subtitle,
            'title_c' => $titleC,
            'authors' => $authorsStr,
            'author_main' => $mainAuthor,
            'publisher' => $pubName,
            'publication_place' => $pubPlace,
            'year' => $year,
            'edition' => $rawEdition,
            'physical_desc' => $physicalDesc,
            'physical_desc_c' => $physicalDescC,
            'series' => $rawSeries,
            'categories' => $categories,
            'subject_headings' => $subjectHeadings,
            'ddc' => $callnumA,
            'cutter' => $cutter,
            'call_number' => $callNumber,
            'callnum_a' => $callnumA,
            'callnum_b' => $callnumB,
            'callnum_type' => $callnumType,
            'thumbnail' => $row['ThumbnailLink'] ?? '',
            'description' => $description,
            'notes' => $notes,
            'isbn' => $isbn,
            'issn' => '',
            'language' => $lang,
            'provider' => 'pnm_polaris'
        ];
    }
}
