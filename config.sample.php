<?php
/**
 * CES - Cron Exec System
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright (c) 2013, TpyMaH (Vadim Buchinsky) <vadim.buchinsky@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Надо ли выводить все действия в syslog. TRUE|FALSE
 */
define('MA_DEBUG', false);

// ==================================================
// =                    Почта                       =
// ==================================================
// Ниже указанные настройки используются в MA_Model_Mail

// Устанавливает почтовые адреса на которые отправлять простые отчеты.
// Может быть как строкой так и массивом.
// Например:
//      $sysMail['to'] = 'test@test.com';
//      или
//      $sysMail['to'] = array('test@test.com', 'test2@test.com);
$sysMail['to'] = 'test@test.com';

// Устанавливает почтовый адрес от чьего имени отправлять простые отчеты.
// Может быть только строкой.
// Например:
//      $sysMail['from'] = 'test@test.com';
$sysMail['from'] = 'test@test.com';

// Устанавливает тему сообщения для простого отчета.
// Может быть только строкой.
// Например:
//      $sysMail['subject'] = 'test report';
$sysMail['subject'] = 'CES notice';

// Устанавливает почтовые адреса на которые отправлять копии
// простых отчетов в случае неудачного выполнения любой команды
// в задачах. 
// Может быть как строкой так и массивом.
// Например:
//      $sysMail['error_to'] = 'test@test.com';
//      $sysMail['error_to'] = array('test@test.com', 'test2@test.com);
$sysMail['error_to'] = 'test@test.com';

// Устанавливает почтовый адрес от чьего имени отправлять копии
// простых отчетов в случае неудачного выполнения любой команды
// в задачах. 
// Может быть только строкой.
// Например:
//      $sysMail['error_from'] = 'test@test.com';
$sysMail['error_from'] = 'test@test.com';

// Устанавливает тему сообщения для копии простого отчета
// в случае неудачного выполнения любой команды в задачах. 
// Может быть только строкой.
// Например:
//      $sysMail['error_subject'] = 'test error report';
$sysMail['error_subject'] = 'CES error notice';

// ==================================================
// =                      SMS                       =
// ==================================================
// Ниже указанные настройки используются в MA_Model_Sms
//
// Чтобы данная служба работала на сервере рассылки надо открыть доступ для IP 
// сервера на котором расположена текущая копия CES!
// В нашем случае ip надо прописать суда - \web\intranet.elkor.lv\sms\config.php
//
// SMS отсылаются только если произошла ошибка в задаче.

// Устанавливает флаг активности SMS оповещения.
// Может быть TRUE|FALSE
$sysSms['enabled'] = FALSE;

// Устанавливает хост сервера на котором расположена служба SMS рассылки.
// Может быть только строкой.
// Например:
//      $sysSms['serverHost'] = 'test.com';
$sysSms['serverHost'] = 'test.com';

// Устанавливает полный путь до API службы SMS рассылки.
// Может быть только строкой.
// Например:
//      $sysSms['sendPage'] = 'http://tes.com/sms/send.php';
$sysSms['sendPage'] = 'http://tes.com/sms/send.php';

// Устанавливает ID задачи, которой будет назначены отправляемые сообщения.
// Может быть строкой или целым числом.
// Например:
//      $sysSms['taskId'] = '10';
$sysSms['taskId'] = '10';

// Устанавливает номера кому отправлять sms отчеты.
// Номера должны состоять из 11 символов.
// Может быть как строкой так и массивом.
// Например:
//      $sysSms['number'] = '37122222222';
//      или
//      $sysSms['number'] = array('37122222222', '37122222222', '37122222222','37122222222');
$sysSms['number'] = array('37122222222');

// ==================================================
// =                      EXEC                      =
// ==================================================
// Настройки команд.
// Для каждой команды используется свой подмассив.
// 
// $sysExec[<command>][<param>] = <value>;
//      <command> - имя команды
//      <param> - параметр который необходимо настроить
//      <value> - значение параметра.
//
// Параметры доступные всем командам:
//      path - путь до исполняемого файла. Имя файла также необходимо указывать.

