<?php

  /**
  * @backupGlobals disabled
  * @backupStaticAttributes disabled
  */

  require_once "src/Class.php";

  class ClassTest extends PHPUnit_Framework_TestCase {
    function testClass () {
      //Arrange
      $test_class = new Class;
      $input = "";
      //Act
      $result = $test_class->methode();
      //Assert
      $this->assertEquals(" ", $result);
    }
  }

?>
