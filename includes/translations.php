<?php
// Инициализация на масива с преводи
$translations = [];

// Български преводи
$translations['bg'] = [
    'menu' => [
        'services' => 'Услуги',
        'properties' => 'Имоти',
        'blog' => 'Блог',
        'about' => 'За нас',
        'contact' => 'Контакти'
    ],
    'home' => [
        'services' => 'Нашите услуги',
        'blog_posts' => 'Последни публикации',
        'latest_properties' => 'Избрани имоти',
        'hero_text' => 'Намерете перфектния индустриален имот',
        'view_all' => 'Вижте всички имоти',
        'featured_properties' => 'Избрани имоти'
    ],
    'search' => [
        'title' => 'Търсене на имоти',
        'all_types' => 'Всички типове',
        'min_area' => 'Минимална площ',
        'max_area' => 'Максимална площ',
        'min_price' => 'Минимална цена',
        'max_price' => 'Максимална цена',
        'submit' => 'Търси'
    ],
    'services' => [
        'title' => 'Нашите Услуги',
        'consulting' => [
            'title' => 'Консултации за имоти',
            'description' => 'Предлагаме професионални консултации за индустриални имоти, включително пазарен анализ, оценка на локацията и правни съвети.'
        ],
        'valuation' => [
            'title' => 'Оценка на имоти',
            'description' => 'Извършваме детайлни оценки на индустриални имоти на база пазарни данни, локация, състояние и потенциал за развитие.'
        ],
        'management' => [
            'title' => 'Управление на имоти',
            'description' => 'Цялостно управление на индустриални имоти, включително поддръжка, наемни отношения и оптимизация на разходите.'
        ],
        'investment' => [
            'title' => 'Инвестиционни услуги',
            'description' => 'Консултации относно инвестиционни възможности, анализ на възвръщаемостта и стратегическо планиране на инвестиции в индустриални имоти.'
        ],
        'legal' => [
            'title' => 'Юридически услуги',
            'description' => 'Предлагаме юридически услуги за закупуване на имоти от чужденци, включително подкрепа за отваряне на фирма в България и всички необходими стъпки.'
        ],
        'recruitment' => [
            'title' => 'Подбор на персонал',
            'description' => 'Съдействие за набиране и подбор на персонал по изисквания на възложителя, отговарящ на намеренията на инвеститора.'
        ],
        'languages' => [
            'title' => 'Езици',
            'description' => 'Разговаряме на три езика: руски, немски и английски, за да осигурим най-доброто обслужване на нашите клиенти.'
        ],
        'blog' => [
            'title' => [
                'bg' => 'Блог',
                'en' => 'Blog',
                'de' => 'Blog',
                'ru' => 'Блог'
            ],
            'categories' => [
                'industry_articles' => [
                    'bg' => 'Статии за индустриални имоти',
                    'en' => 'Industrial Property Articles',
                    'de' => 'Artikel über Industrieimmobilien',
                    'ru' => 'Статьи о промышленной недвижимости'
                ],
                'sector_news' => [
                    'bg' => 'Новини от сектора',
                    'en' => 'Sector News',
                    'de' => 'Branchennachrichten',
                    'ru' => 'Новости сектора'
                ],
                'investor_tips' => [
                    'bg' => 'Съвети за инвеститори',
                    'en' => 'Investor Tips',
                    'de' => 'Investorentipps',
                    'ru' => 'Советы инвесторам'
                ],
                'reports' => [
                    'bg' => 'Доклади',
                    'en' => 'Reports',
                    'de' => 'Berichte',
                    'ru' => 'Отчеты'
                ],
                'podcast' => [
                    'bg' => 'Подкаст',
                    'en' => 'Podcast',
                    'de' => 'Podcast',
                    'ru' => 'Подкаст'
                ],
                'markets' => [
                    'bg' => 'Пазари',
                    'en' => 'Markets',
                    'de' => 'Märkte',
                    'ru' => 'Рынки'
                ],
                'success_stories' => [
                    'bg' => 'Успешни истории',
                    'en' => 'Success Stories',
                    'de' => 'Erfolgsgeschichten',
                    'ru' => 'Истории успеха'
                ]
            ],
            'no_posts' => [
                'bg' => 'Няма намерени публикации',
                'en' => 'No posts found',
                'de' => 'Keine Beiträge gefunden',
                'ru' => 'Публикации не найдены'
            ],
            'read_more' => [
                'bg' => 'Прочети повече',
                'en' => 'Read more',
                'de' => 'Weiterlesen',
                'ru' => 'Читать далее'
            ],
            'views' => [
                'bg' => 'преглеждания',
                'en' => 'views',
                'de' => 'Aufrufe',
                'ru' => 'просмотров'
            ]
        ]
    ],
    'contact_text' => 'Ако имате интерес към тази услуга, свържете се с нас:',
    'contact_button' => 'Свържете се с нас',
    'property' => [
        'status' => [
            'available' => 'Свободен',
            'reserved' => 'Резервиран',
            'rented' => 'Отдаден',
            'sold' => 'Продаден'
        ],
        'type' => [
            'manufacturing' => 'Производствени сгради',
            'logistics' => 'Логистични центрове',
            'office' => 'Офис сгради',
            'logistics_park' => 'Логистични паркове',
            'specialized' => 'Специализирани имоти',
            'logistics_terminal' => 'Логистични терминали',
            'land' => 'Земя за строеж',
            'food_industry' => 'Хранителна индустрия',
            'heavy_industry' => 'Тежка индустрия',
            'tech_industry' => 'Технологични индустрии',
            'hotels' => 'Хотели'
        ]
    ],
    'footer' => [
        'company_name' => 'Industrial Properties',
        'description' => [
            'bg' => 'Вашият надежден партньор в сферата на индустриалните имоти.',
            'en' => 'Your reliable partner in industrial properties.',
            'de' => 'Ihr zuverlässiger Partner für Industrieimmobilien.',
            'ru' => 'Ваш надежный партнер в сфере промышленной недвижимости.'
        ],
        'all_rights_reserved' => [
            'bg' => 'Всички права запазени.',
            'en' => 'All rights reserved.',
            'de' => 'Alle Rechte vorbehalten.',
            'ru' => 'Все права защищены.'
        ],
        'quick_links' => [
            'bg' => 'Бързи връзки',
            'en' => 'Quick Links',
            'de' => 'Schnelllinks',
            'ru' => 'Быстрые ссылки'
        ],
        'property_types' => 'Видове имоти',
        'contact_info' => 'Контакти',
        'social_media' => 'Социални мрежи',
        'follow_us' => [
            'bg' => 'Последвайте ни',
            'en' => 'Follow us',
            'de' => 'Folgen Sie uns',
            'ru' => 'Подписывайтесь на нас'
        ]
    ],
    'contact' => [
        'title' => 'Контакти',
        'address' => 'Bulgaria Blvd. 102, Sofia 1680, България',
        'phone' => 'Телефон',
        'phone_number' => '+359 888 123 456',
        'email' => 'Имейл',
        'email_address' => 'contact@example.com'
    ],
    'about' => [
        'title' => [
            'bg' => 'За нас',
            'en' => 'About Us',
            'de' => 'Über uns',
            'ru' => 'О нас'
        ],
        'mission_title' => [
            'bg' => 'Нашата мисия',
            'en' => 'Our Mission',
            'de' => 'Unsere Mission',
            'ru' => 'Наша миссия'
        ],
        'mission_text' => [
            'bg' => 'Industrial Properties е водеща компания в сферата на индустриалните имоти, специализирана в предоставянето на професионални услуги за бизнес клиенти. Нашата мисия е да бъдем надежден партньор в развитието на вашия бизнес, предоставяйки експертни решения за индустриални имоти.',
            'en' => 'Industrial Properties is a leading company in the industrial real estate sector, specializing in providing professional services to business clients. Our mission is to be a reliable partner in the development of your business, providing expert solutions for industrial properties.',
            'de' => 'Industrial Properties ist ein führendes Unternehmen im Bereich Industrieimmobilien, das sich auf professionelle Dienstleistungen für Geschäftskunden spezialisiert hat. Unsere Mission ist es, ein zuverlässiger Partner bei der Entwicklung Ihres Unternehmens zu sein und Expertenlösungen für Industrieimmobilien anzubieten.',
            'ru' => 'Industrial Properties - ведущая компания в сфере промышленной недвижимости, специализирующаяся на предоставлении профессиональных услуг бизнес-клиентам. Наша миссия - быть надежным партнером в развитии вашего бизнеса, предоставляя экспертные решения в области промышленной недвижимости.'
        ],
        'services_title' => [
            'bg' => 'Нашите услуги',
            'en' => 'Our Services',
            'de' => 'Unsere Dienstleistungen',
            'ru' => 'Наши услуги'
        ],
        'service_1_title' => [
            'bg' => 'Отдаване под наем',
            'en' => 'Leasing',
            'de' => 'Vermietung',
            'ru' => 'Аренда'
        ],
        'service_1_text' => [
            'bg' => 'Предлагаме широка гама от индустриални имоти под наем, съобразени с вашите нужди.',
            'en' => 'We offer a wide range of industrial properties for lease, tailored to your needs.',
            'de' => 'Wir bieten eine breite Palette von Industrieimmobilien zur Miete an, die auf Ihre Bedürfnisse zugeschnitten sind.',
            'ru' => 'Мы предлагаем широкий спектр промышленной недвижимости в аренду, адаптированной под ваши потребности.'
        ],
        'service_2_title' => [
            'bg' => 'Продажби',
            'en' => 'Sales',
            'de' => 'Verkauf',
            'ru' => 'Продажи'
        ],
        'service_2_text' => [
            'bg' => 'Професионално консултиране при покупка на индустриални имоти.',
            'en' => 'Professional consulting for industrial property purchases.',
            'de' => 'Professionelle Beratung beim Kauf von Industrieimmobilien.',
            'ru' => 'Профессиональные консультации при покупке промышленной недвижимости.'
        ],
        'service_3_title' => [
            'bg' => 'Консултации',
            'en' => 'Consulting',
            'de' => 'Beratung',
            'ru' => 'Консультации'
        ],
        'service_3_text' => [
            'bg' => 'Експертни съвети за оптимизиране на вашите индустриални площи.',
            'en' => 'Expert advice for optimizing your industrial spaces.',
            'de' => 'Expertenberatung zur Optimierung Ihrer Industrieflächen.',
            'ru' => 'Экспертные советы по оптимизации ваших промышленных площадей.'
        ],
        'why_us_title' => [
            'bg' => 'Защо да изберете нас',
            'en' => 'Why Choose Us',
            'de' => 'Warum uns wählen',
            'ru' => 'Почему выбирают нас'
        ],
        'why_us_text' => [
            'bg' => 'С дългогодишен опит в сферата на индустриалните имоти, ние предлагаме индивидуален подход към всеки клиент и гарантираме професионално обслужване на всеки етап от сделката.',
            'en' => 'With years of experience in industrial real estate, we offer an individual approach to each client and guarantee professional service at every stage of the deal.',
            'de' => 'Mit langjähriger Erfahrung im Bereich Industrieimmobilien bieten wir einen individuellen Ansatz für jeden Kunden und garantieren professionellen Service in jeder Phase der Transaktion.',
            'ru' => 'С многолетним опытом в сфере промышленной недвижимости, мы предлагаем индивидуальный подход к каждому клиенту и гарантируем профессиональное обслуживание на каждом этапе сделки.'
        ],
        'contact_title' => [
            'bg' => 'Свържете се с нас',
            'en' => 'Contact Us',
            'de' => 'Kontaktieren Sie uns',
            'ru' => 'Свяжитесь с нами'
        ],
        'contact_text' => [
            'bg' => 'Готови сме да отговорим на всички ваши въпроси и да ви помогнем да намерите най-подходящото решение за вашия бизнес.',
            'en' => 'We are ready to answer all your questions and help you find the most suitable solution for your business.',
            'de' => 'Wir sind bereit, alle Ihre Fragen zu beantworten und Ihnen zu helfen, die beste Lösung für Ihr Unternehmen zu finden.',
            'ru' => 'Мы готовы ответить на все ваши вопросы и помочь найти наиболее подходящее решение для вашего бизнеса.'
        ],
        'contact_button' => [
            'bg' => 'Свържете се с нас',
            'en' => 'Contact Us',
            'de' => 'Kontaktieren Sie uns',
            'ru' => 'Свяжитесь с нами'
        ]
    ]
];

