-- Изключване на проверката за чужди ключове
SET FOREIGN_KEY_CHECKS = 0;

-- Обновяване на описанията за складови имоти
UPDATE properties 
SET 
    description_bg = '<p>Модерен складов имот с отлична локация и достъп. Характеристики:</p><ul><li>Височина на тавана: 12 метра</li><li>Товарни рампи: 8 бр.</li><li>LED осветление</li><li>Противопожарна система</li><li>24/7 охрана и видеонаблюдение</li><li>Паркинг за тежкотоварни автомобили</li></ul><p>Имотът разполага със собствена трафопостна станция и възможност за температурен контрол.</p>',
    description_de = '<p>Moderne Lagerfläche mit ausgezeichneter Lage und Zugang. Eigenschaften:</p><ul><li>Deckenhöhe: 12 Meter</li><li>Laderampen: 8 Stück</li><li>LED-Beleuchtung</li><li>Brandschutzsystem</li><li>24/7 Sicherheit und Videoüberwachung</li><li>LKW-Parkplatz</li></ul><p>Die Immobilie verfügt über eine eigene Transformatorstation und Temperaturkontrolle.</p>',
    description_ru = '<p>Современный складской объект с отличным расположением и доступом. Характеристики:</p><ul><li>Высота потолка: 12 метров</li><li>Погрузочные рампы: 8 шт.</li><li>LED освещение</li><li>Противопожарная система</li><li>Круглосуточная охрана и видеонаблюдение</li><li>Парковка для грузовых автомобилей</li></ul><p>Объект имеет собственную трансформаторную подстанцию и возможность температурного контроля.</p>',
    description_en = '<p>Modern warehouse property with excellent location and access. Features:</p><ul><li>Ceiling height: 12 meters</li><li>Loading docks: 8 pcs</li><li>LED lighting</li><li>Fire protection system</li><li>24/7 security and video surveillance</li><li>Heavy vehicle parking</li></ul><p>The property has its own transformer station and temperature control capability.</p>'
WHERE type = 'warehouse';

-- Обновяване на описанията за логистични имоти
UPDATE properties 
SET 
    description_bg = '<p>Първокласен логистичен център с стратегическо местоположение. Особености:</p><ul><li>Директен достъп до магистрала</li><li>Cross-dock съоръжения</li><li>Автоматизирана складова система</li><li>Митническо складиране</li><li>Модерна WMS система</li><li>Офис помещения</li></ul><p>Имотът е проектиран за максимална ефективност на логистичните операции.</p>',
    description_de = '<p>Erstklassiges Logistikzentrum in strategischer Lage. Besonderheiten:</p><ul><li>Direkter Autobahnzugang</li><li>Cross-Dock-Einrichtungen</li><li>Automatisiertes Lagersystem</li><li>Zolllagerung</li><li>Modernes WMS-System</li><li>Büroräume</li></ul><p>Die Immobilie wurde für maximale Effizienz der Logistikoperationen konzipiert.</p>',
    description_ru = '<p>Первоклассный логистический центр со стратегическим расположением. Особенности:</p><ul><li>Прямой доступ к автомагистрали</li><li>Cross-dock сооружения</li><li>Автоматизированная складская система</li><li>Таможенное хранение</li><li>Современная WMS система</li><li>Офисные помещения</li></ul><p>Объект спроектирован для максимальной эффективности логистических операций.</p>',
    description_en = '<p>First-class logistics center with strategic location. Features:</p><ul><li>Direct highway access</li><li>Cross-dock facilities</li><li>Automated warehouse system</li><li>Customs warehousing</li><li>Modern WMS system</li><li>Office spaces</li></ul><p>The property is designed for maximum efficiency of logistics operations.</p>'
WHERE type = 'logistics';

-- Обновяване на описанията за индустриални имоти
UPDATE properties 
SET 
    description_bg = '<p>Първокласен индустриален комплекс с всички необходими удобства. Характеристики:</p><ul><li>Производствени помещения</li><li>Складови площи</li><li>Офис пространства</li><li>Собствен паркинг</li><li>Модерни комуникации</li><li>Възможност за разширение</li></ul><p>Стратегическа локация с отлична транспортна достъпност.</p>',
    description_de = '<p>Erstklassiger Industriekomplex mit allen notwendigen Annehmlichkeiten. Eigenschaften:</p><ul><li>Produktionsräume</li><li>Lagerflächen</li><li>Büroflächen</li><li>Eigener Parkplatz</li><li>Moderne Kommunikation</li><li>Erweiterungsmöglichkeit</li></ul><p>Strategische Lage mit ausgezeichneter Verkehrsanbindung.</p>',
    description_ru = '<p>Первоклассный промышленный комплекс со всеми необходимыми удобствами. Характеристики:</p><ul><li>Производственные помещения</li><li>Складские площади</li><li>Офисные помещения</li><li>Собственная парковка</li><li>Современные коммуникации</li><li>Возможность расширения</li></ul><p>Стратегическое расположение с отличной транспортной доступностью.</p>',
    description_en = '<p>First-class industrial complex with all necessary amenities. Features:</p><ul><li>Production facilities</li><li>Storage areas</li><li>Office spaces</li><li>Private parking</li><li>Modern communications</li><li>Expansion possibility</li></ul><p>Strategic location with excellent transport accessibility.</p>'
WHERE type = 'industrial';

-- Обновяване на описанията за производствени имоти
UPDATE properties 
SET 
    description_bg = '<p>Съвременен производствен комплекс с отлична инфраструктура. Предлага:</p><ul><li>Производствени помещения</li><li>Складови площи</li><li>Офис части</li><li>Мостови кранове</li><li>Индустриални врати</li><li>Вентилационна система</li></ul><p>Имотът е оборудван според най-високите индустриални стандарти.</p>',
    description_de = '<p>Moderner Produktionskomplex mit ausgezeichneter Infrastruktur. Bietet:</p><ul><li>Produktionsräume</li><li>Lagerflächen</li><li>Bürobereiche</li><li>Brückenkräne</li><li>Industrietore</li><li>Lüftungssystem</li></ul><p>Die Immobilie ist nach höchsten Industriestandards ausgestattet.</p>',
    description_ru = '<p>Современный производственный комплекс с отличной инфраструктурой. Предлагает:</p><ul><li>Производственные помещения</li><li>Складские площади</li><li>Офисные части</li><li>Мостовые краны</li><li>Промышленные ворота</li><li>Вентиляционная система</li></ul><p>Объект оборудован по высочайшим промышленным стандартам.</p>',
    description_en = '<p>Modern manufacturing complex with excellent infrastructure. Offers:</p><ul><li>Production facilities</li><li>Storage areas</li><li>Office sections</li><li>Bridge cranes</li><li>Industrial gates</li><li>Ventilation system</li></ul><p>The property is equipped to the highest industrial standards.</p>'
WHERE type = 'manufacturing';

-- Включване на проверката за чужди ключове
SET FOREIGN_KEY_CHECKS = 1; 