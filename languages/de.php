<?php
return [
    // Allgemein
    'menu' => [
        'home' => 'Startseite',
        'properties' => 'Immobilien',
        'services' => 'Dienstleistungen',
        'about' => 'Über uns',
        'contact' => 'Kontakt',
        'blog' => 'Blog'
    ],
    
    // Startseite
    'home' => [
        'featured_properties' => 'Ausgewählte Immobilien',
        'latest_properties' => 'Neueste Immobilien',
        'search_title' => 'Immobiliensuche',
        'view_all' => 'Alle anzeigen',
        'hero_text' => 'Finden Sie Ihre perfekte Gewerbeimmobilie',
        'services' => 'Unsere Dienstleistungen',
        'blog_posts' => 'Neueste Beiträge',
        'latest_properties' => 'Ausgewählte Immobilien',
        'hero_text' => 'Finden Sie die perfekte Industrieimmobilie',
        'view_all' => 'Alle Immobilien anzeigen',
        'featured_properties' => 'Ausgewählte Immobilien'
    ],
    
    // Suche
    'search' => [
        'title' => 'Immobiliensuche',
        'all_types' => 'Alle Typen',
        'all_statuses' => 'Alle Status',
        'min_price' => 'Mindestpreis',
        'max_price' => 'Maximaler Preis',
        'min_area' => 'Mindestfläche',
        'max_area' => 'Maximale Fläche',
        'location' => 'Standort',
        'submit' => 'Suchen',
        'clear' => 'Zurücksetzen',
        'no_results' => 'Keine Immobilien gefunden',
        'results_count' => 'Gefundene Immobilien',
        'min_area' => 'Min. Fläche',
        'max_area' => 'Max. Fläche',
        'min_price' => 'Min. Preis',
        'max_price' => 'Max. Preis'
    ],
    
    // Immobilien
    'property' => [
        'details' => 'Details',
        'description' => 'Beschreibung',
        'features' => 'Eigenschaften',
        'location' => 'Standort',
        'documents' => 'Dokumente',
        'inquiry' => 'Anfrage',
        'price' => 'Preis',
        'area' => 'Fläche',
        'type' => [
            'manufacturing' => 'Produktionsgebäude',
            'logistics' => 'Logistikzentren',
            'office' => 'Bürogebäude',
            'logistics_park' => 'Logistikparks',
            'specialized' => 'Spezialimmobilien',
            'logistics_terminal' => 'Logistikterminals',
            'land' => 'Bauland',
            'food_industry' => 'Lebensmittelindustrie',
            'heavy_industry' => 'Schwerindustrie',
            'tech_industry' => 'Technologieindustrie',
            'hotels' => 'Hotels'
        ],
        'status' => [
            'available' => 'Verfügbar',
            'reserved' => 'Reserviert',
            'rented' => 'Vermietet',
            'sold' => 'Verkauft'
        ],
        'specifications' => 'Spezifikationen',
        'contact_agent' => 'Makler kontaktieren',
        'share' => 'Teilen',
        'download_docs' => 'Dokumente herunterladen',
        'similar' => 'Ähnliche Immobilien',
        'back_to_list' => 'Zurück zur Liste',
        'property_id' => 'Immobilien-ID',
        'last_update' => 'Letzte Aktualisierung',
        'features_list' => [
            'built_year' => 'Baujahr',
            'floors' => 'Etagen',
            'parking_spots' => 'Parkplätze',
            'ceiling_height' => 'Deckenhöhe',
            'office_space' => 'Bürofläche',
            'storage_space' => 'Lagerfläche',
            'production_space' => 'Produktionsfläche',
            'heating' => 'Heizung',
            'electricity' => 'Stromversorgung',
            'water_supply' => 'Wasserversorgung',
            'security' => 'Sicherheit',
            'loading_docks' => 'Laderampen',
            'ceiling_height_value' => '%s Meter',
            'loading_docks_value' => '%d Laderampen',
            'parking_spots_value' => '%d Parkplätze'
        ],
        'technical_details' => [
            'title' => 'Technische Details',
            'ceiling_height' => 'Deckenhöhe',
            'loading_docks' => 'Laderampen',
            'parking' => 'Parken',
            'led_lighting' => 'LED-Beleuchtung',
            'fire_system' => 'Brandschutzsystem',
            'security_system' => '24/7 Sicherheit und Videoüberwachung',
            'parking_lot' => 'LKW-Parkplatz',
            'transformer' => 'Eigene Transformatorstation',
            'temperature' => 'Temperaturkontrolle'
        ],
        'inquiry_form' => [
            'title' => 'Anfrage senden',
            'subtitle' => 'Interessiert an dieser Immobilie? Senden Sie uns eine Anfrage und wir melden uns in Kürze bei Ihnen.',
            'name' => 'Ihr Name',
            'email' => 'Ihre E-Mail',
            'phone' => 'Ihre Telefonnummer',
            'message' => 'Ihre Nachricht',
            'submit' => 'Anfrage senden',
            'success' => 'Ihre Anfrage wurde erfolgreich gesendet. Wir werden uns in Kürze bei Ihnen melden.',
            'error' => 'Beim Senden Ihrer Anfrage ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.',
            'required' => 'Pflichtfeld'
        ],
        'filter' => [
            'title' => 'Filter',
            'type' => 'Immobilientyp',
            'status' => 'Status',
            'area' => 'Fläche',
            'price' => 'Preis',
            'min' => 'Min',
            'max' => 'Max',
            'apply' => 'Filter anwenden',
            'clear' => 'Filter zurücksetzen'
        ],
        'sort' => [
            'title' => 'Sortierung',
            'date_desc' => 'Neueste zuerst',
            'date_asc' => 'Älteste zuerst',
            'price_asc' => 'Preis aufsteigend',
            'price_desc' => 'Preis absteigend',
            'area_asc' => 'Fläche aufsteigend',
            'area_desc' => 'Fläche absteigend'
        ],
        'showing_results' => '%d von %d Immobilien',
        'no_results' => 'Keine Immobilien gefunden',
        'pagination' => [
            'previous' => 'Vorherige',
            'next' => 'Nächste',
            'page' => 'Seite'
        ]
    ],
    
    // Formulare
    'form' => [
        'name' => 'Name',
        'email' => 'E-Mail',
        'phone' => 'Telefon',
        'message' => 'Nachricht',
        'submit' => 'Senden',
        'search' => 'Suchen',
        'required' => 'Pflichtfeld'
    ],
    
    // Nachrichten
    'messages' => [
        'inquiry_sent' => 'Ihre Anfrage wurde erfolgreich gesendet',
        'error' => 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut'
    ],
    
    // Über uns
    'about' => [
        'title' => 'Über uns',
        'our_mission' => 'Unsere Mission',
        'mission_text' => 'Industrial Properties ist ein führendes Unternehmen im Bereich Industrieimmobilien mit über 15 Jahren Erfahrung. Unsere Mission ist es, erstklassige Industrieflächen und professionelle Dienstleistungen für unsere Kunden bereitzustellen und ihnen zu helfen, ihr Geschäft erfolgreich zu entwickeln.',
        'our_services' => 'Unsere Dienstleistungen',
        'service_1' => 'Vermietung von Lager- und Logistikflächen',
        'service_2' => 'Verkauf von Industrieimmobilien und Grundstücken',
        'service_3' => 'Beratung bei Standortwahl und Projektentwicklung',
        'why_choose_us' => 'Warum uns wählen',
        'why_choose_text' => 'Wir zeichnen uns durch einen individuellen Ansatz für jeden Kunden, fundierte Marktkenntnisse und ein umfangreiches Portfolio hochwertiger Immobilien aus. Unser Expertenteam steht Ihnen jederzeit zur Verfügung, um Sie bei der Auswahl der am besten geeigneten Immobilie für Ihr Unternehmen zu beraten und zu unterstützen.',
        'contact_us' => 'Kontaktieren Sie uns',
        'contact_text' => 'Haben Sie Fragen oder benötigen Sie weitere Informationen? Unser Team steht Ihnen zur Verfügung.',
        'contact_button' => 'Kontakt'
    ],
    
    // Kontakt
    'contact' => [
        'title' => 'Kontakt',
        'description' => 'Kontaktieren Sie uns für weitere Informationen zu unseren Immobilien und Dienstleistungen. Unser Team steht Ihnen zur Verfügung.',
        'address_title' => 'Adresse',
        'address' => 'Bulgaria Blvd. 102, Sofia 1680, Bulgarien',
        'email_title' => 'E-Mail',
        'phone_title' => 'Telefon',
        'office_hours' => 'Öffnungszeiten',
        'monday_friday' => 'Montag - Freitag',
        'saturday' => 'Samstag',
        'sunday' => 'Sonntag',
        'closed' => 'Geschlossen',
        'social_media' => 'Soziale Medien',
        'phone' => 'Telefon',
        'phone_number' => '+359 888 123 456',
        'email' => 'E-Mail',
        'email_address' => 'contact@example.com'
    ],
    
    // Footer
    'footer' => [
        'description' => 'Ihr zuverlässiger Partner für Industrieimmobilien',
        'quick_links' => 'Schnellzugriff',
        'contact_info' => 'Kontaktinformationen',
        'all_rights_reserved' => 'Alle Rechte vorbehalten',
        'property_types' => 'Immobilientypen',
        'company_name' => 'Industrial Properties',
        'follow_us' => 'Folgen Sie uns'
    ],
    
    // Immobilien
    'properties' => [
        'type' => 'Immobilientyp',
        'status' => 'Status',
        'area' => 'Fläche',
        'price' => 'Preis',
        'sort' => 'Sortieren nach',
        'min' => 'Min',
        'max' => 'Max',
        'apply' => 'Anwenden',
        'clear' => 'Zurücksetzen',
        'all' => 'Alle',
        'available' => 'Verfügbar',
        'rented' => 'Vermietet',
        'sold' => 'Verkauft',
        'newest' => 'Neueste zuerst',
        'oldest' => 'Älteste zuerst',
        'area_asc' => 'Fläche (aufsteigend)',
        'area_desc' => 'Fläche (absteigend)',
        'showing' => 'Zeige %d von %d Immobilien',
        'no_properties' => 'Keine Immobilien gefunden',
        'view_details' => 'Details anzeigen',
        'previous' => 'Zurück',
        'next' => 'Weiter'
    ],
    
    // Immobilienbeschreibungen
    'property_descriptions' => [
        'manufacturing' => 'Moderne Produktionsanlagen für verschiedene Branchen. Umfasst Gebäude für leichte und schwere Produktion mit Anpassungsmöglichkeiten nach Bedarf.',
        'logistics' => 'Strategisch günstig gelegene Logistikzentren mit hervorragender Anbindung. Ausgestattet mit modernen Lager- und Warenwirtschaftssystemen.',
        'office' => 'Zeitgemäße Büroflächen in Industriegebieten. Umfasst freistehende Bürogebäude und kombinierte Räume mit Produktionsanlagen.',
        'logistics_park' => 'Großflächige Logistikkomplexe mit vollständiger Infrastruktur. Bietet verschiedene Lager- und Vertriebslösungen unter einem Dach.',
        'specialized' => 'Immobilien für spezielle Zwecke, einschließlich Kühlhäuser, Reinräume und spezialisierte Produktionsanlagen.',
        'logistics_terminal' => 'Multimodale Logistikterminals mit Zugang zu verschiedenen Verkehrsträgern. Umfasst Bahnterminals, Containerterminals und Verteilzentren.',
        'land' => 'Industriebaugrundstücke mit allen notwendigen Versorgungseinrichtungen. Geeignet für den Bau von Produktions- und Logistikanlagen.',
        'food_industry' => 'Spezialisierte Einrichtungen für die Lebensmittelindustrie. Erfüllt alle Hygieneanforderungen und Sicherheitsstandards.',
        'heavy_industry' => 'Industriekomplexe für die Schwerindustrie. Umfasst Anlagen für Metallurgie, Maschinenbau und verarbeitende Industrie.',
        'tech_industry' => 'Hightech-Produktionsstandorte und Rechenzentren. Ausgestattet mit modernster Infrastruktur für Technologieunternehmen.',
        'hotels' => 'Hotelimmobilien in Industrie- und Geschäftszonen. Ideal für Geschäftsreisende und langfristige Firmenkunden.'
    ],
    
    // Dienstleistungen
    'services' => [
        'title' => 'Unsere Dienstleistungen',
        'consulting' => [
            'title' => 'Immobilienberatung',
            'description' => 'Wir bieten professionelle Beratung für Industrieimmobilien, einschließlich Marktanalyse, Standortbewertung und rechtliche Beratung.'
        ],
        'valuation' => [
            'title' => 'Immobilienbewertung',
            'description' => 'Wir führen detaillierte Bewertungen von Industrieimmobilien durch, basierend auf Marktdaten, Standort, Zustand und Entwicklungspotenzial.'
        ],
        'management' => [
            'title' => 'Immobilienverwaltung',
            'description' => 'Umfassende Verwaltung von Industrieimmobilien, einschließlich Instandhaltung, Mietverhältnisse und Kostenoptimierung.'
        ],
        'investment' => [
            'title' => 'Investmentservices',
            'description' => 'Beratung zu Investitionsmöglichkeiten, Renditeanalyse und strategische Planung von Investitionen in Industrieimmobilien.'
        ],
        'legal' => [
            'title' => 'Rechtsdienstleistungen',
            'description' => 'Wir bieten Rechtsdienstleistungen für den Immobilienerwerb durch Ausländer an, einschließlich Unterstützung bei der Firmengründung in Bulgarien und allen erforderlichen Schritten.'
        ],
        'recruitment' => [
            'title' => 'Personalrekrutierung',
            'description' => 'Unterstützung bei der Rekrutierung und Auswahl von Personal gemäß den Anforderungen des Auftraggebers, die den Absichten des Investors entsprechen.'
        ],
        'languages' => [
            'title' => 'Sprachen',
            'description' => 'Wir sprechen drei Sprachen: Russisch, Deutsch und Englisch, um den besten Service für unsere Kunden zu gewährleisten.'
        ]
    ],
    
    // Контактен текст и бутон
    'contact_text' => 'Wenn Sie an diesem Service interessiert sind, kontaktieren Sie uns:',
    'contact_button' => 'Kontaktieren Sie uns',

    // Blog translations
    'blog' => [
        'title' => 'Blog',
        'categories' => [
            'all' => 'Alle',
            'industry_articles' => 'Artikel über Industrieimmobilien',
            'sector_news' => 'Branchennachrichten',
            'investor_tips' => 'Investorentipps'
        ],
        'read_more' => 'Weiterlesen',
        'views' => 'Aufrufe',
        'share' => 'Teilen',
        'no_posts' => 'Keine Beiträge gefunden.',
        'published_on' => 'Veröffentlicht am',
        'author' => 'Autor',
        'category' => 'Kategorie',
        'latest_posts' => 'Neueste Beiträge',
        'popular_posts' => 'Beliebte Beiträge'
    ]
];