// Български преводи за статуси на имоти
$translations['bg']['property']['status'] = [
    'available' => 'Свободен',
    'reserved' => 'Резервиран',
    'rented' => 'Отдаден',
    'sold' => 'Продаден'
];

// Немски преводи
$translations['de'] = [
    'menu' => [
        'services' => 'Dienstleistungen',
        'properties' => 'Immobilien',
        'blog' => 'Blog',
        'about' => 'Über uns',
        'contact' => 'Kontakt'
    ],
    'home' => [
        'services' => 'Unsere Dienstleistungen',
        'blog_posts' => 'Neueste Beiträge',
        'latest_properties' => 'Ausgewählte Immobilien',
        'hero_text' => 'Finden Sie die perfekte Industrieimmobilie',
        'view_all' => 'Alle Immobilien anzeigen',
        'featured_properties' => 'Ausgewählte Immobilien'
    ],
    'search' => [
        'title' => 'Immobiliensuche',
        'all_types' => 'Alle Typen',
        'min_area' => 'Min. Fläche',
        'max_area' => 'Max. Fläche',
        'min_price' => 'Min. Preis',
        'max_price' => 'Max. Preis',
        'submit' => 'Suchen'
    ],
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
    'contact_text' => 'Wenn Sie an diesem Service interessiert sind, kontaktieren Sie uns:',
    'contact_button' => 'Kontaktieren Sie uns',
    'property' => [
        'status' => [
            'available' => 'Verfügbar',
            'reserved' => 'Reserviert',
            'rented' => 'Vermietet',
            'sold' => 'Verkauft'
        ],
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
        ]
    ],
    'footer' => [
        'company_name' => 'Industrial Properties',
        'description' => 'Ihr zuverlässiger Partner für Industrieimmobilien. Wir bieten professionelle Dienstleistungen für den Kauf, Verkauf und die Verwaltung von Industrieimmobilien.',
        'all_rights_reserved' => 'Alle Rechte vorbehalten.',
        'quick_links' => 'Schnellzugriff',
        'property_types' => 'Immobilientypen',
        'contact_info' => 'Kontaktinformationen',
        'social_media' => 'Soziale Medien',
        'follow_us' => 'Folgen Sie uns'
    ],
    'contact' => [
        'title' => 'Kontakt',
        'address' => 'Bulgaria Blvd. 102, Sofia 1680, Bulgarien',
        'phone' => 'Telefon',
        'phone_number' => '+359 888 123 456',
        'email' => 'E-Mail',
        'email_address' => 'contact@example.com'
    ]
];

