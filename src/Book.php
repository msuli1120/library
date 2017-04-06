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

    static function getAllBooks(){
      $book_array = array();
      $executed = $GLOBALS['db']->query("SELECT * FROM books;");
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      foreach($results as $result){
        array_push($book_array, $result['book']);
      }
      return $book_array;
    }

    function saveCopy($copy){
      $executed = $GLOBALS['db']->prepare("INSERT INTO copies (book_id, copy) VALUES ({$this->getId()}, :copy);");
      $executed->bindParam(':copy', $copy, PDO::PARAM_INT);
      $executed->execute();
      if($executed){
        return true;
      } else {
        return false;
      }
    }

    function saveAvailableCopy($copy){
      $executed = $GLOBALS['db']->prepare("INSERT INTO available_copies (book_id, available_copy) VALUES ({$this->getId()}, :copy);");
      $executed->bindParam(':copy', $copy, PDO::PARAM_INT);
      $executed->execute();
      if($executed){
        return true;
      } else {
        return false;
      }
    }


    static function getCopy($book_id){
      $executed = $GLOBALS['db']->prepare("SELECT copies.* FROM copies JOIN books ON (copies.book_id = books.id) WHERE books.id = :id;");
      $executed->bindParam(':id', $book_id, PDO::PARAM_INT);
      $executed->execute();
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      return $result['copy'];
    }

    static function getAvailableCopy($book_id){
      $executed = $GLOBALS['db']->prepare("SELECT available_copy FROM available_copies JOIN books ON (available_copies.book_id = books.id) WHERE books.id = :id;");
      $executed->bindParam(':id', $book_id, PDO::PARAM_INT);
      $executed->execute();
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      return $result['available_copy'];
    }

    static function setAvailableCopy($book_id){
      $executed = $GLOBALS['db']->prepare("UPDATE available_copies SET available_copy = available_copy - 1 WHERE book_id = :id;");
      $executed->bindParam(':id', $book_id, PDO::PARAM_INT);
      $executed->execute();
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      return $result['available_copy'];
    }

    static function findLoaner($book_id){
      $executed = $GLOBALS['db']->prepare("SELECT users.*, checkouts.checkout_date, checkouts.due_date FROM users JOIN checkouts ON (checkouts.user_id = users.id) JOIN books ON (checkouts.book_id = books.id) WHERE books.id = :id;");
      $executed->bindParam(':id', $book_id, PDO::PARAM_INT);
      $executed->execute();
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }

    static function checkOverdue($book_id){
      $executed = $GLOBALS['db']->prepare("SELECT users.*, checkouts.checkout_date, checkouts.due_date FROM users JOIN checkouts ON (checkouts.user_id = users.id) JOIN books ON (checkouts.book_id = books.id) WHERE books.id = :id AND checkouts.due_date < NOW();");
      $executed->bindParam(':id', $book_id, PDO::PARAM_INT);
      $executed->execute();
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }

  }
?>