$sysExec['mysqldump']['path'] = '/opt/mysql/bin/mysqldump';

// ==================================================
// =            Инструкция выполнения               =
// ==================================================
//  Архитектука:
//  Стек->Задачи->Команды
//
// ==================================================
// ===================== СТЕК =======================
// Стек - Совокупность задач которые будут выполнены в текущем процессе.
// По умолчанию вызывается стек с ключем - 'default'.
// В случае если вызываемого стека нету, то процесс прекращается в сообщением в syslog.
// Выбрать запускаемый стек можно передав первым параметром имя ключ желаемого стека.
//
// Например:
//      "php -f run.php default"
//
// =============== Настройка стека: =================
// $sysTaskStac[<key>][config][<param>] = <value>;
//      <key> - ключ стека. (имя стека)
//      <param> - Параметр который хотите настроить.
//      <value> - значение параметра.
//      
// ======= Доступные параметры и их значения: =======
// notice - уровень mail оповещения.
// Значения:
//      0 - включены все оповещения в задач.
//      1 - отключить простые оповещения о задачах.
//      2 - отключить Mail оповещение об ошибках в задачах. (SMS тоже отключает)
//      3 - отключить SMS оповещение об ошибках в задачах. 
//      4 - отключить все оповещение в задачах.
// Значение по умолчанию - 0
// 
// noticeconf - тонкая настройка оповещения.
// $sysTaskStac['default']['config']['noticeconf'] = array(
//     'repeat' => (true|false), по умолчанию true. Отправлять ли все отчеты об ошибках или отправлять только первый. остальные просто считать.
//     'resetinterval' => (int), по умолчанию 2. Через сколько часов скидывать счетчик для отправки сообщений об ошибках.
//     'smsperday' => (int), по умолчанию 5. Сколько sms в день можно отправлять
// );
// 
// subject - задать новый subject
// $sysTaskStac['default']['config']['subject'] = "новый subject"
// 
// error_subject - задать новый error_subject
// $sysTaskStac['default']['config']['error_subject'] = "новый subject"
// 
// additional_mails - дополнительный список эмайлов оповещения
// $sysTaskStac['default']['config']['additional_mails'] = array();
//
// additional_error_mails - дополнительный список эмайлов оповещения ошибок
// $sysTaskStac['default']['config']['additional_error_mails'] = array();
//
// ignore_default_mails - игнорировать ли основной список, возможно только если заданы дополнительные списки.
// $sysTaskStac['default']['config']['ignore_default_mails'] = true|false;
//
// ==================================================
// ==================== Задача ======================
// Задача - Совокупность команд. Которая необходима для последовательного выполнения определённой цели.
// 
// ================ Создание задачи: ================
// $sysTaskStac = array();
//      $sysTask = array();
//      $tmpTask = array();
//          $tmpTask['info']['name'] = 'name';
//          ...
//          ...
//          $sysTask[] = $tmpTask;
//          unset($tmpTask);
//      $sysTaskStac['default'] = $sysTask;
//      $sysTaskStac['default']['config']['notice'] = 0;
//      unset($sysTask);
//
// ======== Обязательные свойства задачи: ===========
// $tmpTask['info']['name'] - название задачи.
//
// =============== Настройка задачи: ================
// $tmpTask['config'][<param>] = <value>
//      <param> - Параметр который хотите настроить.
//      <value> - значение параметра.
//
// ======= Доступные параметры и их значения: =======
// scheduler - выполнения заданий в определённые дни.
//  Данный параметр является не обязательным, в случае его отсутствия задача будет
//  выполнятся каждый вызов стека к которому относится.
//  Значение это массив:
//      monthly - перечень месяцев когда выполнять
//      daily - перечень дней месяца когда выполнять
//      weekly - перечень дней недели когда выполнять
//  Разрешено совмещая вышеуказанные настройки, значениями элементов может быть int|array_of_int
//  Пример:
//      $tmpTask['config']['scheduler'] = array(
//          'monthly' => array(1,2,3),
//          'daily' => array(1,2,3,4,5,6,7,8,9,10),
//          'weekly' => array(0,1,2,3,4,5)
//      );
//      Наглядно: задача будет выполнятся с 1 по 10 число, с 1 по 3 месяц, каждый день недели кроме воскресенья!
//
// ==================================================
// =================== Команда ======================
// !Команды выполняются последовательно, поэтому если возникает ошибка в выполнение текущей команды
// последующие команды выполнены не будут. Если команды которые игнорируют ошибки, соответственно
// выполнение задачи будет продолжено!
// 
// =============== Создание команды: ================
// $tmpTask['command'][] = array(<command>, <param>, <param>, ...);
//      <command> - имя вызываемой команды.
//      <param> - параметры. Для каждой команды существует свой набор параметров. Также они могут вообще отсутствовать.
//
// ==================================================
// =               Перечень команд                  =
// ==================================================
//
// cp - предназначенная для копирования файлов из одного в другие каталоги. Исходный файл остаётся неизменным,
//  имя созданного файла может быть таким же, как у исходного, или измениться.
//  $tmpTask['command'][] = array('cp', <SOURCE>, <DIRECTORY>, <OPTION>, <COMMENT>, <HIDE>);
//      <SOURCE> - что.
//      <DIRECTORY> - куда.
//      <OPTION> - (необязательный параметр) флаги, запрещено использовать именные флаги, только буквенные.
//          знак минус ставить не надо. Использование флагом меняющих порядок <SOURCE> и <DIRECTORY>
//          при выполнение в exec остаётся на вашей совести.
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//  Команда не проверяет существуют ли <SOURCE> и <DIRECTORY> физически на сервере,
//  соответственно если их нету, то скрипт выдаст ошибку в соответствии с командой exec.
//  
//  
// mv - используется для перемещения или переименования файлов. 
//  $tmpTask['command'][] = array('cp', <SOURCE>, <DIRECTORY>, <OPTION>, <COMMENT>, <HIDE>);
//      <SOURCE> - что.
//      <DIRECTORY> - куда.
//      <OPTION> - (необязательный параметр) флаги, запрещено использовать именные флаги, только буквенные.
//          знак минус ставить не надо. Использование флагом меняющий порядок порядок <SOURCE> и <DIRECTORY>
//          при выполнение в exec остаётся на вашей совести.
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//  Команда не проверяет существуют ли <SOURCE> и <DIRECTORY> физически на сервере,
//  соответственно если их нету, то скрипт выдаст ошибку в соответствии с командой exec.
//
//
// rm - используемая для удаления файлов.
//  $tmpTask['command'][] = array('cp', <SOURCE>, <OPTION>, <COMMENT>, <HIDE>);
//      <SOURCE> - что.
//      <OPTION> - (необязательный параметр) флаги, запрещено использовать именные флаги, только буквенные.
//          знак минус ставить не надо.
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//
//
// tar - работы с tar архивами, только создание!
//  $tmpTask['command'][] = array('tar', <SOURCE>, <DIRECTORY>, <OPTION>, <COMMENT>, <USERCOMMAND>, <IGNORE ERROR>, <HIDE>);
//      <SOURCE> - файл куда будет создан архив.
//      <DIRECTORY> - директория которую необходимо заархивировать.
//      <OPTION> - (необязательный параметр) флаги, запрещено использовать именные флаги, только буквенные.
//          разрешенные флаги - czjplf
//          флаги по умолчанию (ставятся автоматически)- cplf
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <USERCOMMAND> - TRUE - отключить автогенерацию комманды. В этом случае в параметр <OPTION> будет воспринят как полный надор аргументов. то есть:
//              $tmpTask['command'][] = array('tar', '/home/web/test.gz', '/home/web/default/', 'pczlf', 'test', FALSE); - вернёт следующую комманду:
//                  cd /home/web/default/ && tar -pczlf /home/web/test.gz .
//              $tmpTask['command'][] = array('tar', '', '/home/web/default/', '--one-file-system -pczlf /home/web/t.gz .', 'test', TRUE); - вернёт следующую комманду:
//                  cd /home/web/default/ && tar --one-file-system -pczlf /home/web/t.gz .
//      <IGNORE ERROR> - TRUE - тключить проверку ошибки, FALSE - включить проверку.
//      <HIDE> - true|false скрыть из отчета.
//
//
// killall
//  команда не возвращает ошибок!
//  $tmpTask['command'][] = array('killall', <NAME>, <OPTION>, <COMMENT>, <HIDE>);
//      <NAME> - имя процесса
//      <OPTION> - передаваемые параметры, передаются как есть! знак "-" не подставляется.
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//
// mysqldump
//  !на большинстве серверов требуется указать - $sysExec['mysqldump']['path']!
//  данная команда сразу сжимает дамп в gzip.
//  $tmpTask['command'][] = array('mysqldump', <DATABASE>, <SOURCE>, <OPTION>, <COMMENT>, <HIDE>);
//      <DATABASE> - перечень баз которые надо сдампить разделённые пробелом. можно оставить пустым значением, но тогда обязателен флаг "a"
//      <SOURCE> - куда будет создан дамп.
//      <OPTION> - параметры, передаётся как есть!
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//
//
// exec - выполнить произвольную команду.
//  $tmpTask['command'][] = array('exec', <COMMAND>, <RETURN>, <COMMENT>, <HIDE>, <COMMAND>);
//      <COMMAND> - произвольная команда.
//      <RETURN> - реагировать ли на ошибки. (TRUE|FALSE)
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//      <COMMAND> - массив с командами для выполнения. Пример:
/*
'command' => array(
    array(
        'exec',
        'lsa -la',//COMMAND
        true,//RETURN
        'test',//COMMENT
        false,//HIDE
        'command' => array(//COMMAND
            'success' => 'консольная команда.',
            'error'   =>'консольная команда.'
        )
    ),
)
*/
//
//
// bz2 - работы с bz2 архивами, только создание!
//  $tmpTask['command'][] = array('bz2', <SOURCE>, <FILE>, <COMMENT>, <HIDE>);
//      <SOURCE> - файл куда будет создан архив.
//      <FILE> - файл который необходимо заархивировать.
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//
//
// df - занято места в %
//  $tmpTask['command'][] = array('df', <SOURCE>, <LIMIT>, <COMMENT>, <HIDE>);
//      <SOURCE> - папка чей размер проверить.
//      <LIMIT> - (необязательный параметр) уровень после которого вызвать ошибку. пример '60' эквивалентно 60%
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//  возвращает в отчет % занятого места.
//
//
// raid - проверка массива на целостность
//  $tmpTask['command'][] = array('raid', <HIDE>);
//      <HIDE> - true|false скрыть из отчета.
//
//
// ps - проверка процесса.
//  $tmpTask['command'][] = array('ps', <PROCESS>, <COMMAND>, <TRYCOUNT>, <COMMENT>, <HIDE>);
//      <PROCESS> - имя процесса.
//      <COMMAND> - команда которую выполнить если процессы отсутствует.
//      <TRYCOUNT> - сколько раз пробовать выполнить команду.
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//      
//
// httpstat - проверка кодов состояния HTTP. команда использует консольный curl и проверяет заголовки ответа.
//  $tmpTask['command'][] = array('httpstat', <HOST>, <PARAMS>, <COMMENT>, <HIDE>);
//      <HOST> - хост проверяемого ресурса.
//      <PARAMS> - многомерный массив, содержит настройки для груп кодов.
//          ...
//          array(
//              array( - первая группа
//                  'codes' => array('200', '400-404'),
//                  'command' => 'комманда которую выполнить' - необязательный параметр.
//              )
//              array( - втарая группа
//                  'codes' => array('500-403')         
//              )
//              array( - действие на тот случай, если хост вообще не отвечает.
//                  'codes' => array('zero'),
//                  'command' => 'поднять nginx'
//              )
//          )
//          ...
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
// возвращает в отчет код состояния HTTP, если хост неотвечает возвращает 'zero'.
// !ВНИМАНИЕ! sms оповещения отключаются этой командой для всей задачи.
// 
// du - занято места в человекопонятном виде.
//  $tmpTask['command'][] = array('du', <SOURCE>, <COMMENT>, <HIDE>);
//      <SOURCE> - папка или файл чей размер проверить.
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//  возвращает в отчет размер файла или каталога.
// 
// ping - пингуем...
//  $tmpTask['command'][] = array('ping', <HOST>, <PACKET COUNT>, <PACKET LOST>, <COMMENT>, <HIDE>);
//      <HOST> - кого пингуем.
//      <PACKET COUNT> - сколько пакетов слать.
//      <PACKET LOST> - с какого значения считать что пинг провалился. указывать INT. Пример 10% - надо указать 10.
//      <COMMENT> - (необязательный параметр) комментарий будет помещён в поле return.
//      <HIDE> - true|false скрыть из отчета.
//      $tmpTask['command'][] = array('ping', "elkor.lv", 2, 10);
// 
// 
// Глобально
//  Крайне не рекомендуется использовать флаг -v --verbose, или любой другой который возвращает
//  большое количество данных. У скрипта просто может не хватить рама и он упадет без всяких оповещений.
//
// ==================================================
// =                    Образцы                     =
// ==================================================
// 
// ================== Классический: =================
/*
  $sysTaskStac = array();
  $sysTask = array();
      $tmpTask = array();
          $tmpTask['info']['name'] = 'name';
          $tmpTask['config']['scheduler'] = array('monthly' => array(5,6), 'daily' => 10,'weekly' => array(1,2));
          $tmpTask['command'][] = array('command', 'param', 'param1', 'param2');
          $tmpTask['command'][] = array('command2', 'param', 'param1', 'param2');
          $sysTask[] = $tmpTask;
          unset($tmpTask);
      $tmpTask = array();
          $tmpTask['info']['name'] = 'name2';
          $tmpTask['command'][] = array('command', 'param', 'param1', 'param2');
          $tmpTask['command'][] = array('command2', 'param', 'param1', 'param2');
          $sysTask[] = $tmpTask;
          unset($tmpTask);
      $sysTaskStac['default'] = $sysTask;
      $sysTaskStac['default']['config']['notice'] = 0;
      unset($sysTask);
  $sysTask = array();
      $tmpTask = array();
          $tmpTask['info']['name'] = 'name';
          $tmpTask['command'][] = array('command', 'param', 'param1', 'param2');
          $tmpTask['command'][] = array('command2', 'param', 'param1', 'param2');
          $sysTask[] = $tmpTask;
          unset($tmpTask);
      $tmpTask = array();
          $tmpTask['info']['name'] = 'name2';
          $tmpTask['command'][] = array('command', 'param', 'param1', 'param2');
          $tmpTask['command'][] = array('command2', 'param', 'param1', 'param2');
          $sysTask[] = $tmpTask;
          unset($tmpTask);
      $sysTaskStac['custom'] = $sysTask;
      $sysTaskStac['custom']['config']['notice'] = 0;
      unset($sysTask);
*/
// ================== Сокращённый: ==================
/*
$sysTaskStac = array(
    'default' => array(
        'config' => array('notice' => 0),
        array( // task 1
            'info' => array('name' => 'name'),
            'config' => array(
                'scheduler' => array('monthly' => array(5,6), 'daily' => 10, 'weekly' => array(1,2)),
            ),
            'command' => array(
                array('command', 'param', 'param1', 'param2'),
                array('command2', 'param', 'param1', 'param2'),
            ), // END commands
        ), // END task 1
        array( // task 2
            'info' => array('name' => 'name2'),
            'command' => array(
                array('command', 'param', 'param1', 'param2'),
                array('command2', 'param', 'param1', 'param2'),
            ), // END commands
        ), // END task 2
    ), // END default stac
    'custom' => array(
        'config' => array('notice' => 0),
        array( // task 1
            'info' => array('name' => 'name'),
            'command' => array(
                array('command', 'param', 'param1', 'param2'),
                array('command2', 'param', 'param1', 'param2'),
            ), // END commands
        ), // END task 1
        array( //task 2
            'info' => array('name' => 'name2'),
            'command' => array(
                array('command', 'param', 'param1', 'param2'),
                array('command2', 'param', 'param1', 'param2'),
            ), // END commands
        ), // END task 2
    ), // END Custom stac
);
*/
?>