// Руски преводи
$translations['ru'] = [
    'menu' => [
        'services' => 'Услуги',
        'properties' => 'Недвижимость',
        'blog' => 'Блог',
        'about' => 'О нас',
        'contact' => 'Контакты'
    ],
    'home' => [
        'services' => 'Наши услуги',
        'blog_posts' => 'Последние публикации',
        'latest_properties' => 'Избранная недвижимость',
        'hero_text' => 'Найдите идеальную промышленную недвижимость',
        'view_all' => 'Посмотреть все объекты',
        'featured_properties' => 'Избранные объекты'
    ],
    'search' => [
        'title' => 'Поиск недвижимости',
        'all_types' => 'Все типы',
        'min_area' => 'Мин. площадь',
        'max_area' => 'Макс. площадь',
        'min_price' => 'Мин. цена',
        'max_price' => 'Макс. цена',
        'submit' => 'Поиск'
    ],
    'footer' => [
        'company_name' => 'Industrial Properties',
        'description' => 'Ваш надежный партнер в сфере промышленной недвижимости. Мы предлагаем профессиональные услуги по покупке, продаже и управлению промышленной недвижимостью.',
        'all_rights_reserved' => 'Все права защищены.',
        'quick_links' => 'Быстрые ссылки',
        'property_types' => 'Типы недвижимости',
        'contact_info' => 'Контактная информация',
        'social_media' => 'Социальные сети',
        'follow_us' => 'Подписывайтесь на нас'
    ],
    'contact' => [
        'title' => 'Контакты',
        'address' => 'Bulgaria Blvd. 102, Sofia 1680, Болгария',
        'phone' => 'Телефон',
        'phone_number' => '+359 888 123 456',
        'email' => 'Эл. почта',
        'email_address' => 'contact@example.com'
    ],
    'services' => [
        'title' => 'Наши услуги',
        'consulting' => [
            'title' => 'Консультации по недвижимости',
            'description' => 'Мы предлагаем профессиональные консультации по промышленной недвижимости, включая анализ рынка, оценку местоположения и юридические консультации.'
        ],
        'valuation' => [
            'title' => 'Оценка недвижимости',
            'description' => 'Мы проводим детальную оценку промышленной недвижимости на основе рыночных данных, местоположения, состояния и потенциала развития.'
        ],
        'management' => [
            'title' => 'Управление недвижимостью',
            'description' => 'Комплексное управление промышленной недвижимостью, включая обслуживание, арендные отношения и оптимизацию затрат.'
        ],
        'investment' => [
            'title' => 'Инвестиционные услуги',
            'description' => 'Консультации по инвестиционным возможностям, анализ доходности и стратегическое планирование инвестиций в промышленную недвижимость.'
        ],
        'legal' => [
            'title' => 'Юридические услуги',
            'description' => 'Мы предлагаем юридические услуги по приобретению недвижимости иностранцами, включая поддержку при регистрации компании в Болгарии и все необходимые шаги.'
        ],
        'recruitment' => [
            'title' => 'Подбор персонала',
            'description' => 'Содействие в наборе и отборе персонала согласно требованиям заказчика, соответствующего намерениям инвестора.'
        ],
        'languages' => [
            'title' => 'Языки',
            'description' => 'Мы говорим на трех языках: русском, немецком и английском, чтобы обеспечить наилучшее обслуживание наших клиентов.'
        ]
    ],
    'contact_text' => 'Если вас интересует эта услуга, свяжитесь с нами:',
    'contact_button' => 'Свяжитесь с нами',
    'property' => [
        'status' => [
            'available' => 'Доступен',
            'reserved' => 'Зарезервирован',
            'rented' => 'Арендован',
            'sold' => 'Продан'
        ],
        'type' => [
            'manufacturing' => 'Производственные здания',
            'logistics' => 'Логистические центры',
            'office' => 'Офисные здания',
            'logistics_park' => 'Логистические парки',
            'specialized' => 'Специализированные объекты',
            'logistics_terminal' => 'Логистические терминалы',
            'land' => 'Земля под застройку',
            'food_industry' => 'Пищевая промышленность',
            'heavy_industry' => 'Тяжелая промышленность',
            'tech_industry' => 'Технологическая индустрия',
            'hotels' => 'Отели'
        ]
    ]
];

