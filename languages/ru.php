<?php
return [
    // Общие
    'menu' => [
        'home' => 'Главная',
        'properties' => 'Недвижимость',
        'services' => 'Услуги',
        'about' => 'О нас',
        'contact' => 'Контакты',
        'blog' => 'Блог'
    ],
    
    // Главная страница
    'home' => [
        'featured_properties' => 'Избранная недвижимость',
        'latest_properties' => 'Последние объекты',
        'search_title' => 'Поиск недвижимости',
        'view_all' => 'Смотреть все',
        'hero_text' => 'Найдите идеальную промышленную недвижимость',
        'services' => 'Наши услуги',
        'blog_posts' => 'Последние публикации',
        'latest_properties' => 'Избранная недвижимость',
        'hero_text' => 'Найдите идеальную промышленную недвижимость',
        'view_all' => 'Посмотреть все объекты',
        'featured_properties' => 'Избранные объекты'
    ],
    
    // Поиск
    'search' => [
        'title' => 'Поиск недвижимости',
        'all_types' => 'Все типы',
        'all_statuses' => 'Все статусы',
        'min_price' => 'Минимальная цена',
        'max_price' => 'Максимальная цена',
        'min_area' => 'Минимальная площадь',
        'max_area' => 'Максимальная площадь',
        'location' => 'Расположение',
        'submit' => 'Поиск',
        'clear' => 'Очистить',
        'no_results' => 'Объекты не найдены',
        'results_count' => 'Найдено объектов'
    ],
    
    // Недвижимость
    'property' => [
        'details' => 'Подробности',
        'description' => 'Описание',
        'features' => 'Характеристики',
        'location' => 'Расположение',
        'documents' => 'Документы',
        'inquiry' => 'Запрос',
        'price' => 'Цена',
        'area' => 'Площадь',
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
            'hotels' => 'Гостиницы'
        ],
        'status' => [
            'available' => 'Доступен',
            'reserved' => 'Зарезервирован',
            'rented' => 'Арендован',
            'sold' => 'Продан'
        ],
        'specifications' => 'Спецификации',
        'contact_agent' => 'Связаться с агентом',
        'share' => 'Поделиться',
        'download_docs' => 'Скачать документы',
        'similar' => 'Похожие объекты',
        'back_to_list' => 'Вернуться к списку',
        'property_id' => 'ID объекта',
        'last_update' => 'Последнее обновление',
        'features_list' => [
            'built_year' => 'Год постройки',
            'floors' => 'Этажи',
            'parking_spots' => 'Парковочные места',
            'ceiling_height' => 'Высота потолков',
            'office_space' => 'Офисная площадь',
            'storage_space' => 'Складская площадь',
            'production_space' => 'Производственная площадь',
            'heating' => 'Отопление',
            'electricity' => 'Электричество',
            'water_supply' => 'Водоснабжение',
            'security' => 'Охрана',
            'loading_docks' => 'Погрузочные доки',
            'ceiling_height_value' => '%s метров',
            'loading_docks_value' => '%d доков',
            'parking_spots_value' => '%d мест'
        ],
        'technical_details' => [
            'title' => 'Технические характеристики',
            'ceiling_height' => 'Высота потолков',
            'loading_docks' => 'Погрузочные доки',
            'parking' => 'Парковка',
            'led_lighting' => 'LED освещение',
            'fire_system' => 'Система пожаротушения',
            'security_system' => 'Круглосуточная охрана и видеонаблюдение',
            'parking_lot' => 'Парковка для грузовиков',
            'transformer' => 'Собственная трансформаторная подстанция',
            'temperature' => 'Контроль температуры'
        ],
        'inquiry_form' => [
            'title' => 'Отправить запрос',
            'subtitle' => 'Заинтересовал объект? Отправьте нам запрос, и мы свяжемся с вами в ближайшее время.',
            'name' => 'Ваше имя',
            'email' => 'Ваш email',
            'phone' => 'Ваш телефон',
            'message' => 'Ваше сообщение',
            'submit' => 'Отправить запрос',
            'success' => 'Ваш запрос успешно отправлен. Мы свяжемся с вами в ближайшее время.',
            'error' => 'При отправке запроса произошла ошибка. Пожалуйста, попробуйте еще раз.',
            'required' => 'Обязательное поле'
        ],
        'filter' => [
            'title' => 'Фильтры',
            'type' => 'Тип недвижимости',
            'status' => 'Статус',
            'area' => 'Площадь',
            'price' => 'Цена',
            'min' => 'Мин',
            'max' => 'Макс',
            'apply' => 'Применить фильтры',
            'clear' => 'Сбросить фильтры'
        ],
        'sort' => [
            'title' => 'Сортировка',
            'date_desc' => 'Сначала новые',
            'date_asc' => 'Сначала старые',
            'price_asc' => 'По возрастанию цены',
            'price_desc' => 'По убыванию цены',
            'area_asc' => 'По возрастанию площади',
            'area_desc' => 'По убыванию площади'
        ],
        'showing_results' => 'Показано %d из %d объектов',
        'no_results' => 'Объекты не найдены',
        'pagination' => [
            'previous' => 'Предыдущая',
            'next' => 'Следующая',
            'page' => 'Страница'
        ]
    ],
    
    // Формы
    'form' => [
        'name' => 'Имя',
        'email' => 'Email',
        'phone' => 'Телефон',
        'message' => 'Сообщение',
        'submit' => 'Отправить',
        'search' => 'Поиск',
        'required' => 'Обязательное поле'
    ],
    
    // Сообщения
    'messages' => [
        'inquiry_sent' => 'Ваш запрос успешно отправлен',
        'error' => 'Произошла ошибка. Пожалуйста, попробуйте снова'
    ],
    
    // За нас
    'about' => [
        'title' => 'О нас',
        'our_mission' => 'Наша миссия',
        'mission_text' => 'Industrial Properties является ведущей компанией в сфере промышленной недвижимости с более чем 15-летним опытом работы. Наша миссия - предоставлять первоклассные промышленные площади и профессиональные услуги нашим клиентам, помогая им успешно развивать свой бизнес.',
        'our_services' => 'Наши услуги',
        'service_1' => 'Аренда складских и логистических помещений',
        'service_2' => 'Продажа промышленной недвижимости и земельных участков',
        'service_3' => 'Консультации по выбору локации и развитию проектов',
        'why_choose_us' => 'Почему выбирают нас',
        'why_choose_text' => 'Мы отличаемся индивидуальным подходом к каждому клиенту, глубоким знанием рынка и богатым портфолио качественной недвижимости. Наша команда экспертов всегда готова проконсультировать вас и помочь в выборе наиболее подходящего объекта для вашего бизнеса.',
        'contact_us' => 'Свяжитесь с нами',
        'contact_text' => 'У вас есть вопросы или нужна дополнительная информация? Наша команда к вашим услугам.',
        'contact_button' => 'Контакты'
    ],
    
    // Контакты
    'contact' => [
        'title' => 'Контакты',
        'description' => 'Свяжитесь с нами для получения дополнительной информации о наших объектах и услугах. Наша команда к вашим услугам.',
        'address_title' => 'Адрес',
        'address' => 'Bulgaria Blvd. 102, Sofia 1680, Болгария',
        'email_title' => 'Эл. почта',
        'phone_title' => 'Телефон',
        'office_hours' => 'Часы работы',
        'monday_friday' => 'Понедельник - Пятница',
        'saturday' => 'Суббота',
        'sunday' => 'Воскресенье',
        'closed' => 'Закрыто',
        'social_media' => 'Социальные сети',
        'phone' => 'Телефон',
        'phone_number' => '+359 888 123 456',
        'email' => 'Эл. почта',
        'email_address' => 'contact@example.com'
    ],
    
    // Footer
    'footer' => [
        'company_name' => 'Industrial Properties',
        'description' => 'Ваш надежный партнер в сфере промышленной недвижимости.',
        'all_rights_reserved' => 'Все права защищены.',
        'quick_links' => 'Быстрые ссылки',
        'property_types' => 'Типы недвижимости',
        'contact_info' => 'Контактная информация',
        'social_media' => 'Социальные сети',
        'follow_us' => 'Подписывайтесь на нас'
    ],
    
    // Недвижимость
    'properties' => [
        'type' => 'Тип недвижимости',
        'status' => 'Статус',
        'area' => 'Площадь',
        'price' => 'Цена',
        'sort' => 'Сортировать по',
        'min' => 'Мин',
        'max' => 'Макс',
        'apply' => 'Применить',
        'clear' => 'Очистить',
        'all' => 'Все',
        'available' => 'Доступно',
        'rented' => 'В аренде',
        'sold' => 'Продано',
        'newest' => 'Сначала новые',
        'oldest' => 'Сначала старые',
        'area_asc' => 'Площадь (возр.)',
        'area_desc' => 'Площадь (убыв.)',
        'showing' => 'Показано %d из %d объектов',
        'no_properties' => 'Объекты не найдены',
        'view_details' => 'Подробнее',
        'previous' => 'Назад',
        'next' => 'Вперед'
    ],
    
    // Описание недвижимости
    'property_descriptions' => [
        'manufacturing' => 'Современные производственные помещения, подходящие для различных отраслей. Включает здания для легкой и тяжелой промышленности с возможностью адаптации под потребности.',
        'logistics' => 'Стратегически расположенные логистические центры с отличной транспортной доступностью. Оснащены современными системами хранения и обработки грузов.',
        'office' => 'Современные офисные помещения в промышленных зонах. Включает отдельно стоящие офисные здания и комбинированные пространства с производственными помещениями.',
        'logistics_park' => 'Масштабные логистические комплексы с полной инфраструктурой. Предлагают различные складские и дистрибьюторские решения под одной крышей.',
        'specialized' => 'Объекты специального назначения, включая холодильные склады, чистые помещения и специализированные производственные объекты.',
        'logistics_terminal' => 'Мультимодальные логистические терминалы с доступом к различным видам транспорта. Включает железнодорожные терминалы, контейнерные терминалы и распределительные центры.',
        'land' => 'Участки под промышленное строительство со всеми необходимыми коммуникациями. Подходят для строительства производственных и логистических объектов.',
        'food_industry' => 'Специализированные помещения для пищевой промышленности. Соответствуют всем гигиеническим требованиям и стандартам безопасности.',
        'heavy_industry' => 'Промышленные комплексы для тяжелой промышленности. Включает объекты для металлургии, машиностроения и обрабатывающей промышленности.',
        'tech_industry' => 'Высокотехнологичные производственные базы и центры обработки данных. Оснащены современной инфраструктурой для технологических компаний.',
        'hotels' => 'Гостиничные объекты в промышленных и деловых зонах. Идеально подходят для размещения бизнес-гостей и долгосрочных корпоративных клиентов.'
    ],
    
    // Услуги
    'services' => [
        'title' => 'Наши услуги',
        'consulting' => [
            'title' => 'Консультации по недвижимости',
            'description' => 'Мы предоставляем профессиональные консультации по промышленной недвижимости, включая анализ рынка, оценку местоположения и юридические консультации.'
        ],
        'valuation' => [
            'title' => 'Оценка недвижимости',
            'description' => 'Мы проводим детальную оценку промышленной недвижимости на основе рыночных данных, местоположения, состояния и потенциала развития.'
        ],
        'management' => [
            'title' => 'Управление недвижимостью',
            'description' => 'Комплексное управление промышленной недвижимостью, включая техническое обслуживание, арендные отношения и оптимизацию затрат.'
        ],
        'investment' => [
            'title' => 'Инвестиционные услуги',
            'description' => 'Консультации по инвестиционным возможностям, анализ доходности и стратегическое планирование инвестиций в промышленную недвижимость.'
        ],
        'legal' => [
            'title' => 'Юридические услуги',
            'description' => 'Мы предоставляем юридические услуги для приобретения недвижимости иностранцами, включая поддержку в открытии компании в Болгарии и все необходимые шаги.'
        ],
        'recruitment' => [
            'title' => 'Подбор персонала',
            'description' => 'Помощь в наборе и отборе персонала согласно требованиям заказчика, соответствующего намерениям инвестора.'
        ],
        'languages' => [
            'title' => 'Языки',
            'description' => 'Мы говорим на трех языках: русском, немецком и английском, чтобы обеспечить наилучший сервис для наших клиентов.'
        ]
    ],
    
    // Контактный текст и кнопка
    'contact_text' => 'Если вас интересует эта услуга, свяжитесь с нами:',
    'contact_button' => 'Свяжитесь с нами',
    
    // Блог переводы
    'blog' => [
        'title' => 'Блог',
        'categories' => [
            'all' => 'Все',
            'industry_articles' => 'Статьи о промышленной недвижимости',
            'sector_news' => 'Новости отрасли',
            'investor_tips' => 'Советы инвесторам'
        ],
        'read_more' => 'Читать далее',
        'views' => 'просмотров',
        'share' => 'Поделиться',
        'no_posts' => 'Публикации не найдены.',
        'published_on' => 'Опубликовано',
        'author' => 'Автор',
        'category' => 'Категория',
        'latest_posts' => 'Последние публикации',
        'popular_posts' => 'Популярные публикации'
    ]
];
