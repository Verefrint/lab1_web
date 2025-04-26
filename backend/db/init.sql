DROP TABLE IF EXISTS legal_services.services;

CREATE TABLE legal_services.services (
    id INT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price VARCHAR(50) NOT NULL,
    link VARCHAR(255) NOT NULL,
    keywords TEXT NOT NULL
);

INSERT INTO legal_services.services (id, title, description, price, link, keywords) VALUES
(1, 'Регистрация ООО', 'Полная юридическая регистрация Общества с Ограниченной Ответственностью', 'от 15 000 руб.', '#corporate', 'регистрация ооо создание фирмы'),
(2, 'Налоговое консультирование', 'Оптимизация налогообложения и защита от претензий налоговых органов', 'индивидуально', '#tax', 'нализы консультация оптимизация'),
(3, 'Трудовое право', 'Помощь в решении трудовых споров и оформлении кадровых документов', 'от 10 000 руб.', '#labor', 'трудовые договоры кадры');