// Английски преводи
$translations['en'] = [
    'menu' => [
        'services' => 'Services',
        'properties' => 'Properties',
        'blog' => 'Blog',
        'about' => 'About',
        'contact' => 'Contact'
    ],
    'home' => [
        'services' => 'Our Services',
        'blog_posts' => 'Latest Posts',
        'latest_properties' => 'Featured Properties',
        'hero_text' => 'Find Your Perfect Industrial Property',
        'view_all' => 'View All Properties',
        'featured_properties' => 'Featured Properties'
    ],
    'search' => [
        'title' => 'Property Search',
        'all_types' => 'All Types',
        'min_area' => 'Min Area',
        'max_area' => 'Max Area',
        'min_price' => 'Min Price',
        'max_price' => 'Max Price',
        'submit' => 'Search'
    ],
    'footer' => [
        'company_name' => 'Industrial Properties',
        'description' => 'Your reliable partner in industrial real estate. We offer professional services for buying, selling and managing industrial properties.',
        'all_rights_reserved' => 'All rights reserved.',
        'quick_links' => 'Quick Links',
        'property_types' => 'Property Types',
        'contact_info' => 'Contact Info',
        'social_media' => 'Social Media',
        'follow_us' => 'Follow Us'
    ],
    'contact' => [
        'title' => 'Contact',
        'address' => 'Bulgaria Blvd. 102, Sofia 1680, Bulgaria',
        'phone' => 'Phone',
        'phone_number' => '+359 888 123 456',
        'email' => 'Email',
        'email_address' => 'contact@example.com'
    ],
    'services' => [
        'title' => 'Our Services',
        'consulting' => [
            'title' => 'Real Estate Consulting',
            'description' => 'We offer professional consulting for industrial properties, including market analysis, location assessment, and legal advice.'
        ],
        'valuation' => [
            'title' => 'Property Valuation',
            'description' => 'We conduct detailed valuations of industrial properties based on market data, location, condition, and development potential.'
        ],
        'management' => [
            'title' => 'Property Management',
            'description' => 'Comprehensive management of industrial properties, including maintenance, tenant relations, and cost optimization.'
        ],
        'investment' => [
            'title' => 'Investment Services',
            'description' => 'Consulting on investment opportunities, return analysis, and strategic planning of investments in industrial properties.'
        ],
        'legal' => [
            'title' => 'Legal Services',
            'description' => 'We offer legal services for property acquisition by foreigners, including support for company registration in Bulgaria and all necessary steps.'
        ],
        'recruitment' => [
            'title' => 'Recruitment Services',
            'description' => 'Assistance in recruiting and selecting personnel according to client requirements, meeting investor intentions.'
        ],
        'languages' => [
            'title' => 'Languages',
            'description' => 'We speak three languages: Russian, German, and English, to ensure the best service for our clients.'
        ]
    ],
    'contact_text' => 'If you are interested in this service, contact us:',
    'contact_button' => 'Contact us',
    'property' => [
        'status' => [
            'available' => 'Available',
            'reserved' => 'Reserved',
            'rented' => 'Rented',
            'sold' => 'Sold'
        ],
        'type' => [
            'manufacturing' => 'Manufacturing Buildings',
            'logistics' => 'Logistics Centers',
            'office' => 'Office Buildings',
            'logistics_park' => 'Logistics Parks',
            'specialized' => 'Specialized Properties',
            'logistics_terminal' => 'Logistics Terminals',
            'land' => 'Land for Construction',
            'food_industry' => 'Food Industry',
            'heavy_industry' => 'Heavy Industry',
            'tech_industry' => 'Technology Industry',
            'hotels' => 'Hotels'
        ]
    ]
];

