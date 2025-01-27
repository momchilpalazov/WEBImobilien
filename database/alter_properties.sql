-- Добавяне на колони за спецификация
ALTER TABLE properties
ADD COLUMN specification_bg TEXT AFTER description_en,
ADD COLUMN specification_de TEXT AFTER specification_bg,
ADD COLUMN specification_ru TEXT AFTER specification_de,
ADD COLUMN specification_en TEXT AFTER specification_ru;

-- Промяна на типа на колоната type
ALTER TABLE properties
MODIFY COLUMN type ENUM('industrial', 'logistics', 'office') NOT NULL; 