-- Добавяне на нови колони
ALTER TABLE properties 
    ADD COLUMN specification_en TEXT AFTER specification_ru,
    ADD COLUMN description_en TEXT AFTER description_ru; 