// Преводи за страницата с имоти
$translations['properties'] = [
    'show_map' => [
        'bg' => 'Покажи на картата',
        'en' => 'Show on Map',
        'de' => 'Auf Karte anzeigen',
        'ru' => 'Показать на карте'
    ],
    'map_title' => [
        'bg' => 'Локации на имотите',
        'en' => 'Property Locations',
        'de' => 'Immobilienstandorte',
        'ru' => 'Расположение объектов'
    ],
    'clear_filters' => [
        'bg' => 'Изчисти филтрите',
        'en' => 'Clear Filters',
        'de' => 'Filter zurücksetzen',
        'ru' => 'Сбросить фильтры'
    ],
    'filter_by_type' => [
        'bg' => 'Филтрирай по тип',
        'en' => 'Filter by Type',
        'de' => 'Nach Typ filtern',
        'ru' => 'Фильтр по типу'
    ],
    'price_range' => [
        'bg' => 'Ценови диапазон',
        'en' => 'Price Range',
        'de' => 'Preisbereich',
        'ru' => 'Ценовой диапазон'
    ],
    'area_range' => [
        'bg' => 'Площ',
        'en' => 'Area',
        'de' => 'Fläche',
        'ru' => 'Площадь'
    ],
    'from' => [
        'bg' => 'от',
        'en' => 'from',
        'de' => 'von',
        'ru' => 'от'
    ],
    'to' => [
        'bg' => 'до',
        'en' => 'to',
        'de' => 'bis',
        'ru' => 'до'
    ],
    'apply_filters' => [
        'bg' => 'Приложи филтрите',
        'en' => 'Apply Filters',
        'de' => 'Filter anwenden',
        'ru' => 'Применить фильтры'
    ],
    'no_properties_found' => [
        'bg' => 'Няма намерени имоти',
        'en' => 'No properties found',
        'de' => 'Keine Immobilien gefunden',
        'ru' => 'Объекты не найдены'
    ],
    'view_details' => [
        'bg' => 'Виж детайли',
        'en' => 'View Details',
        'de' => 'Details ansehen',
        'ru' => 'Подробнее'
    ]
];

// Преводи за пагинацията
$translations['pagination'] = [
    'previous' => [
        'bg' => 'Предишна',
        'en' => 'Previous',
        'de' => 'Vorherige',
        'ru' => 'Предыдущая'
    ],
    'next' => [
        'bg' => 'Следваща',
        'en' => 'Next',
        'de' => 'Nächste',
        'ru' => 'Следующая'
    ]
];

// Дебъг информация
error_log("translations.php loaded. Available languages: " . implode(', ', array_keys($translations)));
?>

<!-- Translations loaded. Available languages: " . implode(', ', array_keys($translations)) . " -->"; 
