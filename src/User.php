<?php
  class User{
    private $id;
    private $user;

    function __construct($user, $id=null){
      $this->user = $user;
      $this->id = $id;
    }

    function getId(){
      return $this->id;
    }

    function setUser($new_user){
      $this->user = (string) $new_user;
    }

    function getUser(){
      return $this->user;
    }

    function save(){
      $executed = $GLOBALS['db']->exec("INSERT INTO users (user) VALUES ('{$this->getUser()}');");
      if($executed){
        $this->id = $GLOBALS['db']->lastInsertId();
        return true;
      } else {
        return false;
      }
    }

    static function find($id){
      $executed = $GLOBALS['db']->prepare("SELECT * FROM users WHERE id = :id;");
      $executed->bindParam(':id', $id, PDO::PARAM_INT);
      $executed->execute();
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      $new_user = new User($result['user'], $result['id']);
      return $new_user;
    }

    static function getAll(){
      $user_array = array();
      $executed = $GLOBALS['db']->query("SELECT * FROM users;");
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      foreach($results as $result){
        $new_user = new User($result['user'], $result['id']);
        array_push($user_array, $new_user);
      }
      return $user_array;
    }

    static function userArray(){
      $user_name_array = array();
      $executed = $GLOBALS['db']->query("SELECT * FROM users;");
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      foreach($results as $result){
        array_push($user_name_array, $result['user']);
      }
      return $user_name_array;
    }

    static function SearchByUser($user){
      $executed = $GLOBALS['db']->prepare("SELECT * FROM users WHERE user = :user;");
      $executed->bindParam(':user', $user, PDO::PARAM_STR);
      $executed->execute();
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      $new_user = new User($result['user'], $result['id']);
      return $new_user;
    }

    function userInfo(){
      $executed = $GLOBALS['db']->query("SELECT books.id, books.book, checkouts.checkout_date, checkouts.due_date From users JOIN checkouts ON (checkouts.user_id = users.id) JOIN books ON (checkouts.book_id = books.id) WHERE users.id = {$this->getId()};");
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }

  }
?>
