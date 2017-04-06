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

    

  }
?>
