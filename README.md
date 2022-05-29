# service

## Оглавление

[1.Описание](#description "Описание") <br>
[2.Документация](#doc "Документация") <br>
[3.Поднятие проекта](#projectUp "Поднятие проекта") <br>
[4.консольные комманды](#console "консольные команды") <br>
[5.Доступы](#access "Стек") <br>
[6.Задача](#task "Задача") <br>

<a name="description"></a>
### Описание
REST API  приложение для отслеживания даты изменения цен

<a name="doc"></a>
### Документация

[1.Поиск товара](#search) 


<a name="search"></a>
#### Создание автора
* **Описание:** Поиск определённого товара  <br>
  **Метод:** GET <br>
  **Адрес:**
    ```sh 
  http://service.local/item/★ StatTrak™ M9 Bayonet | Rust Coat (Well-Worn)?gte=2022-05-29&lte=2022-05-29
    ```


  **Ответ:**
  > `{
  "status": "success",
  "data": {
  "★ StatTrak™ M9 Bayonet | Rust Coat (Well-Worn)": [
  {
  "date": "22-05-29",
  "price": 489.87
  }
  ]
  }
  }`





<a name="projectUp"></a>
### Поднятие проекта
#### Linux/mac
* Скачать репозиторий в домашнюю папку
* в файле etc/hosts добавить local
  ```sh
    127.0.0.1 service.local
  ```

* перейти в service/deployment выполнить
  ```sh
  docker-compose build
  ```
  ```sh
  docker-compose up -d
  ```
* Зайти в котейнер
  ```sh
  docker exec -it dev_service_1 bash
  ```
* Сменить пользователя
  ```sh
  su apps
  ```
* перейти
  ```sh
  cd ~/service/
  ```
* Выполнить команды
  ```sh
  composer install
  ```

<a name="console"></a>
### Консольные комманды
* **Описание:** Создание/Обновление цен товаров   <br>
* Зайти в котейнер
  ```sh
   docker exec -it deployment_service_1 bash
  ```
* Сменить пользователя
  ```sh
  su apps
  ```
* Перейти
  ```sh
  cd ~/service/
  ```
* Выполнить команды
  ```sh
  cd php bin/console user:updatePrice
  ```



<a name="stack"></a>
### Стек
* Symfony 5.4.6
* php7.4
* Библиотеки
    * "php": ">=7.2.5",
    *  "ext-ctype": "*",
    *  "ext-iconv": "*",
    *  "ext-json": "*",
    *  "elasticsearch/elasticsearch": "^8.2",
    *  "symfony/console": "5.4.*",
    *   "symfony/dotenv": "5.4.*",
    *  "symfony/filesystem": "5.4.*",
    *  "symfony/flex": "^1.17|^2",
    *  "symfony/framework-bundle": "5.4.*",
    *  "symfony/http-client": "5.4.*",
    *  "symfony/monolog-bundle": "^3.8",
    *  "symfony/runtime": "5.4.*",
    *  "symfony/yaml": "5.4.*"

<a name="access"></a>
### Доступы
Elastic <br>
* host
  ```sh
  172.17.0.1
  ```
* port
  ```sh
  9200
  ```
* login
  ```sh
  null
  ```
* password
  ```sh
  null
  ```
* index
  ```sh
  item
  ```

<a name="task"></a>
### Задача
Сторонний сервис предоставляет актуальные цены на игровые предметы
https://service.com/v2/items/show/csgo?token={TOKEN_VALUE}&offset=0 <br>
Токен: 228 <br>
Сервис возвращает информацию постранично, навигация выполняется за счет передачи параметра offset (смещение)

Необходимо реализовать
⁃ CLI-команду загрузки и обновления данных из itemsccservice
⁃ API представляющее данные о ценах на один из игровых предметов (steam_market_hash_name) в указанном интервале времени (date_from и date_to)
```
