<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
class TelegraphText 
{
    public $title; 
    public $text;
    public $author; 
    public $published; 
    public $slug; 

    public function __construct($author, $slug)
    {
        $this->author = $author;
        $this->slug = $slug;
        $this->published = date('Y-m-d H:i:s');
    }

    public function storeText()
    {
        $storeText = [
            'text' => $this->text, 
            'title' => $this->title, 
            'author' => $this->author, 
            'published' => $this->published
        ];
        $serialize = serialize($storeText);
        file_put_contents($this->slug, $serialize);
    }

    public function loadText()
    {
        $fileStorage = new FileStorage;
        $obj = $fileStorage->read(null, $this->slug);
        if (is_object($obj)) {
            $this->author = $obj->author;
            $this->text = $obj->text;
            $this->title = $obj->title;
            $this->published = $obj->published;
            return $this->text;
        } else {
            return false;
        }
    }
    
    public function editText($text, $title)
    {
        $this->text = $text;
        $this->title = $title;
    }
}


abstract class Storage implements LoggerInterface, EventListenerInterface 
{
    abstract function create($obj); 
    abstract function read ($id, $slug); 
    abstract function update ($id, $slug, $obj); 
    abstract function delete ($id, $slug); 
    abstract function list(); 
    abstract function logMessage($error_text);
    abstract function lastMessages($number_of_messages);
    abstract function attachEvent($method_name);
    abstract function detouchEvent($method_name);
}

abstract class View 
{
    public $storage;

    abstract function displayTextById ($id); 
    abstract function displayTextByUrl ($url); 
    
    public function __construct($storage) 
    {
        $this->storage = $storage;
    }
    

} 

abstract class User implements EventListenerInterface
{
    public $id; 
    public $name; 
    public $role; 

    abstract function getTextsToEdit(); 
    abstract function attachEvent($method_name);
    abstract function detouchEvent($method_name);
} 

class FileStorage extends Storage
{
    public function logMessage($error_text)
    {
        file_put_contents('error.log', $error_text . PHP_EOL, FILE_APPEND);
    }
    public function lastMessages($number_of_messages) 
    {
        // получаем содержимое файл с логами.
        $logs = file_get_contents('error.log');
        $logs = trim($logs);
        // превратим полученный контент в массив по строкам
        $arr = explode(PHP_EOL, $logs);
        // из этого массива мы получаем данные с конца
        $array = array_slice($arr, -2, $number_of_messages);
        // вернуть массив сообщений
        return $array;
    }
    public function attachEvent($method_name)
    {
        
    }
    public function detouchEvent($method_name)
    {

    }
    public function create($obj)
    {
        $now = new DateTime();
        $date = $now->format('Y-m-d');
        $slug = $obj->slug;
        $slug = explode('.', $slug);
        print_r($slug);
        $fileName = $slug[0] . '_' . $date . '.' . $slug[1];
        $i = 0;
        while (file_exists($fileName)) {
            $i++;
            $fileName = $slug[0] . '_' . $date .  '_' . $i . '.'  . $slug[1];
        }
        $obj->slug = $fileName;
        $serialize = serialize($obj);
        file_put_contents ($fileName, $serialize);
        return $fileName;
    }
    public function read($id, $slug) 
    {   
        if (file_exists($slug)) { 
            $serialize = file_get_contents($slug); 
            $obj = unserialize ($serialize); 
            return $obj;
        } 
        
    }
    public function update($id, $slug, $obj)
    {
        if (file_exists($slug)) {
            $serialize = serialize ($obj);
            file_put_contents ($slug, $serialize);
        }
    }
    public function delete($id, $slug)
    {
        if (file_exists($slug)) {
            unlink($slug);
        }
    }
    public function list()
    {
        $files = scandir(__DIR__);
        print_r($files);
        foreach ($files as $value) {
            $pos = strpos($value, '.txt');
            if ($pos != false) {
                echo $value;
                $serialize = file_get_contents($value); 
                $obj = unserialize ($serialize); 
                $files_true[] = $obj;
            }
        }
        return $files_true; 
    }
}
// TASK 10
interface LoggerInterface
{
    public function logMessage($error_text);
    public function lastMessages($number_of_messages);
}
interface EventListenerInterface
{
    public function attachEvent($method_name);
    public function detouchEvent($method_name);
}


$TelegraphText = new TelegraphText('viktor', 'textclass.txt');
$TelegraphText->editText('hi', 'text');
$TelegraphText->storeText();
var_dump($TelegraphText->loadText());
echo '<pre>';
var_dump($TelegraphText);
$serialize = serialize ($TelegraphText);
var_dump($serialize);
$fileStorage = new FileStorage;
$fileName = $fileStorage->create($TelegraphText);
$fileStorage->update(null, $fileName, $TelegraphText);
var_dump($TelegraphText);
echo $fileName;
var_dump ($fileStorage->list());
$fileStorage->logMessage('ошибка 1');
$fileStorage->logMessage('ошибка 2');
$fileStorage->logMessage('ошибка 3');
print_r($fileStorage->lastMessages(2));
