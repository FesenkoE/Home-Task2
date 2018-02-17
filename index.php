<?php
require_once('phar://ask.phar/src/ask.php');

/*
	API библиотеки обогатилось дополнительными функциями:
	save($user) - принимает ассоциативный массив вида ['username' => 'oleg', 'score' => 20];
				  username - имя пользователя в ЛАТИНИЦЕ (кирилицу не использовать)
				  score - очки пользователя
    find($username) - принимает строку (имя пользователя) и возвращает ассоциативный массив вида ['username' => 'oleg', 'score' => 20]
    				  или null если пользователь с таким username не найден
    findAll()		- возвращает коллекцию (многомерный массив) ВСЕХ пользователей когда-либо участвоваших в викторине.
    clear()			- очищает хранилище данных.

    Задача 2:
    Реализовать викторину похожую на первую часть домашнего задания, но теперь с большей логикой.
    Что бы участвовать в викторине, пользователь должен представиться (ввести имя пользователя на латинице). Если пользователь с таким username
    найден, следует вывести приветственное сообщение, которое проинформирует его о текущем положении очков ($user['score']).

    За правильные ответы, пользователь получает очки, эти очки фиксируются в поле $user['score']. По завершении теста, информация о достижении
    должна быть зафиксирована при помощи функции save($user); Очки можно рассчитывать по-разному, это на ваше усмотрение. Каждый вопрос может
    иметь свою сложность и по-разному насчитывать балы (очки).

    Если пользователь ввел некорректные данные, которые прерывают работу программы - функция save($user) все равно должна быть вызвана
    что бы зафиксировать текущий результат.

    Сценарий имеет массив переданных ему аргументов (http://php.net/manual/ru/reserved.variables.argv.php), используйте это:
    	- Если пользователь запускает программу с аргументом stats (php index.php stats) - то необходимо имена пользователей и их очки.
    	- Если пользователь запускает программу с аргументом reset (php index.php reset) - то необходимо сбросить хранилище данных.


	Задача 3:
	Ничто не мешает расширять информацию о пользователе. Например, так: ['username' => 'oleg', 'score' => 20, 'question' => 5]
	Таким образом, вы можете сохранить информацию о том, на каком вопросе остановился пользователь.

	Добавьте метод load {username} что бы пользователь продолжил с того вопроса, на котором он остановился. Это может быть полезно, если
	пользователь прервал выполнение программы нажатием Ctrl + C или ввел некорректно ответ на один из вопросов, что привело к завершению программы.
	Подумайте, как это можно реализовать. Пример вызова: php index.php load oleg - это приведет к тому, что пользователь oleg продолжит с того вопроса
	на котором остановился. Если же для этого пользователя викторина была окончена, он просто начнет с первого вопроса.

*/
$user = [];
$score = 0;
$questionNumber = 1;

//Массив правильных ответов

$answers[1] = "2";
$answers[2] = "1";
$answers[3] = "5";
$answers[4] = "echo";
$answers[5] = "strip_tags";

//Массив вопросов
$questions = [];
$questions[1] = "Какой из нижеперечисленных вариантов корректно объявляет константу? (выберите один вариант, указав цифру)
[1] define \"NAME\", \"John\";
[2] define (\"NAME\", 'John');
[3] define (\"NAME\"), ('John');
[4] define (\"NAME\": 'John');";
$questions[2] = "Что делает функция strlen? (выберите один вариант, указав цифру)
[1] Считает количество символов в строке
[2] Находит подстроку в строке
[3] Дели строку на подстроки по указанному символу";
$questions[3] = "Сколько типов данных в PHP начиная с версии 7.2, выберите вариант ответа
[1] 4
[2] 6
[3] 8
[4] 9
[5] 10";
$questions[4] = "Какая функция выводит отформатированную строку(напишите Ваш ответ)";
$questions[5] = "Какая функция удаляет теги HTML и PHP(напишите Ваш ответ)";

function startTest()
{
    global $answer, $username, $score, $questionNumber;
    if (strtolower(trim($answer)) === "y") {
        echo "Отлично, тогда начнем!\n";
        $user['username'] = $username;
        $user['score'] = $score;
        $user['question'] = $questionNumber;
        $user['answer'] = [];
        save($user);
    } elseif (strtolower(trim($answer)) === "n") {
        echo "Тогда в другой раз! Всего доброго!";
        die();
    } else {
        echo "Вы ничего не ответили!\n";
        $answer = ask("Вы согласны? [y\\n] \n");
        startTest();
    }
}

function newUser()
{
    global $questions, $user, $answers, $username;
    for ($i = 1; $i <= count($questions); $i++) {
        $questions[$i] = ask(<<<EOT
\nВопрос #$i из 5
$questions[$i]
Ваш вариант
EOT
        );
        $user['question']++;
        $user['answer'][$i] = $questions[$i];
        if ($answers[$i] == $questions[$i]) {
            $user['score']++;
        }
        save(['username' => $username, 'score' => $user['score'], 'question' => $user['question'],
            "answer" => $user['answer']]);

    }
}

function currentUser()
{
    global $questions, $user, $answers, $username;
    $count = $user['question'];
    for ($i = $count + 1; $i <= count($questions); $i++) {
        $questions[$i] = ask(<<<EOT
\nВопрос #$i из 5
$questions[$i]
Ваш вариант
EOT
        );
        $user['question']++;
        $user['answer'][$i] = $questions[$i];
        if ($answers[$i] == $questions[$i]) {
            $user['score']++;
        }
        save(['username' => $username, 'score' => $user['score'], 'question' => $user['question'],
            "answer" => $user['answer']]);

    }
}

function green($item)
{
    echo "\033[1;32m$item\033[0m\n";
}

function red($item)
{
    echo "\033[31m$item\033[0m\n";
}

$username = ask("Укажите своё имя на латинском языке");

if (empty($username)) {
    die("Введите данные");
} else {
    $user = find($username);
    if (($user['username']) == $username) {
        printf("Рады вас снова видеть, %s. Ваш текущий результат %s.\n", $user['username'], $user['score']);
        if ($user['question'] == 5) {
            $answer = ask("Хотите пройти еще раз? [y\\n] \n");
            if ($answer == 'y') {
                clear();
                newUser();
            } else {
                currentUser();
            }
        }
    } else {
        echo "Вы у нас впервые!\n";
        $answer = ask("Предлагаем вам поучаствовать в нашей векторине. Вы согласны? [y\\n] \n");
        startTest();
        newUser();
    }
}
print_r($user);

