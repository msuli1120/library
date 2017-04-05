<?php
  class Author{

    private $id;
    private $author;

    function __construct($author, $id=null){
      $this->author = $author;
      $this->id = $id;
    }

    function getId(){
      return $this->id;
    }

    function setAuthor($new_author){
      $this->author = (string) $new_author;
    }

    function getAuthor(){
      return $this->author;
    }

    function save(){
      $executed = $GLOBALS['db']->exec("INSERT INTO authors (author) VALUES ('{$this->getAuthor()}');");
      if($executed){
        $this->id = $GLOBALS['db']->lastInsertId();
        return true;
      } else {
        return false;
      }
    }

    function saveBook($book_id){
      $executed = $GLOBALS['db']->exec("INSERT INTO books_authors (author_id, book_id) VALUES ({$this->getId()}, $book_id);");
      if($executed){
        return true;
      } else {
        return false;
      }
    }

    static function getAuthors($id){
      $author_array = array();
      $executed = $GLOBALS['db']->prepare("SELECT authors.* FROM authors JOIN books_authors ON (books_authors.author_id = authors.id) JOIN books ON (books_authors.book_id = books.id) WHERE books.id = :id;");
      $executed->bindParam(':id', $id, PDO::PARAM_INT);
      $executed->execute();
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      foreach($results as $result){
        $new_author = new Author($result['author'], $result['id']);
        array_push($author_array, $new_author);
      }
      return $author_array;
    }

    static function find($id){
      $executed = $GLOBALS['db']->prepare("SELECT * FROM authors WHERE id = :id;");
      $executed->bindParam(':id', $id, PDO::PARAM_INT);
      $executed->execute();
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      $new_author = new Author($result['author'], $result['id']);
      return $new_author;
    }

    function findbooks(){
      $book_array = array();
      $executed = $GLOBALS['db']->query("SELECT books.* FROM books JOIN books_authors ON (books.id = books_authors.book_id) JOIN authors ON (books_authors.author_id = authors.id) WHERE authors.id = {$this->getId()};");
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }

    static function getAllNames(){
      $author_array = array();
      $executed = $GLOBALS['db']->query("SELECT * FROM authors;");
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      foreach($results as $result){
        $new_author = new Author($result['author'], $result['id']);
        array_push($author_array, $new_author->getAuthor());
      }
      return $author_array;
    }

    static function getByName($name){
      $executed = $GLOBALS['db']->prepare("SELECT * FROM authors WHERE author = :name;");
      $executed->bindParam(':name', $name, PDO::PARAM_STR);
      $executed->execute();
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      $new_author = new Author($result['author'], $result['id']);
      return $new_author;
    }



  }
?>
