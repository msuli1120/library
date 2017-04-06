<?php
  date_default_timezone_set('America/Los_Angeles');
  require_once __DIR__."/../vendor/autoload.php";
  require_once __DIR__."/../src/Book.php";
  require_once __DIR__."/../src/Author.php";
  require_once __DIR__."/../src/User.php";

  use Symfony\Component\Debug\Debug;
  Debug::enable();

  $app = new Silex\Application();

  $app['debug'] = true;

  $server = 'mysql:host=localhost;dbname=library';
  $user = 'root';
  $pass = 'root';
  $db = new PDO($server, $user, $pass);

  $app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views'
  ));

  use Symfony\Component\HttpFoundation\Request;
  Request::enableHttpMethodParameterOverride();

  $app->get("/", function () use ($app) {
    $msg = '';
    return $app['twig']->render('index.html.twig', array('msg'=>$msg));
  });

  $app->get("/addbook", function () use ($app) {
    return $app['twig']->render('addbook.html.twig', array('books'=>Book::getAll()));
  });

  $app->post("/addbook", function () use ($app) {
    if(empty($_POST['book'])){
      return $app['twig']->render('warning.html.twig');
    } else {
      $new_book = new Book($_POST['book']);
      $new_book->save();
      $new_book->saveCopy($_POST['copy']);
      $new_book->saveAvailableCopy($_POST['copy']);
      return $app['twig']->render('addbook.html.twig', array('books'=>Book::getAll(), 'copy'=>Book::getCopy($new_book->getId())));
    }
  });

  $app->get("/book/{id}", function ($id) use ($app) {
    return $app['twig']->render('book.html.twig', array('book'=>Book::find($id), 'copy'=>Book::getCopy($id), 'available_copy'=>Book::getAvailableCopy($id), 'authors'=>Author::getAuthors($id), 'loaners'=>Book::findLoaner($id), 'overdueloaners'=>Book::checkOverdue($id)));
  });

  $app->post("/addauthor", function () use ($app) {
    $authors = Author::getAllNames();
    if(empty($_POST['author'])){
      return $app['twig']->render('warning.html.twig');
    } else {
      if(in_array($_POST['author'], $authors)){
        $new_author = Author::getByName($_POST['author']);
        $new_author->saveBook($_POST['book_id']);
        return $app['twig']->render('book.html.twig', array('book'=>Book::find($_POST['book_id']), 'copy'=>Book::getCopy($_POST['book_id']),
        'overdueloaners'=>Book::checkOverdue($_POST['book_id']), 'loaners'=>Book::findLoaner($_POST['book_id']), 'available_copy'=>Book::getAvailableCopy($_POST['book_id']), 'authors'=>Author::getAuthors($_POST['book_id'])));
      } else {
        $new_author = new Author($_POST['author']);
        $new_author->save();
        $new_author->saveBook($_POST['book_id']);
        return $app['twig']->render('book.html.twig', array('book'=>Book::find($_POST['book_id']), 'copy'=>Book::getCopy($_POST['book_id']), 'loaners'=>Book::findLoaner($_POST['book_id']),
        'overdueloaners'=>Book::checkOverdue($_POST['book_id']), 'available_copy'=>Book::getAvailableCopy($_POST['book_id']), 'authors'=>Author::getAuthors($_POST['book_id'])));
      }
    }
  });

  $app->get("/author/{id}", function ($id) use ($app) {
    $new_author = Author::find($id);
    $books = $new_author->findbooks();
    return $app['twig']->render('author.html.twig', array('author'=>Author::find($id), 'books'=>$books));
  });

  $app->post("/search", function () use ($app) {
    $book_array = Book::getAllBooks();
    $author_array = Author::getAllNames();
    if(in_array($_POST['srch-term'], $book_array)){
      $author = '';
      $new_book = Book::findByBook($_POST['srch-term']);
      return $app['twig']->render('result.html.twig', array('book'=>$new_book, 'author'=>$author));
    } else if(in_array($_POST['srch-term'], $author_array)){
      $book = '';
      $new_author = Author::getByName($_POST['srch-term']);
      return $app['twig']->render('result.html.twig', array('author'=>$new_author, 'book'=>$book));
    } else {
      $msg = "No Match!";
      return $app['twig']->render('index.html.twig', array('msg'=>$msg));
    }
  });

  $app->get("/adduser", function () use ($app) {
    return $app['twig']->render('adduser.html.twig', array('user'=>''));
  });

 $app->post("/adduser", function () use ($app) {
   if(!empty($_POST['user'])){
     $new_user = new User($_POST['user']);
     $new_user->save();
     return $app['twig']->render('user.html.twig', array('user'=>$new_user, 'books'=>Book::getAll()));
   } else {
     return $app['twig']->render('adduser.html.twig', array('user'=>''));
   }
 });

 $app->get("/checkout/{userid}/{bookid}", function ($userid,$bookid) use ($app) {
   return $app['twig']->render('checkout.html.twig', array('user'=>User::find($userid), 'book'=>Book::find($bookid), 'available_copy'=>Book::getAvailableCopy($bookid)));
 });

 $app->post("/checkout", function () use ($app) {
   $user_id = (int) $_POST['user_id'];
   $book_id = (int) $_POST['book_id'];
   $query = $GLOBALS['db']->query("INSERT INTO checkouts (user_id, book_id, checkout_date, due_date) VALUES ($user_id, $book_id, NOW(),NOW()+INTERVAL 7 DAY);");
   $executed = $GLOBALS['db']->prepare("SELECT * FROM checkouts WHERE user_id = :user_id AND book_id = :book_id;");
   $executed->bindParam(':user_id', $user_id, PDO::PARAM_INT);
   $executed->bindParam(':book_id', $book_id, PDO::PARAM_INT);
   $executed->execute();
   $result = $executed->fetch(PDO::FETCH_ASSOC);
   $new_book = Book::find($book_id);
   $new_book->setAvailableCopy($book_id);
   return $app['twig']->render('receipt.html.twig', array('result'=>$result, 'user'=>User::find($user_id), 'book'=>$new_book->getBook()));
 });

 $app->post("/usersearch", function () use ($app) {
   $user_names = User::userArray();
   if(!empty($_POST['srch-term'])){
     if(in_array($_POST['srch-term'], $user_names)){
       $new_user = User::SearchByUser($_POST['srch-term']);
       return $app['twig']->render('adduser.html.twig', array('user'=>$new_user));
     } else {
       $user = '';
       return $app['twig']->render('adduser.html.twig', array('user'=>$user));
     }
   } else {
     $user = '';
     return $app['twig']->render('adduser.html.twig', array('user'=>$user));
   }
 });

 $app->get("/user/{id}", function ($id) use ($app) {
   $user = User::find($id);
   $results = $user->userInfo();
   return $app['twig']->render('userinfo.html.twig', array('user'=>$user, 'results'=>$results));
 });

 $app->get("/book/{id}/edit", function ($id) use ($app) {
   return $app['twig']->render('bookedit.html.twig', array('book'=>Book::find($id), 'copy'=>Book::getCopy($id), 'availablecopy'=>Book::getAvailableCopy($id)));
 });

 $app->post("/editbook", function () use ($app) {
   if((empty($_POST['book']))||(empty($_POST['copy']))){
     return $app['twig']->render('bookedit.html.twig', array('book'=>Book::find($_POST['book_id']), 'copy'=>Book::getCopy($_POST['book_id']), 'availablecopy'=>Book::getAvailableCopy($_POST['book_id'])));
   } else {
     $book = Book::find($_POST['book_id']);
     $old_copy = Book::getCopy($_POST['book_id']);
     $old_available_copy = Book::getAvailableCopy($_POST['book_id']);
     if($_POST['copy'] != 0){
       $available_copy = $old_available_copy + ($_POST['copy']-$old_copy);
       $book->availableCopyUpdate($available_copy);
     }
     $book->bookUpdate($_POST['book']);
     $book->copyUpdate($_POST['copy']);
     return $app['twig']->render('book.html.twig', array('book'=>Book::find($_POST['book_id']), 'copy'=>Book::getCopy($_POST['book_id']),
     'overdueloaners'=>Book::checkOverdue($_POST['book_id']), 'loaners'=>Book::findLoaner($_POST['book_id']), 'available_copy'=>Book::getAvailableCopy($_POST['book_id']), 'authors'=>Author::getAuthors($_POST['book_id'])));
   }
 });

 $app->post("/return", function () use  ($app) {
   $book = Book::find($_POST['book_id']);
   $book->bookReturn($_POST['user_id']);
   $book->availableCopyReturn();
   $user = User::find($_POST['user_id']);
   $results = $user->userInfo();
   return $app['twig']->render('userinfo.html.twig', array('user'=>$user, 'results'=>$results));
 });

  return $app;
?>
