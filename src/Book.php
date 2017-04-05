<?php
  class Book {
    private $id;
    private $book;

    function __construct($book, $id=null){
      $this->book = $book;
      $this->id = $id;
    }

    function getId(){
      return $this->id;
    }

    function setBook($new_book){
      $this->book = (string) $new_book;
    }

    function getBook(){
      return $this->book;
    }

    function save(){
      $executed = $GLOBALS['db']->exec("INSERT INTO books (book) VALUES ('{$this->getBook()}');");
      if($executed){
        $this->id = $GLOBALS['db']->lastInsertId();
        return true;
      } else {
        return false;
      }
    }

    static function getAll(){
      $book_array = array();
      $executed = $GLOBALS['db']->query("SELECT * FROM books;");
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      foreach($results as $result){
        $new_book = new Book($result['book'], $result['id']);
        array_push($book_array, $new_book);
      }
      return $book_array;
    }

    static function find($id){
      $executed = $GLOBALS['db']->prepare("SELECT * FROM books WHERE id = :id;");
      $executed->bindParam(':id', $id, PDO::PARAM_INT);
      $executed->execute();
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      $new_book = new Book($result['book'], $result['id']);
      return $new_book;
    }

    static function findByBook($book){
      $executed = $GLOBALS['db']->prepare("SELECT * FROM bookS WHERE book = :book;");
      $executed->bindParam(':book', $book, PDO::PARAM_STR);
      $executed->execute();
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      $new_book = new Book($result['book'], $result['id']);
      return $new_book;
    }


  }
?>
